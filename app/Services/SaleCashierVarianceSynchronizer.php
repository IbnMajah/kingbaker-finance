<?php

namespace App\Services;

use App\Models\CashierReconciliation;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Str;

class SaleCashierVarianceSynchronizer
{
    public function __construct(private readonly CashierVarianceCalculator $calculator)
    {
    }

    public function sync(Sale $sale, User $actor): ?CashierReconciliation
    {
        $sale->loadMissing(['branch', 'shift', 'hrCashier', 'reconciliation']);
        $existing = $sale->reconciliation;

        if (! $sale->hr_cashier_id || $sale->actual_total === null || ! $sale->hrCashier || ! $sale->branch || ! $sale->shift) {
            if ($existing?->isMutable()) {
                $existing->delete();
                $sale->unsetRelation('reconciliation');

                return null;
            }

            return $existing;
        }
        if ($existing && ! $existing->isMutable()) {
            return $existing;
        }

        $expected = $this->calculator->format((string) $sale->total_amount);
        $actual = $this->calculator->format((string) $sale->actual_total);
        $calculation = $this->calculator->calculate($expected, $actual);
        $reference = 'SALE-'.$sale->id;
        $cashier = $sale->hrCashier;

        $attributes = [
            'hr_cashier_id' => $cashier->id,
            'branch_id' => $sale->branch_id,
            'shift_id' => $sale->shift_id,
            'cashier_employee_id' => $cashier->employee_id,
            'cashier_name' => $cashier->name,
            'branch_name' => $sale->branch->name,
            'shift_name' => $sale->shift->name,
            'till_number' => $reference,
            'pos_reference' => $reference,
            'business_date' => $sale->sales_date,
            'closed_at' => $existing?->closed_at ?? now(),
            'expected_total' => $expected,
            'actual_total' => $actual,
            'calculated_variance' => $calculation['amount'],
            'classification' => $calculation['classification'],
        ];

        if ($existing) {
            $existing->update($attributes);
            $existing->events()->create([
                'event_type' => 'updated',
                'actor_id' => $actor->id,
                'new_values' => [
                    'expected_total' => $expected,
                    'actual_total' => $actual,
                    'variance' => $calculation['amount'],
                    'classification' => $calculation['classification'],
                ],
            ]);

            return $existing;
        }

        $record = CashierReconciliation::create([
            'uuid' => (string) Str::uuid(),
            'sale_id' => $sale->id,
            ...$attributes,
            'status' => 'draft',
            'created_by' => $actor->id,
        ]);

        $record->events()->create([
            'event_type' => 'created',
            'actor_id' => $actor->id,
            'new_values' => [
                'sale_id' => $sale->id,
                'status' => 'draft',
                'variance' => $record->calculated_variance,
                'classification' => $record->classification,
            ],
        ]);

        return $record;
    }
}
