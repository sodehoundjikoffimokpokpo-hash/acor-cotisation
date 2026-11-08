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
            $table->boolean('can_manage_lots')->default(false)->after('can_view_audit_logs');
            $table->boolean('can_manage_locations')->default(false)->after('can_manage_lots');
            $table->boolean('can_manage_backups')->default(false)->after('can_manage_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_manage_lots', 'can_manage_locations', 'can_manage_backups']);
        });
    }
};
