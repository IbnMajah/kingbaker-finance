<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'type',
        'payment_mode',
        'reference_number',
        'payee',
        'amount',
        'description',
        'branch_id',
        'shift_id',
        'category',
        'image_path',
        'created_by',
        'is_reconciled',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
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

    public function billPayments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }

    protected static function booted()
    {
        static::created(function ($transaction) {
            $transaction->bankAccount->updateBalance();
        });

        static::updated(function ($transaction) {
            $transaction->bankAccount->updateBalance();
        });

        static::deleted(function ($transaction) {
            $transaction->bankAccount->updateBalance();
        });
    }
}