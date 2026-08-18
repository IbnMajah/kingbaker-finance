<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class CashierVarianceEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'cashier_reconciliation_id',
        'event_type',
        'actor_id',
        'reason',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Cashier variance events are immutable.'));
        static::deleting(fn () => throw new LogicException('Cashier variance events are immutable.'));
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(CashierReconciliation::class, 'cashier_reconciliation_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
