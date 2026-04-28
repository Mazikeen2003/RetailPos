<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'vatable_sales')) {
                $table->decimal('vatable_sales', 10, 2)->default(0)->after('discount_amount');
            }

            if (!Schema::hasColumn('sales', 'vat_amount')) {
                $table->decimal('vat_amount', 10, 2)->default(0)->after('vatable_sales');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'vat_amount')) {
                $table->dropColumn('vat_amount');
            }

            if (Schema::hasColumn('sales', 'vatable_sales')) {
                $table->dropColumn('vatable_sales');
            }
        });
    }
};
