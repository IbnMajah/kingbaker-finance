<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrCashier extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'status',
        'hr_branch_code',
        'hr_branch_name',
        'branch_id',
        'default_shift_id',
        'last_seen_at',
        'last_synced_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function defaultShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'default_shift_id')->withTrashed();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'hr_cashier_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(CashierReconciliation::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(CashierVarianceLedgerEntry::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
