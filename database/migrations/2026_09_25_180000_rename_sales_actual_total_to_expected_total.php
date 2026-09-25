<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('actual_total', 'expected_total');
        });

        foreach (DB::table('cashier_reconciliations')->get(['id', 'expected_total', 'actual_total']) as $row) {
            $expected = $row->actual_total;
            $actual = $row->expected_total;
            $variance = round(((float) $expected) - ((float) $actual), 2);

            DB::table('cashier_reconciliations')->where('id', $row->id)->update([
                'expected_total' => $expected,
                'actual_total' => $actual,
                'calculated_variance' => number_format($variance, 2, '.', ''),
                'classification' => match (true) {
                    $variance > 0 => 'shortage',
                    $variance < 0 => 'surplus',
                    default => 'balanced',
                },
            ]);
        }

        foreach (DB::table('cashier_variance_ledger_entries')->get(['id', 'entry_type', 'variance_amount']) as $entry) {
            DB::table('cashier_variance_ledger_entries')->where('id', $entry->id)->update([
                'entry_type' => match ($entry->entry_type) {
                    'shortage' => 'surplus',
                    'surplus' => 'shortage',
                    'shortage_reversal' => 'surplus_reversal',
                    'surplus_reversal' => 'shortage_reversal',
                    default => $entry->entry_type,
                },
                'variance_amount' => -1 * (float) $entry->variance_amount,
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('cashier_variance_ledger_entries')->get(['id', 'entry_type', 'variance_amount']) as $entry) {
            DB::table('cashier_variance_ledger_entries')->where('id', $entry->id)->update([
                'entry_type' => match ($entry->entry_type) {
                    'shortage' => 'surplus',
                    'surplus' => 'shortage',
                    'shortage_reversal' => 'surplus_reversal',
                    'surplus_reversal' => 'shortage_reversal',
                    default => $entry->entry_type,
                },
                'variance_amount' => -1 * (float) $entry->variance_amount,
            ]);
        }

        foreach (DB::table('cashier_reconciliations')->get(['id', 'expected_total', 'actual_total']) as $row) {
            $expected = $row->actual_total;
            $actual = $row->expected_total;
            $variance = round(((float) $expected) - ((float) $actual), 2);

            DB::table('cashier_reconciliations')->where('id', $row->id)->update([
                'expected_total' => $expected,
                'actual_total' => $actual,
                'calculated_variance' => number_format($variance, 2, '.', ''),
                'classification' => match (true) {
                    $variance > 0 => 'shortage',
                    $variance < 0 => 'surplus',
                    default => 'balanced',
                },
            ]);
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('expected_total', 'actual_total');
        });
    }
};
