<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        // Create transaction when expense claim is created
        static::created(function ($expenseClaim) {
            if ($expenseClaim->bankAccount && $expenseClaim->status === 'active') {
                $expenseClaim->bankAccount->transactions()->create([
                    'transaction_date' => $expenseClaim->claim_date,
                    'type' => 'debit',
                    'payment_mode' => $expenseClaim->mapExpenseTypeToPaymentMode($expenseClaim->expense_type),
                    'reference_number' => $expenseClaim->reference_id,
                    'payee' => $expenseClaim->payee,
                    'amount' => $expenseClaim->total,
                    'description' => "Expense Claim: {$expenseClaim->title}",
                    'branch_id' => $expenseClaim->branch_id,
                    'category' => 'expense_claim',
                    'created_by' => $expenseClaim->user_id,
                    'is_reconciled' => false,
                ]);
            }
        });

        // Update transaction when expense claim is updated
        static::updated(function ($expenseClaim) {
            if ($expenseClaim->bankAccount) {
                DB::transaction(function () use ($expenseClaim) {
                    $transaction = $expenseClaim->bankAccount->transactions()
                        ->where('category', 'expense_claim')
                        ->where('reference_number', $expenseClaim->reference_id)
                        ->lockForUpdate()
                        ->first();

                    if ($transaction) {
                        $transaction->update([
                            'transaction_date' => $expenseClaim->claim_date,
                            'payment_mode'     => $expenseClaim->mapExpenseTypeToPaymentMode($expenseClaim->expense_type),
                            'payee'            => $expenseClaim->payee,
                            'amount'           => $expenseClaim->total,
                            'description'      => "Expense Claim: {$expenseClaim->title}",
                            'branch_id'        => $expenseClaim->branch_id,
                        ]);
                    }
                });
            }
        });

        // Delete transaction when expense claim is deleted
        static::deleted(function ($expenseClaim) {
            if ($expenseClaim->bankAccount) {
                $transaction = $expenseClaim->bankAccount->transactions()
                    ->where('category', 'expense_claim')
                    ->where('reference_number', $expenseClaim->reference_id)
                    ->first();

                if ($transaction) {
                    $transaction->delete();
                }
            }
        });
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
