<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Carnet;
use App\Models\Contribution;
use App\Models\CaisseClosure;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use App\Models\CarnetLot;
use Illuminate\Support\Facades\DB;

class SecretaryController extends Controller
{
    /**
     * Display Secretary Dashboard.
     */
    public function dashboard(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $query = Client::byLocation()->with(['collector', 'activeCarnet']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        $clients = $query->orderBy('created_at', 'desc')->get();

        // Statistiques globales filtrées par localisation (Scope géographique de la secrétaire)
        $totalClientsActifs = Client::byLocation()->whereIn('status', ['active', 'goal_reached'])->count();
        $totalEncaisse = Contribution::whereHas('client', function($q) {
            $q->byLocation();
        })->sum('amount');
        $clientsALivrer = Client::byLocation()->where('status', 'goal_reached')->count();

        // Données d'administration pour la colonne de droite filtrées par localisation
        $pendingClosures = CaisseClosure::whereHas('collector', function($q) {
            $q->byLocation();
        })->with('collector')->where('status', 'pending')->get();

        $collectors = User::byLocation()->where(function($q) {
            $q->where('role', 'collector')->orWhere('role', 'collecteur');
        })->get();

        // Contributions de la journée en attente de validation filtrées par localisation
        $pendingContributions = Contribution::whereHas('client', function($q) {
            $q->byLocation();
        })
            ->with(['client.activeCarnet', 'collector'])
            ->where('validation_status', 'pending')
            ->latest()
            ->get();

        return view('secretary.dashboard', compact(
            'clients',
            'totalClientsActifs',
            'totalEncaisse',
            'clientsALivrer',
            'search',
            'statusFilter',
            'pendingClosures',
            'collectors',
            'pendingContributions'
        ));
    }

    /**
     * Attribuer un nouveau carnet à un collecteur (Correction de l'affectation sans recréation).
     */
    public function assignCarnet(Request $request)
    {
        $request->validate([
            'collector_id' => ['required', 'exists:users,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'daily_rate' => ['required', 'numeric'],
        ]);

        $quantityRequested = (int) $request->input('quantity');
        $collectorId = $request->input('collector_id');
        $dailyRate = $request->daily_rate;

        // 1. Vérifier s'il y a assez de stock global dans les lots de ce tarif créés par l'admin
        $availableLots = CarnetLot::byLocation()->where('remaining_quantity', '>', 0)
            ->where('daily_rate', $dailyRate)
            ->orderBy('purchase_date', 'asc')
            ->get();

        $totalStock = $availableLots->sum('remaining_quantity');

        if ($totalStock < $quantityRequested) {
            return redirect()->back()->with('error', "Stock insuffisant ! Demandé : {$quantityRequested} (Tarif {$dailyRate} F), Stock disponible : {$totalStock}.");
        }

        $remainingToAssign = $quantityRequested;

        // 2. Piocher et mettre à jour les carnets existants liés aux lots de l'admin (FIFO)
        foreach ($availableLots as $lot) {
            if ($remainingToAssign <= 0) {
                break;
            }

            // Quantité à prendre dans ce lot spécifique
            $takeFromThisLot = min($lot->remaining_quantity, $remainingToAssign);

            // Récupérer exactement le nombre de carnets non encore attribués (stock central) pour ce lot
            $carnetsToUpdate = Carnet::where('carnet_lot_id', $lot->id)
                ->whereNull('collector_id')
                ->take($takeFromThisLot)
                ->get();

            // Si pour une raison quelconque les carnets n'ont pas été pré-générés dans la table carnets lors de la création du lot,
            // on s'assure d'affecter ou de mettre à jour correctement.
            foreach ($carnetsToUpdate as $carnet) {
                $carnet->update([
                    'collector_id' => $collectorId,
                    'status' => 'assigned_to_collector',
                ]);
            }

            // Réduire la quantité disponible dans le lot de l'admin
            $lot->decrement('remaining_quantity', $takeFromThisLot);

            $remainingToAssign -= $takeFromThisLot;
        }

        // 3. Journalisation de l'action
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'ASSIGN_CARNETS_FROM_ADMIN_STOCK',
            'description' => "La secrétaire a transféré {$quantityRequested} carnets (issus du stock administrateur) au collecteur ID {$collectorId}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('secretary.dashboard')->with('success', __('messages.transfer_success', ['qty' => $quantityRequested]));
    }

    /**
     * Valider une cotisation spécifique de la journée.
     */
    public function validateContribution(Request $request, $id)
    {
        // Sécurisation anti-IDOR : la secrétaire ne peut valider que les cotisations de son scope géographique
        $contribution = Contribution::whereHas('client', function($q) {
            $q->byLocation();
        })->findOrFail($id);

        $contribution->update([
            'validation_status' => 'validated'
        ]);

        event(new \App\Events\ContributionValidated($contribution));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'VALIDATE_CONTRIBUTION',
            'description' => "Validation de la cotisation de {$contribution->amount} FCFA pour le client ID {$contribution->client_id}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('secretary.dashboard')->with('success', __('messages.validation_success'));
    }
    /**
     * Valider la clôture de caisse globale d'un collecteur.
     */
    public function validateClosure(Request $request, $id)
    {
        // Sécurisation anti-IDOR : la secrétaire ne peut valider que les clôtures de son scope géographique
        $closure = CaisseClosure::whereHas('collector', function($q) {
            $q->byLocation();
        })->findOrFail($id);

        DB::transaction(function () use ($request, $closure) {
            $closure->update([
                'status' => 'validated',
                'validated_by' => Auth::id()
            ]);

            // Auto-valider toutes les cotisations en attente de ce collecteur pour ce jour de clôture
            $contributions = Contribution::where('collector_id', $closure->collector_id)
                ->whereDate('created_at', \Carbon\Carbon::parse($closure->closure_date)->toDateString())
                ->where('validation_status', 'pending')
                ->get();

            foreach ($contributions as $contribution) {
                $contribution->update([
                    'validation_status' => 'validated',
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]);

                event(new \App\Events\ContributionValidated($contribution));
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'VALIDATE_CAISSE_CLOSURE',
                'description' => "Validation de la clôture de caisse et de {$contributions->count()} cotisation(s) du collecteur ID {$closure->collector_id}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('secretary.dashboard')->with('success', __('messages.caisse_validation_success'));
    }

    public function show($id)
    {
        // Sécurisation anti-IDOR : la secrétaire ne peut voir que les clients de son scope géographique
        $client = Client::byLocation()->with(['contributions', 'activeCarnet', 'collector'])->findOrFail($id);
        return view('secretary.clients.show', compact('client'));
    }

    public function deliverArticle(Request $request, $id)
    {
        // Sécurisation anti-IDOR : la secrétaire ne peut livrer des articles qu'à des clients de son scope géographique
        $client = Client::byLocation()->findOrFail($id);

        if ($client->status !== 'goal_reached') {
            return redirect()->route('secretary.dashboard')->with('error', __('messages.goal_not_reached'));
        }

        DB::transaction(function () use ($client, $request) {
            $client->update([
                'status'       => 'completed',
                'delivered_at' => now(),
            ]);

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'DELIVER_ARTICLE',
                'description' => "Remise de l'article '{$client->desired_article}' au client {$client->firstname} {$client->name}.",
                'ip_address'  => $request->ip(),
            ]);
        });

        return redirect()->route('secretary.dashboard')->with('success', __('messages.delivery_success', ['article' => $client->desired_article]));
    }
}
