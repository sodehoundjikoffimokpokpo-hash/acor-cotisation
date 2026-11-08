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
    Schema::create('caisse_closures', function (Blueprint $table) {
        $table->id();
        $table->foreignId('collector_id')->constrained('users'); // Le collecteur qui clôture
        $table->date('closure_date'); // Date de la journée clôturée
        $table->integer('carnets_vendus');
        $table->integer('nb_cotisations');
        $table->decimal('montant_total', 15, 2);
        $table->string('status')->default('pending'); // pending, validated
        $table->foreignId('validated_by')->nullable()->constrained('users'); // Admin qui valide
        $table->timestamp('validated_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisse_closures');
    }
};
