<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CarnetLot;
use App\Models\User;
use App\Notifications\AcorNotification;
use Carbon\Carbon;

class CheckExpiredLots extends Command
{
    // Signature de la commande pour la lancer manuellement au besoin (php artisan stock:check-expiration)
    protected $signature = 'stock:check-expiration';
    protected $description = 'Vérifie les lots expirés et applique automatiquement la pénalité configurée.';

    public function handle()
    {
        // Récupérer les lots expirés pour lesquels la pénalité n'a pas encore été appliquée
        $expiredLots = CarnetLot::where('expiration_date', '<', Carbon::now())
            ->where('penalty_applied', false)
            ->get();

        foreach ($expiredLots as $lot) {
            $lot->update(['penalty_applied' => true]);

            // En option : Mettre à jour le statut des carnets non vendus de ce lot en 'expired'
            $lot->carnets()->where('status', 'in_stock')->update(['status' => 'expired']);

            // Notify admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AcorNotification(
                    'Lot de Carnets Expiré',
                    "Le lot {$lot->lot_number} a expiré le " . $lot->expiration_date->format('d/m/Y') . ". Une pénalité de {$lot->penalty_amount} FCFA a été appliquée.",
                    'danger'
                ));
            }

            $this->info("Pénalité de {$lot->penalty_amount} FCFA appliquée avec succès au Lot N° {$lot->lot_number}.");
        }
    }
}
