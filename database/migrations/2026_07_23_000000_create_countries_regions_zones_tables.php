<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create countries table
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('currency_code')->default('FCFA');
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Create regions table
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // 3. Create zones table
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // 4. Update users table with locations
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
        });

        // 5. Update clients table with locations
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
        });

        // 6. Update carnet_lots table with locations & cost fields
        Schema::table('carnet_lots', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->decimal('card_cost', 12, 2)->default(1000.00);
            $table->integer('days_covered')->default(100);
            $table->decimal('daily_rate', 12, 2)->default(100.00);
        });

        // 7. Update articles table with locations & costing items
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->decimal('purchase_price', 12, 2)->default(0.00);
            $table->decimal('transport_fees', 12, 2)->default(0.00);
            $table->decimal('search_fees', 12, 2)->default(0.00);
            $table->decimal('collector_commission', 12, 2)->default(0.00);
            $table->decimal('admin_fees', 12, 2)->default(0.00);
            $table->decimal('price_provision', 12, 2)->default(0.00);
            $table->decimal('acor_margin', 12, 2)->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['zone_id']);
            $table->dropColumn([
                'country_id', 'region_id', 'zone_id',
                'purchase_price', 'transport_fees', 'search_fees',
                'collector_commission', 'admin_fees', 'price_provision', 'acor_margin'
            ]);
        });

        Schema::table('carnet_lots', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['zone_id']);
            $table->dropColumn([
                'country_id', 'region_id', 'zone_id',
                'card_cost', 'days_covered', 'daily_rate'
            ]);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['zone_id']);
            $table->dropColumn(['country_id', 'region_id', 'zone_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['zone_id']);
            $table->dropColumn(['country_id', 'region_id', 'zone_id']);
        });

        Schema::dropIfExists('zones');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }
};
