<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des lots de carnets
        Schema::create('carnet_lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number')->unique();
            $table->integer('initial_quantity');
            $table->integer('remaining_quantity');
            $table->date('purchase_date');
            $table->date('expiration_date');
            $table->decimal('penalty_amount', 12, 2)->default(5000.00); // Modifiable par l'admin
            $table->boolean('penalty_applied')->default(false);
            $table->string('batch_number')->unique();
            $table->timestamps();
        });

        // Mise à jour de la table existante des carnets
        Schema::table('carnets', function (Blueprint $table) {
            // Lier le carnet à un lot
            $table->foreignId('carnet_lot_id')->nullable()->constrained()->onDelete('set null');
            // Lier le carnet au collecteur qui détient le stock
            $table->foreignId('collector_id')->nullable()->constrained('users')->onDelete('set null');
            // Statuts possibles : 'in_stock', 'assigned_to_collector', 'sold', 'expired'
            $table->string('status')->default('in_stock')->change();
        });
    }

    public function down(): void
    {
        Schema::table('carnets', function (Blueprint $table) {
            $table->dropForeign(['carnet_lot_id']);
            $table->dropForeign(['collector_id']);
        });
        Schema::dropIfExists('carnet_lots');
    }
};
