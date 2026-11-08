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
    Schema::table('audit_logs', function (Blueprint $table) {
        $table->string('model_type')->nullable(); // Pour savoir si c'est un Client, un Carnet, etc.
        $table->unsignedBigInteger('model_id')->nullable(); // L'ID de l'élément modifié
        $table->json('old_values')->nullable(); // Stocke l'ancienne valeur
        $table->json('new_values')->nullable(); // Stocke la nouvelle valeur
    });
}

public function down(): void
{
    Schema::table('audit_logs', function (Blueprint $table) {
        $table->dropColumn(['model_type', 'model_id', 'old_values', 'new_values']);
    });
}
};
