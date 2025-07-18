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
        // First, expand the enum to include both old and new values
        DB::statement("ALTER TABLE deposits MODIFY COLUMN deposit_type ENUM('cash', 'cash_deposit', 'bank_transfer', 'nafa', 'nafa_deposit', 'wave', 'wave_deposit', 'cheque', 'cheque_deposit', 'sales', 'daily_sales_deposit')");

        // Now update existing data to use new enum values
        DB::table('deposits')->where('deposit_type', 'cash')->update(['deposit_type' => 'cash_deposit']);
        DB::table('deposits')->where('deposit_type', 'nafa')->update(['deposit_type' => 'nafa_deposit']);
        DB::table('deposits')->where('deposit_type', 'wave')->update(['deposit_type' => 'wave_deposit']);
        DB::table('deposits')->where('deposit_type', 'cheque')->update(['deposit_type' => 'cheque_deposit']);
        DB::table('deposits')->where('deposit_type', 'sales')->update(['deposit_type' => 'daily_sales_deposit']);

        // Finally, remove the old enum values
        DB::statement("ALTER TABLE deposits MODIFY COLUMN deposit_type ENUM('cash_deposit', 'bank_transfer', 'nafa_deposit', 'wave_deposit', 'cheque_deposit', 'daily_sales_deposit')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data back to old format
        DB::table('deposits')->where('deposit_type', 'cash_deposit')->update(['deposit_type' => 'cash']);
        DB::table('deposits')->where('deposit_type', 'nafa_deposit')->update(['deposit_type' => 'nafa']);
        DB::table('deposits')->where('deposit_type', 'wave_deposit')->update(['deposit_type' => 'wave']);
        DB::table('deposits')->where('deposit_type', 'cheque_deposit')->update(['deposit_type' => 'cheque']);
        DB::table('deposits')->where('deposit_type', 'daily_sales_deposit')->update(['deposit_type' => 'sales']);

        // Revert enum back to old values
        DB::statement("ALTER TABLE deposits MODIFY COLUMN deposit_type ENUM('cash', 'bank_transfer', 'nafa', 'wave', 'cheque', 'sales')");
    }
};
