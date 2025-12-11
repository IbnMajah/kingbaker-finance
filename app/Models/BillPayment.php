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

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    protected static function booted()
    {
        static::created(function ($payment) {
            $bill = $payment->bill;

            // Only create transaction if one doesn't already exist
            if (!$payment->transaction_id) {
                $transaction = Transaction::create([
                    'bank_account_id' => $payment->bank_account_id,
                    'transaction_date' => $payment->payment_date,
                    'type' => 'debit',
                    'payment_mode' => $payment->payment_method,
                    'reference_number' => $payment->reference_number,
                    'payee' => $bill->vendor->name,
                    'amount' => $payment->amount,
                    'description' => 'Payment for bill #' . $bill->bill_number . ' - ' . $bill->description,
                    'category' => 'vendor_payment',
                    'created_by' => $payment->created_by,
                ]);

                // Link the transaction to the bill payment
                $payment->update(['transaction_id' => $transaction->id]);
            }

            // Update bill amount_paid and status
            $bill->amount_paid = $bill->payments()->sum('amount');
            $bill->updateStatus();

            // Update bank account balance
            if ($payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
        });

        static::updated(function ($payment) {
            $bill = $payment->bill;
            $bill->amount_paid = $bill->payments()->sum('amount');

            // Check if bank_account_id was changed
            $bankAccountChanged = $payment->isDirty('bank_account_id');
            $oldBankAccountId = $bankAccountChanged ? $payment->getOriginal('bank_account_id') : null;
            $newBankAccountId = $payment->bank_account_id;

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
                    ];

                    // Update payment_mode if payment_method changed
                    if ($paymentMethodChanged) {
                        $updateData['payment_mode'] = $payment->payment_method;
                    }

                    // Update bank_account_id if it changed
                    if ($bankAccountChanged) {
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
            // If payment method is cash, the transaction still has the old bank account
            // so we update that account's balance
            if ($payment->payment_method === 'cash' && $oldBankAccountId) {
                $oldBankAccount = BankAccount::find($oldBankAccountId);
                if ($oldBankAccount) {
                    $oldBankAccount->updateBalance();
                }
            } elseif ($payment->payment_method !== 'cash' && $payment->bankAccount) {
                // For non-cash payments, update the new bank account
                $payment->bankAccount->updateBalance();
            }
            $bill->updateStatus();
        });

        static::deleted(function ($payment) {
            $bill = $payment->bill;
            $bill->amount_paid = $bill->payments()->sum('amount');

            // Do NOT delete the linked transaction - it should remain for audit trail
            // A reverse transaction will be created separately to offset it
            // The original transaction stays visible on account sheets

            // Update bank account balance (in case amount_paid changed affected other payments)
            if ($payment->bankAccount) {
                $payment->bankAccount->updateBalance();
            }
            $bill->updateStatus();
        });
    }
}
