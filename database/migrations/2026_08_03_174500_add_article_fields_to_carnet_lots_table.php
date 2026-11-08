<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carnet_lots', function (Blueprint $table) {
            $table->string('article_name')->nullable()->after('batch_number');
            $table->decimal('purchase_price', 12, 2)->default(0.00)->after('article_name');
            $table->decimal('transport_fees', 12, 2)->default(0.00)->after('purchase_price');
            $table->decimal('search_fees', 12, 2)->default(0.00)->after('transport_fees');
            $table->decimal('collector_commission', 12, 2)->default(0.00)->after('search_fees');
            $table->decimal('admin_fees', 12, 2)->default(0.00)->after('collector_commission');
            $table->decimal('price_provision', 12, 2)->default(0.00)->after('admin_fees');
            $table->decimal('acor_margin', 12, 2)->default(0.00)->after('price_provision');
            $table->decimal('final_price', 12, 2)->default(0.00)->after('acor_margin');
        });
    }

    public function down(): void
    {
        Schema::table('carnet_lots', function (Blueprint $table) {
            $table->dropColumn([
                'article_name', 'purchase_price', 'transport_fees', 'search_fees',
                'collector_commission', 'admin_fees', 'price_provision', 'acor_margin', 'final_price'
            ]);
        });
    }
};
