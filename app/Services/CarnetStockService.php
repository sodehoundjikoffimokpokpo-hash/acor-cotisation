<?php

namespace App\Services;

use App\Models\User;
use App\Models\Carnet;
use App\Models\CarnetLot;
use Illuminate\Support\Facades\DB;
use Exception;

class CarnetStockService
{
    /**
     * Affecter un lot de carnets du stock général vers un collecteur
     */
    public function assignCarnetsToCollector(User $collector, int $quantity)
    {
        // Vérifier si le stock général dispose d'assez de carnets disponibles
        $availableCarnets = Carnet::where('status', 'in_stock')
            ->orderBy('created_at', 'asc')
            ->take($quantity)
            ->get();

        if ($availableCarnets->count() < $quantity) {
            throw new Exception("Stock général insuffisant. Seulement {$availableCarnets->count()} carnets disponibles.");
        }

        DB::transaction(function () use ($collector, $availableCarnets, $quantity) {
            foreach ($availableCarnets as $carnet) {
                $carnet->update([
                    'collector_id' => $collector->id,
                    'status' => 'assigned_to_collector'
                ]);

                // Diminuer la quantité restante du lot associé
                if ($carnet->carnet_lot_id) {
                    $carnet->lot()->decrement('remaining_quantity');
                }
            }

            // Mettre à jour le stock personnel du collecteur
            $collector->increment('carnet_stock', $quantity);
        });
    }

    /**
     * Consommer automatiquement un carnet lors de la création d'un client
     */
    public function consumeCarnetForClient(User $collector)
    {
        // 1. Bloquer la création si le stock du collecteur est à 0
        if ($collector->carnet_stock <= 0) {
            throw new Exception("Action bloquée : Le collecteur {$collector->firstname} n'a plus de carnet disponible en stock.");
        }

        // 2. Récupérer le premier carnet disponible assigné à ce collecteur
        $carnet = Carnet::where('collector_id', $collector->id)
            ->where('status', 'assigned_to_collector')
            ->first();

        if (!$carnet) {
            throw new Exception("Erreur d'intégrité : Aucun carnet physique trouvé pour ce collecteur malgré son compteur positif.");
        }

        // 3. Procéder à la consommation dans une transaction sécurisée
        DB::transaction(function () use ($collector, $carnet) {
            $carnet->update(['status' => 'sold']);
            $collector->decrement('carnet_stock');
        });

        return $carnet;
    }
}
