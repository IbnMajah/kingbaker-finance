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
        // Only proceed if deposit_type column exists (it may not exist if columns weren't renamed yet)
        if (!Schema::hasColumn('deposits', 'deposit_type')) {
            // Skip this migration - the columns haven't been renamed yet
            return;
        }

        $this->modifyDepositTypeEnum([
            'cash', 'cash_deposit', 'bank_transfer', 'nafa', 'nafa_deposit',
            'wave', 'wave_deposit', 'cheque', 'cheque_deposit', 'sales', 'daily_sales_deposit',
        ]);

        // Now update existing data to use new enum values
        DB::table('deposits')->where('deposit_type', 'cash')->update(['deposit_type' => 'cash_deposit']);
        DB::table('deposits')->where('deposit_type', 'nafa')->update(['deposit_type' => 'nafa_deposit']);
        DB::table('deposits')->where('deposit_type', 'wave')->update(['deposit_type' => 'wave_deposit']);
        DB::table('deposits')->where('deposit_type', 'cheque')->update(['deposit_type' => 'cheque_deposit']);
        DB::table('deposits')->where('deposit_type', 'sales')->update(['deposit_type' => 'daily_sales_deposit']);

        $this->modifyDepositTypeEnum([
            'cash_deposit', 'bank_transfer', 'nafa_deposit', 'wave_deposit', 'cheque_deposit', 'daily_sales_deposit',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only proceed if deposit_type column exists
        if (!Schema::hasColumn('deposits', 'deposit_type')) {
            return;
        }

        $this->modifyDepositTypeEnum([
            'cash', 'cash_deposit', 'bank_transfer', 'nafa', 'nafa_deposit',
            'wave', 'wave_deposit', 'cheque', 'cheque_deposit', 'sales', 'daily_sales_deposit',
        ]);

        // Revert data to old enum values
        DB::table('deposits')->where('deposit_type', 'cash_deposit')->update(['deposit_type' => 'cash']);
        DB::table('deposits')->where('deposit_type', 'nafa_deposit')->update(['deposit_type' => 'nafa']);
        DB::table('deposits')->where('deposit_type', 'wave_deposit')->update(['deposit_type' => 'wave']);
        DB::table('deposits')->where('deposit_type', 'cheque_deposit')->update(['deposit_type' => 'cheque']);
        DB::table('deposits')->where('deposit_type', 'daily_sales_deposit')->update(['deposit_type' => 'sales']);

        $this->modifyDepositTypeEnum(['cash', 'bank_transfer', 'nafa', 'wave', 'cheque', 'sales']);
    }

    /**
     * @param  list<string>  $values
     */
    private function modifyDepositTypeEnum(array $values): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $enum = implode("', '", $values);
        DB::statement("ALTER TABLE deposits MODIFY COLUMN deposit_type ENUM('{$enum}')");
    }
};
