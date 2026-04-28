<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'voided_by_id')) {
                $table->foreignId('voided_by_id')
                    ->nullable()
                    ->after('reprinted')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('sales', 'void_reason')) {
                $table->text('void_reason')->nullable()->after('voided_by_id');
            }

            if (!Schema::hasColumn('sales', 'voided_at')) {
                $table->timestamp('voided_at')->nullable()->after('void_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'voided_by_id')) {
                $table->dropConstrainedForeignId('voided_by_id');
            }

            if (Schema::hasColumn('sales', 'void_reason')) {
                $table->dropColumn('void_reason');
            }

            if (Schema::hasColumn('sales', 'voided_at')) {
                $table->dropColumn('voided_at');
            }
        });
    }
};
