<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Carnet;
use App\Models\Contribution;
use App\Models\AuditLog;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use App\Models\CarnetLot;
use App\Models\Article;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        
        // Vide les tables de données sauf la table User et les tables géographiques si elles sont liées
        Client::truncate();
        Carnet::truncate();
        Contribution::truncate();
        CarnetLot::truncate();
        Article::truncate();
        AuditLog::truncate();

        // 1. Initialise ou récupère les Pays, Régions et Zones
        if (Country::count() === 0) {
            $togo = Country::create([
                'name' => 'Togo',
                'currency_code' => 'FCFA',
                'tax_rate' => 0.00,
            ]);

            $benin = Country::create([
                'name' => 'Bénin',
                'currency_code' => 'FCFA',
                'tax_rate' => 0.00,
            ]);

            $maritime = Region::create(['country_id' => $togo->id, 'name' => 'Région Maritime']);
            $plateaux = Region::create(['country_id' => $togo->id, 'name' => 'Région des Plateaux']);
            $littoral = Region::create(['country_id' => $benin->id, 'name' => 'Littoral']);

            $lomeZone = Zone::create(['region_id' => $maritime->id, 'name' => 'Lomé Adidogomé']);
            $kpalimeZone = Zone::create(['region_id' => $plateaux->id, 'name' => 'Kpalimé Centre']);
            $cotonouZone = Zone::create(['region_id' => $littoral->id, 'name' => 'Cotonou Fidjrossè']);
        } else {
            $togo = Country::where('name', 'Togo')->first() ?: Country::first();
            $benin = Country::where('name', 'Bénin')->first() ?: Country::skip(1)->first() ?: Country::first();
            $maritime = Region::where('name', 'Région Maritime')->first() ?: Region::first();
            $littoral = Region::where('name', 'Littoral')->first() ?: Region::first();
            $lomeZone = Zone::where('name', 'Lomé Adidogomé')->first() ?: Zone::first();
            $cotonouZone = Zone::where('name', 'Cotonou Fidjrossè')->first() ?: Zone::first();
        }

        // 2. Initialise ou récupère les Utilisateurs (sans vider la table user)
        if (User::count() === 0) {
            $admin = User::create([
                'name' => 'ADMINISTRATEUR',
                'firstname' => 'Acor',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]);

            $secretary = User::create([
                'name' => 'SECRETAIRE',
                'firstname' => 'Julie',
                'username' => 'sec1',
                'password' => Hash::make('password'),
                'role' => 'secretary',
                'country_id' => $togo->id,
                'region_id' => $maritime->id,
                'is_active' => true,
            ]);

            $collector1 = User::create([
                'name' => 'COLLECTEUR 1',
                'firstname' => 'Michel',
                'username' => 'col1',
                'password' => Hash::make('password'),
                'role' => 'collector',
                'activity_zone' => $lomeZone->name,
                'activity_country' => $togo->name,
                'country_id' => $togo->id,
                'region_id' => $maritime->id,
                'zone_id' => $lomeZone->id,
                'carnet_stock' => 10,
                'is_active' => true,
            ]);

            $collector2 = User::create([
                'name' => 'COLLECTEUR 2',
                'firstname' => 'Pascal',
                'username' => 'col2',
                'password' => Hash::make('password'),
                'role' => 'collector',
                'activity_zone' => $cotonouZone->name,
                'activity_country' => $benin->name,
                'country_id' => $benin->id,
                'region_id' => $littoral->id,
                'zone_id' => $cotonouZone->id,
                'carnet_stock' => 5,
                'is_active' => true,
            ]);
        } else {
            $admin = User::where('role', 'admin')->first() ?: User::first();
            $collector1 = User::where('role', 'collector')->where('username', 'col1')->first() ?: User::where('role', 'collector')->first();
            $collector2 = User::where('role', 'collector')->where('username', 'col2')->first() ?: User::where('role', 'collector')->skip(1)->first() ?: User::where('role', 'collector')->first();
        }

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 3. Crée les Articles pour stock physique de livraison
        $article1 = Article::create([
            'name' => 'Radio Récepteur',
            'price' => 15000.00,
            'stock_quantity' => 15,
            'country_id' => $togo->id,
            'region_id' => $maritime->id,
            'zone_id' => $lomeZone->id,
            'purchase_price' => 10000.00,
            'transport_fees' => 1000.00,
            'search_fees' => 500.00,
            'collector_commission' => 1000.00,
            'admin_fees' => 500.00,
            'price_provision' => 1000.00,
            'acor_margin' => 1000.00,
        ]);

        $article2 = Article::create([
            'name' => 'Ventilateur Binatone',
            'price' => 10000.00,
            'stock_quantity' => 10,
            'country_id' => $benin->id,
            'region_id' => $littoral->id,
            'zone_id' => $cotonouZone->id,
            'purchase_price' => 6000.00,
            'transport_fees' => 800.00,
            'search_fees' => 400.00,
            'collector_commission' => 800.00,
            'admin_fees' => 400.00,
            'price_provision' => 800.00,
            'acor_margin' => 800.00,
        ]);

        // 4. Crée les Lots de Carnets adaptés aux modifications
        $lot1 = CarnetLot::create([
            'lot_number' => 'LOT-TOGO-001',
            'batch_number' => 'BATCH-TOGO-001',
            'initial_quantity' => 100,
            'remaining_quantity' => 97,
            'purchase_date' => Carbon::now()->subMonths(2),
            'expiration_date' => Carbon::now()->addMonths(4),
            'penalty_amount' => 5000.00,
            'penalty_applied' => false,
            'country_id' => $togo->id,
            'region_id' => $maritime->id,
            'zone_id' => $lomeZone->id,
            'card_cost' => 2000.00,
            'days_covered' => 100,
            'daily_rate' => 150.00,
            'article_name' => 'Radio Récepteur',
            'purchase_price' => 10000.00,
            'transport_fees' => 1000.00,
            'search_fees' => 500.00,
            'collector_commission' => 1000.00,
            'admin_fees' => 500.00,
            'price_provision' => 1000.00,
            'acor_margin' => 1000.00,
            'final_price' => 15000.00,
        ]);

        $lot2 = CarnetLot::create([
            'lot_number' => 'LOT-BENIN-001',
            'batch_number' => 'BATCH-BENIN-001',
            'initial_quantity' => 50,
            'remaining_quantity' => 49,
            'purchase_date' => Carbon::now()->subMonths(1),
            'expiration_date' => Carbon::now()->addMonths(5),
            'penalty_amount' => 3000.00,
            'penalty_applied' => false,
            'country_id' => $benin->id,
            'region_id' => $littoral->id,
            'zone_id' => $cotonouZone->id,
            'card_cost' => 1500.00,
            'days_covered' => 100,
            'daily_rate' => 100.00,
            'article_name' => 'Ventilateur Binatone',
            'purchase_price' => 6000.00,
            'transport_fees' => 800.00,
            'search_fees' => 400.00,
            'collector_commission' => 800.00,
            'admin_fees' => 400.00,
            'price_provision' => 800.00,
            'acor_margin' => 800.00,
            'final_price' => 10000.00,
        ]);

        // 5. Crée les Clients
        $client1 = Client::create([
            'name' => 'MENSAH',
            'firstname' => 'Koffi',
            'phone' => '+22890123456',
            'address' => 'Quartier Adidogomé, Lomé',
            'daily_contribution' => 150.00,
            'target_amount' => 15000.00,
            'desired_article' => 'Radio Récepteur',
            'status' => 'active',
            'collector_id' => $collector1->id,
            'country_id' => $togo->id,
            'region_id' => $maritime->id,
            'zone_id' => $lomeZone->id,
        ]);

        $client2 = Client::create([
            'name' => 'BELLO',
            'firstname' => 'Amina',
            'phone' => '+22997123456',
            'address' => 'Quartier Fidjrossè, Cotonou',
            'daily_contribution' => 100.00,
            'target_amount' => 10000.00,
            'desired_article' => 'Ventilateur Binatone',
            'status' => 'active',
            'collector_id' => $collector2->id,
            'country_id' => $benin->id,
            'region_id' => $littoral->id,
            'zone_id' => $cotonouZone->id,
        ]);

        $client3 = Client::create([
            'name' => 'AGBOVI',
            'firstname' => 'Yawovi',
            'phone' => '+22891876543',
            'address' => 'Quartier Deckon, Lomé',
            'daily_contribution' => 150.00,
            'target_amount' => 15000.00,
            'desired_article' => 'Radio Récepteur',
            'status' => 'active',
            'collector_id' => $collector1->id,
            'country_id' => $togo->id,
            'region_id' => $maritime->id,
            'zone_id' => $lomeZone->id,
        ]);

        // 6. Associe les Carnets
        Carnet::create([
            'client_id' => $client1->id,
            'carnet_lot_id' => $lot1->id,
            'collector_id' => $collector1->id,
            'number' => 'CARNET-0001',
            'status' => 'active',
        ]);

        Carnet::create([
            'client_id' => $client2->id,
            'carnet_lot_id' => $lot2->id,
            'collector_id' => $collector2->id,
            'number' => 'CARNET-0002',
            'status' => 'active',
        ]);

        Carnet::create([
            'client_id' => $client3->id,
            'carnet_lot_id' => $lot1->id,
            'collector_id' => $collector1->id,
            'number' => 'CARNET-0003',
            'status' => 'active',
        ]);

        // Crée des carnets en stock pour les collecteurs
        for ($i = 4; $i <= 13; $i++) {
            Carnet::create([
                'carnet_lot_id' => $lot1->id,
                'collector_id' => $collector1->id,
                'number' => 'CARNET-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'status' => 'assigned_to_collector',
            ]);
        }

        for ($i = 14; $i <= 18; $i++) {
            Carnet::create([
                'carnet_lot_id' => $lot2->id,
                'collector_id' => $collector2->id,
                'number' => 'CARNET-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'status' => 'assigned_to_collector',
            ]);
        }

        // 7. Crée des Cotisations cohérentes avec la mise journalière

        // Koffi MENSAH (Mise : 150 FCFA, 14 Validées, 1 En attente)
        for ($day = 15; $day >= 2; $day--) {
            Contribution::create([
                'client_id' => $client1->id,
                'collector_id' => $collector1->id,
                'amount' => 150.00,
                'payment_date' => Carbon::now()->subDays($day)->toDateString(),
                'validation_status' => 'validated',
                'validated_by' => $admin->id,
                'validated_at' => Carbon::now()->subDays($day),
                'created_at' => Carbon::now()->subDays($day),
            ]);
        }
        Contribution::create([
            'client_id' => $client1->id,
            'collector_id' => $collector1->id,
            'amount' => 150.00,
            'payment_date' => Carbon::now()->toDateString(),
            'validation_status' => 'pending',
            'created_at' => Carbon::now(),
        ]);

        // Amina BELLO (Mise : 100 FCFA, 9 Validées, 1 En attente)
        for ($day = 10; $day >= 2; $day--) {
            Contribution::create([
                'client_id' => $client2->id,
                'collector_id' => $collector2->id,
                'amount' => 100.00,
                'payment_date' => Carbon::now()->subDays($day)->toDateString(),
                'validation_status' => 'validated',
                'validated_by' => $admin->id,
                'validated_at' => Carbon::now()->subDays($day),
                'created_at' => Carbon::now()->subDays($day),
            ]);
        }
        Contribution::create([
            'client_id' => $client2->id,
            'collector_id' => $collector2->id,
            'amount' => 100.00,
            'payment_date' => Carbon::now()->toDateString(),
            'validation_status' => 'pending',
            'created_at' => Carbon::now(),
        ]);

        // Yawovi AGBOVI (Mise : 150 FCFA, 100 Validées => Programme terminé !)
        for ($day = 100; $day >= 1; $day--) {
            Contribution::create([
                'client_id' => $client3->id,
                'collector_id' => $collector1->id,
                'amount' => 150.00,
                'payment_date' => Carbon::now()->subDays($day)->toDateString(),
                'validation_status' => 'validated',
                'validated_by' => $admin->id,
                'validated_at' => Carbon::now()->subDays($day),
                'created_at' => Carbon::now()->subDays($day),
            ]);
        }

        // Met à jour les statuts d'objectif
        $client1->checkGoalStatus();
        $client2->checkGoalStatus();
        $client3->checkGoalStatus();

        // 8. Log d'initialisation
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'SYSTEM_INIT',
            'description' => "Initialisation du système avec données adaptées et cohérentes aux mises journalières.",
            'ip_address' => '127.0.0.1',
        ]);
    }
}
