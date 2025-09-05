<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChequePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'payee',
        'amount',
        'payment_date',
        'payment_category',
        'payment_mode',
        'bank_account_id',
        'branch_id',
        'vendor_id',
        'bill_id',
        'is_recurring',
        'recurring_frequency',
        'cheque_number',
        'reference_number',
        'description',
        'notes',
        'status',
        'receipt_image_path',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'issued' => 'bg-blue-100 text-blue-800',
            'cleared' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->payment_category) {
            'vendor_payment' => 'Vendor Payment',
            'bill' => 'Bill',
            'staff_advance' => 'Staff Advance',
            'loan_payment' => 'Loan Payment',
            'institutional_payment' => 'Institutional Payment',
            'other_payment' => 'Other Payment',
            default => 'Unknown',
        };
    }

    public function getPaymentModeLabelAttribute(): string
    {
        return match ($this->payment_mode) {
            'cheque' => 'Cheque',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'nafa' => 'NAFA',
            'wave' => 'Wave',
            'other' => 'Other',
            default => 'Unknown',
        };
    }

    protected static function booted()
    {
        static::created(function ($payment) {
            // Update bank account balance when payment is created
            if ($payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
        });

        static::updated(function ($payment) {
            // Update bank account balance when payment is updated
            if ($payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
        });

        static::deleted(function ($payment) {
            // Update bank account balance when payment is deleted
            if ($payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
        });
    }
}
