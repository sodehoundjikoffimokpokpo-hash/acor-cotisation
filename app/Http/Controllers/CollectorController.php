<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Carnet;
use App\Models\Contribution;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Article;
use App\Models\CaisseClosure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Exception;

class CollectorController extends Controller
{
    /**
     * Display Collector Mobile-responsive Dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        // Si l'utilisateur est un collecteur, on filtre uniquement ses clients.
        // Si c'est un admin / super-admin, il voit tous les clients.
        $query = Client::byLocation()->with(['activeCarnet', 'contributions'])
            ->whereIn('status', ['active', 'goal_reached']);

        // Filtrage selon le rôle du collecteur
        if ($user->role === 'collector' || $user->role === 'collecteur' || (method_exists($user, 'hasRole') && $user->hasRole('collector'))) {
            $query->where('collector_id', $user->id);
        }

        // Application du filtre de recherche si l'utilisateur a écrit quelque chose
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('firstname', 'asc')->get();

        // Correction : Récupération de tous les carnets du collecteur sans client assigné, indépendamment du statut textuel
        $carnetsDisponibles = Carnet::where('collector_id', Auth::id())
            ->whereNull('client_id')
            ->orderBy('number', 'asc')
            ->get();

        // Récupération du premier carnet disponible pour le proposer par défaut
        $suggestedCarnet = $carnetsDisponibles->first();

        // Stock réel calculé dynamiquement
        $stockDisponibleCount = $carnetsDisponibles->count();

        // Stock groupé par tarif journalier du lot
        $stockByRate = $carnetsDisponibles->groupBy(function($c) {
            return $c->lot->daily_rate ?? 0;
        })->map(function($group) {
            return $group->count();
        });

        $articles = Article::where('stock_quantity', '>', 0)->get();
        $articlesExistants = Client::distinct()->pluck('desired_article')->filter();

        // --- NOUVEAU : CLIENTS INACTIFS (PLUS DE 7 JOURS SANS COTISATION) ---
        $inactiveClients = Client::where('collector_id', $user->id)
            ->where('status', 'active')
            ->with(['contributions'])
            ->get()
            ->filter(function($client) {
                $lastContribution = $client->contributions->sortByDesc('payment_date')->first();
                if (!$lastContribution) {
                    return $client->created_at->lt(now()->subDays(7));
                }
                return Carbon::parse($lastContribution->payment_date)->lt(now()->subDays(7));
            });

        return view('collector.dashboard', compact(
            'clients',
            'carnetsDisponibles',
            'suggestedCarnet',
            'stockDisponibleCount',
            'stockByRate',
            'articles',
            'articlesExistants',
            'inactiveClients',
            'search'
        ));
    }

    /**
     * Store new client in the field with automatic stock consumption.
     */
    public function storeClient(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'daily_contribution' => ['required', 'numeric', 'min:1'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'desired_article' => ['required', 'string', 'max:255'],
            'carnet_number' => ['required', 'exists:carnets,number'],
        ]);

        /** @var \App\Models\User $collector */
        $collector = User::find(Auth::id());

        if (Schema::hasColumn('users', 'carnet_stock') && $collector->carnet_stock <= 0) {
            return redirect()->back()->withInput()->withErrors(['stock_error' => "Vous n'avez plus de carnets disponibles. Veuillez demander un nouvel approvisionnement."]);
        }

        // Recherche du carnet par son champ 'number'
        $carnet = Carnet::where('number', $request->carnet_number)
            ->where('collector_id', $collector->id)
            ->whereNull('client_id')
            ->first();

        if (!$carnet) {
            return redirect()->back()->withInput()->withErrors(['stock_error' => "Ce carnet n'est pas disponible dans votre stock ou a déjà été utilisé."]);
        }

        // Validation du tarif du carnet par rapport à la mise journalière choisie
        if (($carnet->lot->daily_rate ?? 0) != $request->daily_contribution) {
            return redirect()->back()->withInput()->withErrors(['stock_error' => "Le carnet sélectionné ({$carnet->number}) correspond à un tarif de " . ($carnet->lot->daily_rate ?? 0) . " FCFA. Il ne peut pas être affecté à une mise journalière de " . $request->daily_contribution . " FCFA."]);
        }

        try {
            DB::transaction(function () use ($request, $collector, $carnet) {
                $client = Client::create([
                    'name' => strtoupper($request->name),
                    'firstname' => $request->firstname,
                    'phone' => $request->phone,
                    'address' => $request->address ?? $collector->activity_zone ?? 'Non spécifiée',
                    'daily_contribution' => $request->daily_contribution,
                    'target_amount' => $request->target_amount,
                    'desired_article' => $request->desired_article,
                    'status' => 'active',
                    'collector_id' => $collector->id,
                    'carnet_id' => $carnet->id,
                    'country_id' => $collector->country_id,
                    'region_id' => $collector->region_id,
                    'zone_id' => $collector->zone_id,
                ]);

                $carnet->update([
                    'client_id' => $client->id,
                    'status' => 'active',
                ]);

                if (Schema::hasColumn('users', 'carnet_stock') && $collector->carnet_stock > 0) {
                    $collector->decrement('carnet_stock');
                }

                AuditLog::create([
                    'user_id' => $collector->id,
                    'action' => 'CREATE_CLIENT',
                    'description' => "Client {$client->firstname} {$client->name} enregistré. Carnet N° {$carnet->number} consommé.",
                    'ip_address' => $request->ip(),
                ]);
            });

            return redirect()->route('collector.dashboard')->with('success', "Client créé avec succès.");

        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors(['stock_error' => "Erreur : " . $e->getMessage()]);
        }
    }

    /**
     * Store contribution in the field.
     */
    public function storeContribution(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'dates' => ['required', 'array'],
            'dates.*' => ['required', 'date'],
        ]);

        // Sécurisation anti-IDOR : Le collecteur ne peut enregistrer un versement que pour l'un de ses propres clients.
        $client = Client::where('id', $request->client_id)->where('collector_id', Auth::id())->firstOrFail();

        $errors = [];
        $addedContributionsCount = 0;
        $totalAmount = 0;

        DB::transaction(function () use ($request, $client, &$errors, &$addedContributionsCount, &$totalAmount) {
            foreach ($request->dates as $dateStr) {
                $paymentDate = Carbon::parse($dateStr)->toDateString();

                // 1. Vérifier si la caisse est clôturée pour cette date
                $isClosed = CaisseClosure::where('collector_id', Auth::id())
                    ->whereDate('closure_date', $paymentDate)
                    ->exists();

                if ($isClosed) {
                    $errors[] = "La caisse pour la journée du " . Carbon::parse($paymentDate)->format('d/m/Y') . " est déjà clôturée.";
                    continue;
                }

                // 2. Vérifier si un versement existe déjà pour cette date pour ce client
                $exists = Contribution::where('client_id', $client->id)
                    ->whereDate('payment_date', $paymentDate)
                    ->whereIn('validation_status', ['pending', 'validated'])
                    ->exists();

                if ($exists) {
                    $errors[] = "Un versement est déjà enregistré pour le " . Carbon::parse($paymentDate)->format('d/m/Y') . ".";
                    continue;
                }

                // 3. Création de la contribution
                Contribution::create([
                    'client_id' => $client->id,
                    'collector_id' => Auth::id(),
                    'amount' => $client->daily_contribution,
                    'payment_date' => $paymentDate,
                    'validation_status' => 'pending',
                ]);

                $addedContributionsCount++;
                $totalAmount += $client->daily_contribution;
            }

            if ($addedContributionsCount > 0) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'RECORD_CONTRIBUTIONS_BULK',
                    'description' => "Enregistrement de {$addedContributionsCount} cotisation(s) pour un montant total de {$totalAmount} FCFA pour le client {$client->firstname} {$client->name}.",
                    'ip_address' => $request->ip(),
                ]);
            }
        });

        if (count($errors) > 0 && $addedContributionsCount === 0) {
            return redirect()->back()->withInput()->withErrors(['stock_error' => implode(' ', $errors)]);
        }

        $msg = "{$addedContributionsCount} cotisation(s) enregistrée(s) avec succès (Montant : {$totalAmount} FCFA).";
        if (count($errors) > 0) {
            $msg .= " Note : " . implode(' ', $errors);
        }

        return redirect()->route('collector.dashboard')->with('success', $msg);
    }

    /**
     * Show collector closure report.
     */
    public function closure()
    {
        $collectorId = Auth::id();
        $todayStr = Carbon::today()->toDateString();

        $contributionsToday = Contribution::with('client.activeCarnet')
            ->where('collector_id', $collectorId)
            ->whereDate('created_at', $todayStr)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalCollectedToday = (float) $contributionsToday->sum('amount');
        $clientsCountToday = $contributionsToday->unique('client_id')->count();

        return view('collector.closure', compact(
            'contributionsToday',
            'totalCollectedToday',
            'clientsCountToday'
        ));
    }

    public function valider(Contribution $contribution)
    {
        $contribution->update(['status' => 'validé']);

        AuditLog::logAction(
            'VALIDATION',
            "Validation de la contribution de " . $contribution->client->name,
            ['status' => 'en attente'],
            ['status' => 'validé'],
            $contribution
        );

        return back()->with('success', 'Validé avec succès');
    }

    public function annuler(Contribution $contribution)
    {
        $contribution->update(['status' => 'annulé']);

        AuditLog::logAction(
            'ANNULATION',
            "Annulation de la cotisation ID: " . $contribution->id,
            ['status' => 'validé'],
            ['status' => 'annulé'],
            $contribution
        );

        return back()->with('info', 'Action annulée');
    }

    public function cloturerCaisse()
    {
        $collectorId = auth()->id();
        $today = Carbon::today();

        $dejaCloture = CaisseClosure::where('collector_id', $collectorId)
            ->whereDate('closure_date', $today)
            ->exists();

        if ($dejaCloture) {
            return redirect()->back()->with('error', 'Vous avez déjà clôturé votre caisse aujourd\'hui.');
        }

        $contributions = Contribution::where('collector_id', $collectorId)
            ->whereDate('created_at', $today);

        $nbCotisations = $contributions->count();
        $montantTotal = $contributions->sum('amount');

        $carnetsVendus = Carnet::where('collector_id', $collectorId)
            ->whereDate('updated_at', $today)
            ->where('status', 'active')
            ->count();

        CaisseClosure::create([
            'collector_id'   => $collectorId,
            'closure_date'   => $today,
            'carnets_vendus' => $carnetsVendus,
            'nb_cotisations' => $nbCotisations,
            'montant_total'  => $montantTotal,
            'status'         => 'pending',
        ]);

        return redirect()->back()->with('success', 'Caisse clôturée avec succès, en attente de validation.');
    }

    /**
     * Update client details in the field.
     */
    public function updateClient(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'daily_contribution' => ['required', 'numeric', 'min:1'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'desired_article' => ['required', 'string', 'max:255'],
        ]);

        // Protection anti-IDOR : Le collecteur ne peut modifier que ses propres clients.
        $client = Client::where('id', $id)->where('collector_id', Auth::id())->firstOrFail();

        $client->update([
            'name' => strtoupper($request->name),
            'firstname' => $request->firstname,
            'phone' => $request->phone,
            'address' => $request->address ?? $client->address,
            'daily_contribution' => $request->daily_contribution,
            'target_amount' => $request->target_amount,
            'desired_article' => $request->desired_article,
        ]);

        $client->checkGoalStatus();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_CLIENT',
            'description' => "Le collecteur a modifié les informations du client {$client->firstname} {$client->name}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('collector.dashboard')->with('success', "Informations du client modifiées avec succès.");
    }
}
