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
        Schema::table('clients', function (Blueprint $table) {
            // Clé étrangère pointant vers la table articles
            $table->foreignId('article_id')->nullable()->constrained('articles')->nullOnDelete();
            // Date exacte de la remise de l'article au client
            $table->timestamp('delivered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropColumn(['article_id', 'delivered_at']);
        });
    }
};
