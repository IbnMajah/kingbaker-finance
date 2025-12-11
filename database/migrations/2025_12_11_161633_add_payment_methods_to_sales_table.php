<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add total amount (calculated from all payment methods)
            $table->decimal('total_amount', 12, 2)->default(0)->after('amount');

            // Add payment method amounts
            $table->decimal('cash_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('bank_transfer_amount', 12, 2)->default(0)->after('cash_amount');
            $table->decimal('mobile_money_my_nafa', 12, 2)->default(0)->after('bank_transfer_amount');
            $table->decimal('mobile_money_aps', 12, 2)->default(0)->after('mobile_money_my_nafa');
            $table->decimal('mobile_money_wave', 12, 2)->default(0)->after('mobile_money_aps');
        });

        // Copy existing amount values to total_amount for existing records
        // The amount column will represent depositable_amount going forward
        DB::statement('UPDATE sales SET total_amount = amount WHERE total_amount = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'cash_amount',
                'bank_transfer_amount',
                'mobile_money_my_nafa',
                'mobile_money_aps',
                'mobile_money_wave',
            ]);
        });
    }
};
