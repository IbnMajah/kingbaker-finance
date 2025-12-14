<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reference_id',
        'claim_date',
        'title',
        'total',
        'payee',
        'status',
        'expense_type',
        'notes',
        'transaction_id',
        'branch_id',
        'bank_account_id',
        'approved_by',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ExpenseClaimPayment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(ExpenseClaimPayment::class)->latestOfMany();
    }

    public function updateTotalAmount(): void
    {
        $this->total = $this->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });
        $this->save();
    }

    /**
     * Boot model events
     */
    protected static function booted()
    {
        // No longer create transactions automatically - transactions are created when payment is made
    }

    /**
     * Map expense type to payment mode for transactions
     */
    public function mapExpenseTypeToPaymentMode(string $expenseType): string
    {
        return match ($expenseType) {
            'petty_cash' => 'cash',
            'cash_on_hand' => 'cash',
            'other' => 'other',
            default => 'other',
        };
    }
}
