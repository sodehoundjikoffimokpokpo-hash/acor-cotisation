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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_validate_contributions')->default(false)->after('role');
            $table->boolean('can_manage_collectors')->default(false)->after('can_validate_contributions');
            $table->boolean('can_view_audit_logs')->default(false)->after('can_manage_collectors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_validate_contributions', 'can_manage_collectors', 'can_view_audit_logs']);
        });
    }
};
