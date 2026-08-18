<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierVarianceOutbox extends Model
{
    protected $table = 'cashier_variance_outbox';

    protected $fillable = [
        'event_uuid',
        'cashier_reconciliation_id',
        'event_type',
        'payload',
        'status',
        'attempts',
        'available_at',
        'processed_at',
        'last_error',
        'external_reference',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'available_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(CashierReconciliation::class, 'cashier_reconciliation_id');
    }
}
