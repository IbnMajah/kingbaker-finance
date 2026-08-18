<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'hr_cashier_id')) {
                $table->foreignId('hr_cashier_id')->nullable()->after('cashier')->constrained('hr_cashiers')->nullOnDelete();
            }
            if (! Schema::hasColumn('sales', 'actual_total')) {
                $table->decimal('actual_total', 14, 2)->nullable()->after('total_amount');
            }
        });

        Schema::table('cashier_reconciliations', function (Blueprint $table) {
            if (! Schema::hasColumn('cashier_reconciliations', 'sale_id')) {
                $table->foreignId('sale_id')->nullable()->after('uuid')->constrained()->restrictOnDelete();
                $table->unique('sale_id');
            }

            if (Schema::hasIndex('cashier_reconciliations', 'cashier_reconciliations_pos_unique')) {
                $table->dropUnique('cashier_reconciliations_pos_unique');
            }
        });

        if (Schema::hasColumn('cashier_reconciliations', 'pos_variance')
            || Schema::hasColumn('cashier_reconciliations', 'variance_matches_receipt')) {
            Schema::table('cashier_reconciliations', function (Blueprint $table) {
                $columns = array_values(array_filter([
                    Schema::hasColumn('cashier_reconciliations', 'pos_variance') ? 'pos_variance' : null,
                    Schema::hasColumn('cashier_reconciliations', 'variance_matches_receipt') ? 'variance_matches_receipt' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('cashier_reconciliations', function (Blueprint $table) {
            if (Schema::hasColumn('cashier_reconciliations', 'sale_id')) {
                $table->dropConstrainedForeignId('sale_id');
            }
            if (! Schema::hasColumn('cashier_reconciliations', 'pos_variance')) {
                $table->decimal('pos_variance', 14, 2)->nullable();
            }
            if (! Schema::hasColumn('cashier_reconciliations', 'variance_matches_receipt')) {
                $table->boolean('variance_matches_receipt')->default(true);
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'hr_cashier_id')) {
                $table->dropConstrainedForeignId('hr_cashier_id');
            }
            if (Schema::hasColumn('sales', 'actual_total')) {
                $table->dropColumn('actual_total');
            }
        });
    }
};
