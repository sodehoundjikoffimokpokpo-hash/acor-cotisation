<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CaisseClosure;
use App\Models\User;
use App\Models\Carnet;
use Carbon\Carbon;
use App\Notifications\AcorNotification;

class CheckAcorAlerts extends Command
{
    protected $signature = 'acor:check-alerts';
    protected $description = 'Vérifie les retards de clôture et carnets expirés';

    public function handle()
    {
        $this->info('Lancement de la vérification ACOR...');

        // 1. Vérification : Collecteurs n'ayant pas clôturé
        $collectors = User::where('role', 'collector')->get();
        foreach ($collectors as $collector) {
            if (!CaisseClosure::where('collector_id', $collector->id)
                ->whereDate('closure_date', Carbon::today())
                ->exists()) {

                // Notifier les admins
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new AcorNotification('Alerte Clôture', "Le collecteur {$collector->firstname} {$collector->name} n'a pas clôturé sa caisse aujourd'hui.", 'warning'));
                }

                // Notifier le collecteur
                $collector->notify(new AcorNotification('Action Requise', "Attention, vous n'avez pas encore clôturé votre caisse aujourd'hui.", 'warning'));
            }
        }

        // 2. Vérification : Collecteurs en rupture de stock de carnets
        $depletedCollectors = User::where('role', 'collector')->where('carnet_stock', '<=', 0)->get();
        foreach ($depletedCollectors as $collector) {
            $admins = User::whereIn('role', ['admin', 'secretary'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new AcorNotification('Rupture de Stock', "Le collecteur {$collector->firstname} {$collector->name} n'a plus de carnets en stock.", 'danger'));
            }
        }

        // 3. Vérification : Clients ayant atteint leur objectif (prêts pour livraison)
        $clientsPrets = \App\Models\Client::where('status', 'goal_reached')->get();
        if ($clientsPrets->count() > 0) {
            $secretaries = User::whereIn('role', ['admin', 'secretary'])->get();
            foreach ($secretaries as $sec) {
                $sec->notify(new AcorNotification('Livraison en Attente', "Il y a {$clientsPrets->count()} client(s) prêt(s) pour la livraison de leur article.", 'success'));
            }
        }

        $this->info('Vérifications terminées.');
    }
}
