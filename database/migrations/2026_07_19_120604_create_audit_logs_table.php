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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Who performed the action
            $table->string('action'); // e.g., 'CREATE_CLIENT', 'RECORD_CONTRIBUTION', 'VALIDATE_DAILY_FUNDS'
            $table->text('description'); // Detailed details
            $table->string('ip_address')->nullable();
            $table->timestamps(); // includes created_at (log time)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
