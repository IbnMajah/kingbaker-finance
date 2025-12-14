<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ExpenseClaimPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_claim_id',
        'bank_account_id',
        'transaction_id',
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

    public function expenseClaim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::created(function ($payment) {
            $expenseClaim = $payment->expenseClaim;

            // Only create transaction if one doesn't already exist
            if (!$payment->transaction_id) {
                $transaction = Transaction::create([
                    'bank_account_id' => $payment->bank_account_id,
                    'transaction_date' => $payment->payment_date,
                    'type' => 'debit',
                    'payment_mode' => $payment->payment_method,
                    'reference_number' => $payment->reference_number,
                    'payee' => $expenseClaim->payee,
                    'amount' => $payment->amount,
                    'description' => "Payment for Expense Claim: {$expenseClaim->title}",
                    'branch_id' => $expenseClaim->branch_id,
                    'category' => 'expense_claim',
                    'created_by' => $payment->created_by,
                ]);

                // Link the transaction to the payment
                DB::table('expense_claim_payments')
                    ->where('id', $payment->id)
                    ->update(['transaction_id' => $transaction->id]);
            }

            // Update expense claim status to paid
            $expenseClaim->update(['status' => 'paid']);

            // Update bank account balance
            if ($payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
        });

        static::updated(function ($payment) {
            $expenseClaim = $payment->expenseClaim;

            // Check if bank_account_id was changed
            $bankAccountChanged = $payment->isDirty('bank_account_id');
            $oldBankAccountId = $bankAccountChanged ? $payment->getOriginal('bank_account_id') : null;

            // Check if payment_method was changed
            $paymentMethodChanged = $payment->isDirty('payment_method');

            // Update the linked transaction if it exists
            if ($payment->transaction_id) {
                $transaction = $payment->transaction;
                if ($transaction) {
                    $updateData = [
                        'amount' => $payment->amount,
                        'transaction_date' => $payment->payment_date,
                        'reference_number' => $payment->reference_number,
                        'payee' => $expenseClaim->payee,
                        'description' => "Payment for Expense Claim: {$expenseClaim->title}",
                    ];

                    // Update payment_mode if payment_method changed
                    if ($paymentMethodChanged) {
                        $updateData['payment_mode'] = $payment->payment_method;
                    }

                    // Update bank_account_id if it changed
                    if ($bankAccountChanged && $payment->bank_account_id) {
                        $updateData['bank_account_id'] = $payment->bank_account_id;
                    }

                    $transaction->update($updateData);
                }
            }

            // Update old bank account balance if account changed
            if ($bankAccountChanged && $oldBankAccountId) {
                $oldBankAccount = BankAccount::find($oldBankAccountId);
                if ($oldBankAccount) {
                    $oldBankAccount->updateBalance();
                }
            }

            // Update new bank account balance
            if ($payment->payment_method === 'cash' && $oldBankAccountId) {
                $oldBankAccount = BankAccount::find($oldBankAccountId);
                if ($oldBankAccount) {
                    $oldBankAccount->updateBalance();
                }
            } elseif ($payment->payment_method !== 'cash' && $payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
        });

        static::deleted(function ($payment) {
            $expenseClaim = $payment->expenseClaim;

            // Update expense claim status back to approved if payment is deleted
            if ($expenseClaim && !$expenseClaim->payments()->exists()) {
                $expenseClaim->update(['status' => 'approved']);
            }

            // Update bank account balance
            if ($payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
        });
    }
}
