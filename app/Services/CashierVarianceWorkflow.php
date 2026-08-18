<?php

namespace App\Services;

use App\Models\CashierReconciliation;
use App\Models\CashierVarianceLedgerEntry;
use App\Models\CashierVarianceOutbox;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CashierVarianceWorkflow
{
    public function approve(
        CashierReconciliation $reconciliation,
        User $actor,
        ?string $comment = null
    ): void
    {
        DB::transaction(function () use ($reconciliation, $actor, $comment): void {
            $locked = CashierReconciliation::query()->lockForUpdate()->findOrFail($reconciliation->id);

            if ($locked->status !== 'submitted') {
                throw new RuntimeException('Only submitted reconciliations can be approved.');
            }

            if ($locked->created_by === $actor->id && ! $actor->isAdminOrManager()) {
                throw new RuntimeException('You cannot approve a reconciliation you created.');
            }

            $locked->update([
                'status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            CashierVarianceLedgerEntry::create([
                'uuid' => (string) Str::uuid(),
                'cashier_reconciliation_id' => $locked->id,
                'hr_cashier_id' => $locked->hr_cashier_id,
                'branch_id' => $locked->branch_id,
                'shift_id' => $locked->shift_id,
                'cashier_employee_id' => $locked->cashier_employee_id,
                'cashier_name' => $locked->cashier_name,
                'branch_name' => $locked->branch_name,
                'shift_name' => $locked->shift_name,
                'entry_date' => $locked->business_date,
                'entry_type' => $locked->classification,
                'variance_amount' => $locked->calculated_variance,
                'reason' => $locked->comments,
                'created_by' => $actor->id,
            ]);

            $locked->events()->create([
                'event_type' => 'approved',
                'actor_id' => $actor->id,
                'reason' => $comment,
                'new_values' => [
                    'status' => 'approved',
                    'variance' => $locked->calculated_variance,
                ],
            ]);

            if ($locked->classification === 'shortage') {
                CashierVarianceOutbox::create([
                    'event_uuid' => (string) Str::uuid(),
                    'cashier_reconciliation_id' => $locked->id,
                    'event_type' => 'shortage.created',
                    'payload' => [
                        'source_reference' => $locked->uuid,
                        'employee_number' => $locked->cashier_employee_id,
                        'amount' => $locked->calculated_variance,
                        'date' => $locked->business_date->toDateString(),
                        'reason' => $locked->comments ?: 'Approved cashier shortage',
                        'finance_context' => [
                            'sale_id' => $locked->sale_id,
                            'branch' => $locked->branch_name,
                            'shift' => $locked->shift_name,
                            'reference' => $locked->pos_reference,
                            'approved_at' => now()->toIso8601String(),
                        ],
                    ],
                    'status' => 'pending',
                    'available_at' => now(),
                ]);

            }
        });
    }

    public function reject(
        CashierReconciliation $reconciliation,
        User $actor,
        string $reason
    ): void {
        DB::transaction(function () use ($reconciliation, $actor, $reason): void {
            $locked = CashierReconciliation::query()->lockForUpdate()->findOrFail($reconciliation->id);

            if ($locked->status !== 'submitted') {
                throw new RuntimeException('Only submitted reconciliations can be rejected.');
            }

            $locked->update([
                'status' => 'rejected',
                'rejected_by' => $actor->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $locked->events()->create([
                'event_type' => 'rejected',
                'actor_id' => $actor->id,
                'reason' => $reason,
                'new_values' => ['status' => 'rejected'],
            ]);
        });
    }

    public function reverse(
        CashierReconciliation $reconciliation,
        User $actor,
        string $reason
    ): void {
        DB::transaction(function () use ($reconciliation, $actor, $reason): void {
            $locked = CashierReconciliation::query()->lockForUpdate()->findOrFail($reconciliation->id);

            if ($locked->status !== 'approved') {
                throw new RuntimeException('Only approved reconciliations can be reversed.');
            }

            $original = $locked->ledgerEntries()->whereNull('reverses_entry_id')->firstOrFail();
            $calculator = app(CashierVarianceCalculator::class);

            CashierVarianceLedgerEntry::create([
                'uuid' => (string) Str::uuid(),
                'cashier_reconciliation_id' => $locked->id,
                'hr_cashier_id' => $locked->hr_cashier_id,
                'branch_id' => $locked->branch_id,
                'shift_id' => $locked->shift_id,
                'reverses_entry_id' => $original->id,
                'cashier_employee_id' => $locked->cashier_employee_id,
                'cashier_name' => $locked->cashier_name,
                'branch_name' => $locked->branch_name,
                'shift_name' => $locked->shift_name,
                'entry_date' => today(),
                'entry_type' => "{$locked->classification}_reversal",
                'variance_amount' => $calculator->negate($original->variance_amount),
                'reason' => $reason,
                'created_by' => $actor->id,
            ]);

            $locked->update([
                'status' => 'reversed',
                'reversed_at' => now(),
            ]);

            $locked->events()->create([
                'event_type' => 'reversed',
                'actor_id' => $actor->id,
                'reason' => $reason,
                'old_values' => [
                    'status' => 'approved',
                    'variance' => $original->variance_amount,
                ],
                'new_values' => [
                    'status' => 'reversed',
                    'reversal_variance' => $calculator->negate($original->variance_amount),
                ],
            ]);

            if ($locked->classification === 'shortage') {
                CashierVarianceOutbox::firstOrCreate(
                    [
                        'cashier_reconciliation_id' => $locked->id,
                        'event_type' => 'shortage.reversed',
                    ],
                    [
                        'event_uuid' => (string) Str::uuid(),
                        'payload' => [
                            'source_reference' => $locked->uuid,
                            'reason' => $reason,
                        ],
                        'status' => 'pending',
                        'available_at' => now(),
                    ]
                );

            }
        });
    }
}
