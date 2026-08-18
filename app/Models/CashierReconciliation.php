<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class CashierReconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'sale_id',
        'hr_cashier_id',
        'branch_id',
        'shift_id',
        'cashier_employee_id',
        'cashier_name',
        'branch_name',
        'shift_name',
        'till_number',
        'pos_reference',
        'business_date',
        'closed_at',
        'expected_total',
        'actual_total',
        'calculated_variance',
        'classification',
        'receipt_image_path',
        'comments',
        'status',
        'created_by',
        'verified_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'reversed_at',
    ];

    protected $casts = [
        'business_date' => 'date',
        'closed_at' => 'datetime',
        'expected_total' => 'decimal:2',
        'actual_total' => 'decimal:2',
        'calculated_variance' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $reconciliation): void {
            $originalStatus = $reconciliation->getOriginal('status');

            if ($originalStatus === 'reversed') {
                throw new LogicException('Reversed reconciliations are immutable.');
            }

            if ($originalStatus === 'approved') {
                $allowed = ['status', 'reversed_at', 'updated_at'];
                $changed = array_keys($reconciliation->getDirty());

                if ($reconciliation->status !== 'reversed' || array_diff($changed, $allowed)) {
                    throw new LogicException('Approved reconciliations are immutable; create a reversal.');
                }
            }
        });

        static::deleting(function (self $reconciliation): void {
            if (in_array($reconciliation->status, ['approved', 'reversed'], true)) {
                throw new LogicException('Posted reconciliations cannot be deleted.');
            }
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(HrCashier::class, 'hr_cashier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(CashierVarianceLedgerEntry::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CashierVarianceEvent::class);
    }

    public function outboxEvents(): HasMany
    {
        return $this->hasMany(CashierVarianceOutbox::class);
    }

    public function isMutable(): bool
    {
        return in_array($this->status, ['draft', 'rejected'], true);
    }
}
