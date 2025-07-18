<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_id',
        'bank_account_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::created(function ($payment) {
            $bill = $payment->bill;
            $bill->amount_paid = $bill->payments()->sum('amount');
            $bill->updateStatus();
        });

        static::updated(function ($payment) {
            $bill = $payment->bill;
            $bill->amount_paid = $bill->payments()->sum('amount');
            $bill->updateStatus();
        });

        static::deleted(function ($payment) {
            $bill = $payment->bill;
            $bill->amount_paid = $bill->payments()->sum('amount');
            $bill->updateStatus();
        });
    }
}