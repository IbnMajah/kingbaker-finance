<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'invoice_date',
        'due_date',
        'amount',
        'amount_paid',
        'status',
        'invoice_type',
        'customer_type',
        'description',
        'notes',
        'bank_account_id',
        'branch_id',
        'billing_period',
        'is_recurring',
        'recurring_frequency',
        'next_invoice_date',
        'attachment_path',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'next_invoice_date' => 'date',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
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

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function updateStatus(): void
    {
        if ($this->amount_paid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date < now() && $this->status !== 'paid') {
            $this->status = 'overdue';
        }

        $this->save();
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->amount_paid;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedAmountPaidAttribute(): string
    {
        return number_format($this->amount_paid, 2);
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format($this->remaining_amount, 2);
    }

    public function addPayment(float $amount, array $paymentData = []): void
    {
        $this->amount_paid += $amount;
        $this->updateStatus();

        // Create payment record if InvoicePayment model exists
        if (class_exists(InvoicePayment::class)) {
            $this->payments()->create(array_merge($paymentData, [
                'amount' => $amount,
                'payment_date' => now(),
            ]));
        }
    }

    /**
     * Calculate total amount from items
     */
    public function calculateTotalFromItems(): void
    {
        $this->amount = $this->items->sum('total');
        $this->save();
    }

    /**
     * Update total when items change
     */
    public function updateTotalAmount(): void
    {
        $this->amount = $this->items()->sum('total');
        $this->save();
    }
}