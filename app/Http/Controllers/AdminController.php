<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Contribution;
use App\Models\AuditLog;
use App\Models\Carnet;
use App\Models\CaisseClosure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display the Admin Dashboard with real-time statistics.
     */
    public function dashboard()
    {
        // --- 1. STATISTIQUES DES CLIENTS ---
        $totalClientsCount = Client::byLocation()->count();

        // Nouveaux clients inscrits au cours des 7 derniers jours
        $newClientsCount = Client::byLocation()->where('created_at', '>=', now()->subDays(7))->count();

        $activeClientsCount = Client::byLocation()->where('status', 'active')->count();
        $goalsReachedCount = Client::byLocation()->where('status', 'goal_reached')->count();

        // --- 2. STATISTIQUES DES COTISATIONS & ENCAISSEMENTS ---
        $totalContributionsCount = Contribution::whereHas('client', function($q) { $q->byLocation(); })->count();
        $totalCollected = (float) Contribution::whereHas('client', function($q) { $q->byLocation(); })->sum('amount');
        $totalValidated = (float) Contribution::whereHas('client', function($q) { $q->byLocation(); })->where('validation_status', 'validated')->sum('amount');

        $pendingValidationCount = Contribution::whereHas('client', function($q) { $q->byLocation(); })->where('validation_status', 'pending')->count();
        $pendingFunds = (float) Contribution::whereHas('client', function($q) { $q->byLocation(); })->where('validation_status', 'pending')->sum('amount');

        // --- 3. STATISTIQUES DES COLLECTEURS ---
        $activeCollectorsCount = User::byLocation()->where('role', 'collector')->where('is_active', true)->count();

        // --- 4. STATISTIQUES DES CARNETS ---
        $carnetsInStockCount = Carnet::whereHas('lot', function($q) { $q->byLocation(); })->whereNull('client_id')->count();
        $carnetsSoldCount = Carnet::whereHas('lot', function($q) { $q->byLocation(); })->whereNotNull('client_id')->count();

        // --- 5. SUIVI DES ARTICLES / LOTS ---
        $articlesReadyCount = Client::byLocation()->where('status', 'goal_reached')->count();
        $deliveredArticlesCount = Client::byLocation()->where('status', 'completed')->count();

        // --- 6. LEADERBOARD DES COLLECTEURS (TOP 5) ---
        $contributionsByCollector = User::byLocation()->where('role', 'collector')
            ->withSum(['contributions' => function($q) {
                $q->where('validation_status', 'validated');
            }], 'amount')
            ->get(['id', 'firstname', 'name'])
            ->map(function($user) {
                return [
                    'name' => $user->firstname . ' ' . $user->name,
                    'total' => (float) ($user->contributions_sum_amount ?? 0)
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // --- 6B. EVOLUTION MENSUELLE DES COTISATIONS VALIDÉES ---
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $monthlyTrendData = Contribution::whereHas('client', function($q) { $q->byLocation(); })
                ->where('validation_status', 'validated')
                ->selectRaw('strftime("%Y-%m", payment_date) as month, SUM(amount) as total')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
        } else {
            $monthlyTrendData = Contribution::whereHas('client', function($q) { $q->byLocation(); })
                ->where('validation_status', 'validated')
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
        }

        $monthlyTrendLabels = [];
        $monthlyTrendAmounts = [];
        foreach ($monthlyTrendData as $item) {
            $monthlyTrendLabels[] = Carbon::parse($item->month . '-01')->translatedFormat('F Y');
            $monthlyTrendAmounts[] = (float) $item->total;
        }

        // --- 6C. RÉPARTITION DES ARTICLES DEMANDÉS (TOP 5) ---
        $articlesShare = Client::byLocation()->selectRaw('desired_article as article, COUNT(*) as count')
            ->groupBy('desired_article')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        $articlesShareLabels = $articlesShare->pluck('article')->toArray();
        $articlesShareCounts = $articlesShare->pluck('count')->toArray();

        // --- 7. ALERTES SUR LES RETARDS ---
        // Clients actifs qui n'ont enregistré aucun versement validé aujourd'hui
        $aujourdhui = now()->startOfDay();
        $lateClientsCount = Client::byLocation()->where('status', 'active')
            ->whereDoesntHave('contributions', function ($query) use ($aujourdhui) {
                $query->where('validation_status', 'validated')
                      ->where('payment_date', '>=', $aujourdhui);
            })->count();

        // --- 8. RÉCUPÉRATION DES LISTES ---
        $clients = Client::byLocation()->with(['collector', 'activeCarnet', 'contributions'])->orderBy('created_at', 'desc')->get();
        $auditLogs = AuditLog::whereHas('user', function($q) {
                $q->byLocation();
            })->with('user')->orderBy('created_at', 'desc')->take(10)->get();
        $countries = \App\Models\Country::all();

        return view('admin.dashboard', compact(
            'totalClientsCount',
            'newClientsCount',
            'activeClientsCount',
            'goalsReachedCount',
            'totalContributionsCount',
            'totalCollected',
            'totalValidated',
            'pendingValidationCount',
            'pendingFunds',
            'activeCollectorsCount',
            'carnetsInStockCount',
            'carnetsSoldCount',
            'articlesReadyCount',
            'deliveredArticlesCount',
            'contributionsByCollector',
            'monthlyTrendLabels',
            'monthlyTrendAmounts',
            'articlesShareLabels',
            'articlesShareCounts',
            'lateClientsCount',
            'clients',
            'auditLogs',
            'countries'
        ));
    }

    /**
     * Show validation page.
     */
    public function validation()
    {
        // Récupération des contributions en attente filtrées par localisation
        $pendingContributions = Contribution::with(['client.activeCarnet', 'collector'])
            ->whereHas('client', function($q) {
                $q->byLocation();
            })
            ->where('validation_status', 'pending')
            ->orderBy('payment_date', 'asc')
            ->get();

        // Calcul du montant total des contributions en attente
        $totalPendingAmount = (float) $pendingContributions->sum('amount');

        // Récupération des clôtures de caisse en attente filtrées par localisation
        $caissesEnAttente = CaisseClosure::whereHas('collector', function($q) {
                $q->byLocation();
            })
            ->with('collector')
            ->where('status', 'pending')
            ->get();

        // Retour de la vue avec les variables compactées
        return view('admin.validation', compact('pendingContributions', 'totalPendingAmount', 'caissesEnAttente'));
    }

    /**
     * Approve a single contribution.
     */
    public function approveContribution(Request $request, $id)
    {
        $contribution = Contribution::findOrFail($id);
        $contribution->update([
            'validation_status' => 'validated',
            'validated_by' => Auth::id(),
            'validated_at' => Carbon::now(),
        ]);

        event(new \App\Events\ContributionValidated($contribution));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'VALIDATE_DAILY_FUNDS',
            'description' => "Validation du versement de {$contribution->amount} FCFA pour le client {$client->firstname} {$client->name}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.validation')->with('success', __('messages.validation_success'));
    }

    /**
     * Approve all pending contributions.
     */
    public function approveAllContributions(Request $request)
    {
        $pending = Contribution::whereHas('client', function($q) {
            $q->byLocation();
        })->where('validation_status', 'pending')->get();

        if ($pending->isEmpty()) {
            return redirect()->route('admin.validation')->with('error', 'Aucun versement en attente.');
        }

        $totalApproved = 0;
        foreach ($pending as $contribution) {
            $contribution->update([
                'validation_status' => 'validated',
                'validated_by' => Auth::id(),
                'validated_at' => Carbon::now(),
            ]);

            event(new \App\Events\ContributionValidated($contribution));
            $totalApproved += $contribution->amount;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'VALIDATE_DAILY_FUNDS',
            'description' => "Validation groupée de tous les versements en attente. Total validé: {$totalApproved} FCFA.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.validation')->with('success', __('messages.validation_success'));
    }

    /**
     * Display finance accounting dashboard.
     */
    public function finance()
    {
        // 1. Chiffre d'Affaires Global Encaissé (Validé)
        $caTotal = Contribution::whereHas('client', function($q) { $q->byLocation(); })->where('validation_status', 'validated')->sum('amount');

        // 2. Chiffre d'Affaires Non-Validé (En attente)
        $caEnAttente = Contribution::whereHas('client', function($q) { $q->byLocation(); })->where('validation_status', 'pending')->sum('amount');

        // 3. Commissions Totales à verser aux Collecteurs
        // Supposons une commission fixe de 5% du montant validé par défaut
        $commissionsTotales = $caTotal * 0.05;

        // 4. Marge Nette Estimée (Revenu validé - Commission collecteurs)
        $margeNette = $caTotal - $commissionsTotales;

        // 5. Récupération des historiques de versement validés par jour pour le graphique
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $versementsChart = Contribution::whereHas('client', function($q) { $q->byLocation(); })
                ->where('validation_status', 'validated')
                ->selectRaw('payment_date as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->take(15)
                ->get();
        } else {
            $versementsChart = Contribution::whereHas('client', function($q) { $q->byLocation(); })
                ->where('validation_status', 'validated')
                ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->take(15)
                ->get();
        }

        $chartLabels = [];
        $chartData = [];
        foreach ($versementsChart as $v) {
            $chartLabels[] = Carbon::parse($v->date)->format('d M');
            $chartData[] = (float) $v->total;
        }

        // 6. Tableau récapitulatif par collecteur
        $collectorsPerformance = User::byLocation()->where('role', 'collector')
            ->withSum(['contributions' => function($q) {
                $q->where('validation_status', 'validated');
            }], 'amount')
            ->withCount(['clients' => function($q) {
                $q->where('status', 'active');
            }])
            ->get();

        return view('admin.finance', compact(
            'caTotal',
            'caEnAttente',
            'commissionsTotales',
            'margeNette',
            'chartLabels',
            'chartData',
            'collectorsPerformance'
        ));
    }

    /**
     * Update client details.
     */
    public function updateClient(Request $request, Client $client)
    {
        $oldValues = $client->toArray();

        $client->update($request->only([
            'name',
            'firstname',
            'phone',
            'address',
            'daily_contribution',
            'target_amount',
            'desired_article',
            'status'
        ]));

        $newValues = $client->fresh()->toArray();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE',
            'model_type' => get_class($client),
            'model_id' => $client->id,
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Display activities history.
     */
    public function historique()
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.historique', compact('logs'));
    }

    /**
     * Close collector cash register daily.
     */
    public function cloturerCaisse()
    {
        $collectorId = auth()->id();
        $today = Carbon::today();

        $nbCotisations = Contribution::where('collector_id', $collectorId)
            ->whereDate('created_at', $today)
            ->count();

        $montantTotal = Contribution::where('collector_id', $collectorId)
            ->whereDate('created_at', $today)
            ->sum('amount');

        $carnetsVendus = Carnet::where('collector_id', $collectorId)
            ->whereDate('created_at', $today)
            ->count();

        CaisseClosure::create([
            'collector_id'   => $collectorId,
            'closure_date'   => $today,
            'carnets_vendus' => $carnetsVendus,
            'nb_cotisations' => $nbCotisations,
            'montant_total'  => $montantTotal,
            'status'         => 'pending',
        ]);

        AuditLog::logAction(
            'CLOTURE_CAISSE',
            "Le collecteur " . auth()->user()->firstname . " a clôturé sa caisse pour le " . $today->format('d/m/Y')
        );

        return redirect()->back()->with('success', 'Caisse clôturée avec succès, en attente de validation.');
    }

    /**
     * Validate collector cash register closure.
     */
    public function validerCaisse($id)
    {
        $caisse = CaisseClosure::findOrFail($id);

        DB::transaction(function () use ($caisse) {
            $caisse->status = 'validated';
            $caisse->save();

            $contributions = Contribution::where('collector_id', $caisse->collector_id)
                ->whereDate('created_at', Carbon::parse($caisse->closure_date)->toDateString())
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
                'action' => 'VALIDATE_DAILY_FUNDS',
                'description' => "Validation de la clôture de caisse pour le collecteur " . $caisse->collector->firstname . " " . $caisse->collector->name . " du " . Carbon::parse($caisse->closure_date)->format('d/m/Y') . " (Montant total: {$caisse->montant_total} FCFA).",
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->back()->with('success', __('messages.caisse_validation_success'));
    }

    /**
     * Filter active country session variable.
     */
    public function setActiveCountry(Request $request)
    {
        $request->validate([
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($request->filled('country_id')) {
            session(['active_country_id' => $request->country_id]);
        } else {
            session()->forget(['active_country_id', 'active_region_id', 'active_zone_id']);
        }

        return redirect()->back()->with('success', 'Pays filtré avec succès.');
    }

    /**
     * Compare different rates reports and exports.
     */
    public function reportTarifs(Request $request)
    {
        $clients100 = Client::byLocation()->where('daily_contribution', 100.00)->get();
        $count100 = $clients100->count();
        $target100 = $clients100->sum('target_amount');
        $paid100 = $clients100->sum(function($c) { return $c->totalValidatedPaid(); });
        $pct100 = $target100 > 0 ? ($paid100 / $target100) * 100 : 0;

        $clients150 = Client::byLocation()->where('daily_contribution', 150.00)->get();
        $count150 = $clients150->count();
        $target150 = $clients150->sum('target_amount');
        $paid150 = $clients150->sum(function($c) { return $c->totalValidatedPaid(); });
        $pct150 = $target150 > 0 ? ($paid150 / $target150) * 100 : 0;

        $report = [
            'rate100' => [
                'count' => $count100,
                'target' => $target100,
                'paid' => $paid100,
                'pct' => $pct100
            ],
            'rate150' => [
                'count' => $count150,
                'target' => $target150,
                'paid' => $paid150,
                'pct' => $pct150
            ]
        ];

        if ($request->input('export') === 'csv') {
            $headers = [
                "Content-type"        => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=comparatif_tarifs_" . date('Ymd') . ".csv",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use ($report) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, ['Tarif Journalier', 'Nombre de Clients', 'Objectif Cumulé (FCFA)', 'Montant Validé Encaissé (FCFA)', 'Taux de Réalisation (%)']);
                fputcsv($file, ['100 FCFA', $report['rate100']['count'], $report['rate100']['target'], $report['rate100']['paid'], round($report['rate100']['pct'], 2)]);
                fputcsv($file, ['150 FCFA', $report['rate150']['count'], $report['rate150']['target'], $report['rate150']['paid'], round($report['rate150']['pct'], 2)]);
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.reports.tarifs', compact('report'));
    }

    /**
     * Display custom dashboard for normal employee.
     */
    public function employeeDashboard()
    {
        $user = Auth::user();
        return view('employee.dashboard', compact('user'));
    }
}
