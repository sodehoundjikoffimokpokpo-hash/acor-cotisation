<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('firstname');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->decimal('daily_contribution', 12, 2); // Daily contribution amount (e.g. 100.00 FCFA)
            $table->decimal('target_amount', 12, 2); // Target goal amount (e.g. 10000.00 FCFA)
            $table->string('desired_article'); // Desired article to purchase
            $table->string('status')->default('active'); // active, goal_reached, delivered
            $table->foreignId('collector_id')->constrained('users')->onDelete('restrict'); // Collector who registered this client
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
