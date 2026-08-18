<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class CashierVarianceLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'cashier_reconciliation_id',
        'hr_cashier_id',
        'branch_id',
        'shift_id',
        'reverses_entry_id',
        'cashier_employee_id',
        'cashier_name',
        'branch_name',
        'shift_name',
        'entry_date',
        'entry_type',
        'variance_amount',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'variance_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Cashier ledger entries are immutable.'));
        static::deleting(fn () => throw new LogicException('Cashier ledger entries are immutable.'));
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(CashierReconciliation::class, 'cashier_reconciliation_id');
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

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
