<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCarnetLotRequest;
use App\Models\Carnet;
use App\Models\CarnetLot;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarnetController extends Controller
{
    /**
     * List all carnets with filtering, searching, and advanced statistics.
     */
    public function indexCarnets(Request $request)
    {
        // 1. Extraction des Statistiques Globales pour les KPIs du haut
        $statistics = [
            'disponible_siege' => Carnet::whereHas('lot', function($q) { $q->byLocation(); })->whereNull('collector_id')->whereNull('client_id')->count(),
            'en_circulation'   => Carnet::whereHas('lot', function($q) { $q->byLocation(); })->whereNotNull('collector_id')->whereNull('client_id')->count(),
            'actif'            => \App\Models\Client::byLocation()->where('status', 'active')->count(),
            'lots_expires'     => CarnetLot::byLocation()->where('expiration_date', '<', now())->count(),
        ];

        // 2. Récupération des lots pour remplir le tableau "Suivi et Validité des Lots"
        $lots = CarnetLot::byLocation()->with(['carnets', 'country'])->latest()->get();

        // 3. Récupération des collecteurs pour alimenter les listes déroulantes
        $collectors = User::byLocation()->where('role', 'collector')->orderBy('name', 'asc')->get();
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        $availableRates = CarnetLot::byLocation()->pluck('daily_rate')->unique()->sort();

        // 4. Construction de la requête principale des carnets
        $query = Carnet::whereHas('lot', function($q) { $q->byLocation(); })->with(['client', 'collector', 'lot.country'])->latest();

        // Filtre : Recherche par numéro de carnet ou nom de client
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('number', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function($qClient) use ($search) {
                      $qClient->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('firstname', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filtre : Statut du carnet
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'disponible') {
                $query->whereNull('client_id')->whereNull('collector_id');
            } elseif ($status === 'en_circulation') {
                $query->whereNotNull('collector_id')->whereNull('client_id');
            } else {
                $query->where('status', $status);
            }
        }

        // Filtre : Affectation à un collecteur spécifique
        if ($request->filled('collector_id')) {
            $query->where('collector_id', $request->input('collector_id'));
        }

        // Filtre : Tarif du carnet (daily_rate)
        if ($request->filled('daily_rate')) {
            $query->whereHas('lot', function($q) use ($request) {
                $q->where('daily_rate', $request->input('daily_rate'));
            });
        }

        // 5. Pagination des résultats
        $carnets = $query->paginate(15)->withQueryString();

        return view('admin.carnets.index', compact('carnets', 'statistics', 'collectors', 'lots', 'countries', 'availableRates'));
    }

    /**
     * Store a newly generated carnet (individual).
     */
    public function storeCarnet(Request $request)
    {
        $dernierCarnet = Carnet::latest('id')->first();
        $prochainNumero = 1;

        if ($dernierCarnet && $dernierCarnet->number) {
            $numeroExtrait = (int) str_replace('CARNET-', '', $dernierCarnet->number);
            $prochainNumero = $numeroExtrait + 1;
        }

        $codeCarnet = 'CARNET-' . str_pad($prochainNumero, 4, '0', STR_PAD_LEFT);

        Carnet::create([
            'number' => $codeCarnet,
            'status' => 'active',
            'client_id' => null,
        ]);

        return redirect()->back()->with('success', "Le carnet $codeCarnet a été généré avec succès !");
    }

    /**
     * Store newly generated carnets in bulk anchored to a specific CarnetLot.
     */
    public function storeBulkCarnet(StoreCarnetLotRequest $request)
    {
        $authCountryId = Auth::user()->country_id;
        $countryId = $authCountryId ?: $request->country_id;

        $quantity = (int) $request->quantity;
        $validityMonths = (int) $request->validity_months;

        $finalPrice = (float)$request->purchase_price 
            + (float)$request->transport_fees 
            + (float)$request->search_fees 
            + (float)$request->collector_commission 
            + (float)$request->admin_fees 
            + (float)$request->price_provision 
            + (float)$request->acor_margin;

        DB::transaction(function () use ($request, $quantity, $validityMonths, $finalPrice, $countryId) {
            $lot = CarnetLot::create([
                'lot_number'           => $request->batch_number,
                'batch_number'         => $request->batch_number,
                'initial_quantity'     => $quantity,
                'remaining_quantity'   => $quantity,
                'purchase_date'        => now(),
                'expiration_date'      => Carbon::now()->addMonths($validityMonths),
                'penalty_amount'       => $request->penalty_amount,
                'card_cost'            => $request->card_cost,
                'days_covered'         => $request->days_covered,
                'daily_rate'           => $request->daily_rate,
                'country_id'           => $countryId,
                'article_name'         => $request->article_name,
                'purchase_price'       => $request->purchase_price,
                'transport_fees'       => $request->transport_fees,
                'search_fees'          => $request->search_fees,
                'collector_commission' => $request->collector_commission,
                'admin_fees'           => $request->admin_fees,
                'price_provision'      => $request->price_provision,
                'acor_margin'          => $request->acor_margin,
                'final_price'          => $finalPrice,
            ]);

            for ($i = 0; $i < $quantity; $i++) {
                $dernierCarnet = Carnet::latest('id')->first();
                $prochainNumero = 1;

                if ($dernierCarnet && $dernierCarnet->number) {
                    $numeroExtrait = (int) str_replace('CARNET-', '', $dernierCarnet->number);
                    $prochainNumero = $numeroExtrait + 1;
                }

                $codeCarnet = 'CARNET-' . str_pad($prochainNumero, 4, '0', STR_PAD_LEFT);

                Carnet::create([
                    'carnet_lot_id' => $lot->id,
                    'number'        => $codeCarnet,
                    'status'        => 'in_stock',
                    'client_id'     => null,
                    'collector_id'  => null,
                ]);
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_LOT',
                'description' => "Génération de {$quantity} carnets pour le Lot {$request->batch_number} (Article: {$request->article_name}, Tarif: {$request->daily_rate} FCFA).",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->back()->with('success', "{$quantity} nouveaux carnets ont été générés et associés au Lot {$request->batch_number} avec succès !");
    }

    /**
     * Affecter un nombre X de carnets à un collecteur.
     */
    public function assignCarnets(Request $request)
    {
        $request->validate([
            'collector_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'daily_rate' => 'required|numeric',
        ]);

        $collector = User::findOrFail($request->collector_id);
        $quantity = (int) $request->quantity;
        $dailyRate = $request->daily_rate;

        $availableCarnets = Carnet::whereNull('client_id')
            ->whereNull('collector_id')
            ->whereHas('lot', function($query) use ($dailyRate) {
                $query->where('daily_rate', $dailyRate)
                      ->byLocation();
            })
            ->orderBy('created_at', 'asc')
            ->take($quantity)
            ->get();

        if ($availableCarnets->count() < $quantity) {
            return redirect()->back()->withErrors([
                'assignment_error' => "Stock général insuffisant. Seulement {$availableCarnets->count()} carnets de tarif {$dailyRate} FCFA disponibles au siège."
            ]);
        }

        DB::transaction(function () use ($collector, $availableCarnets, $quantity) {
            foreach ($availableCarnets as $carnet) {
                $carnet->update([
                    'collector_id' => $collector->id,
                    'status' => 'assigned_to_collector'
                ]);
            }

            $collector->increment('carnet_stock', $quantity);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'ASSIGN_CARNETS',
                'description' => "Affectation de {$quantity} carnets au collecteur {$collector->firstname} {$collector->name}.",
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->back()->with('success', "{$quantity} carnets ont été affectés avec succès au collecteur {$collector->firstname} {$collector->name}.");
    }

    /**
     * Display details for a specific carnet.
     */
    public function showCarnet($id)
    {
        $carnet = Carnet::whereHas('lot', function($q) { $q->byLocation(); })->with(['client.contributions', 'collector', 'lot'])->findOrFail($id);
        return view('admin.carnets.show', compact('carnet'));
    }

    /**
     * Update carnet lot penalty amount.
     */
    public function updateLotPenalty(Request $request, $id)
    {
        $request->validate([
            'penalty_amount' => 'required|numeric|min:0'
        ]);

        $lot = CarnetLot::byLocation()->findOrFail($id);
        $lot->penalty_amount = $request->penalty_amount;
        $lot->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_PENALTY',
            'description' => "Le montant de la pénalité pour le lot {$lot->lot_number} a été mis à jour à {$request->penalty_amount} FCFA.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Montant de la pénalité mis à jour avec succès.');
    }
}
