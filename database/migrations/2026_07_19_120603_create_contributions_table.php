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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('collector_id')->constrained('users')->onDelete('restrict');
            $table->decimal('amount', 12, 2); // Contribution amount (FCFA)
            $table->date('payment_date'); // Date of contribution
            $table->string('validation_status')->default('pending'); // pending, validated
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null'); // Administrator who validated
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
