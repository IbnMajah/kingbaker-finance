<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class BillPaymentController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $bill = Bill::with('vendor')->findOrFail($request->bill_id);
        $bankAccounts = BankAccount::where('active', true)->orderBy('name')->get();

        return Inertia::render('BillPayments/Create', [
            'bill' => $bill,
            'bankAccounts' => $bankAccounts,
            'paymentModes' => ['cash', 'cheque', 'bank_transfer', 'nafa', 'wave', 'other'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'payment_mode' => 'required|in:cash,cheque,bank_transfer,nafa,wave,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        $bill = Bill::findOrFail($validated['bill_id']);

        // Check if payment amount is valid
        $remainingAmount = $bill->amount - $bill->amount_paid;
        if ($validated['amount'] > $remainingAmount) {
            return Redirect::back()->withErrors([
                'amount' => 'Payment amount cannot exceed the remaining balance of ' . number_format($remainingAmount, 2),
            ]);
        }

        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
        }

        DB::beginTransaction();
        try {
            // Create the transaction
            $transaction = Transaction::create([
                'bank_account_id' => $validated['bank_account_id'],
                'transaction_date' => $validated['transaction_date'],
                'type' => 'debit', // Payment is always a debit
                'payment_mode' => $validated['payment_mode'],
                'reference_number' => $validated['reference_number'],
                'payee' => $bill->vendor->name,
                'amount' => $validated['amount'],
                'description' => 'Payment for bill #' . $bill->bill_number . ' - ' . $bill->description,
                'category' => 'vendor_payment',
                'image_path' => $imagePath,
                'created_by' => Auth::id(),
            ]);

            // Create the bill payment
            BillPayment::create([
                'bill_id' => $validated['bill_id'],
                'transaction_id' => $transaction->id,
                'amount' => $validated['amount'],
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while processing the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('bills.show', $bill->id)->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BillPayment $billPayment): Response
    {
        $billPayment->load(['bill', 'bill.vendor', 'transaction', 'transaction.bankAccount', 'creator']);

        return Inertia::render('BillPayments/Show', [
            'payment' => $billPayment,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BillPayment $billPayment): RedirectResponse
    {
        $rules = [
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mobile_money',
        ];

        // Bank account is required only if payment method is not cash
        if ($request->input('payment_method') && $request->input('payment_method') !== 'cash') {
            $rules['bank_account_id'] = 'required|exists:bank_accounts,id';
        } else {
            // If changing to cash, bank_account_id can be null for bill payment
            // but we'll keep the existing one for the transaction
            $rules['bank_account_id'] = 'nullable|exists:bank_accounts,id';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $oldBankAccountId = $billPayment->bank_account_id;
            $oldPaymentMethod = $billPayment->payment_method;

            // If changing to cash and no bank account provided, keep the old one for transaction
            // but set bill payment's bank_account_id to null
            if ($validated['payment_method'] === 'cash' && !isset($validated['bank_account_id'])) {
                $validated['bank_account_id'] = null;
            }

            $billPayment->update($validated);

            // Update the linked transaction if it exists
            if ($billPayment->transaction_id) {
                $transaction = $billPayment->transaction;
                if ($transaction) {
                    $transactionData = [
                        'payment_mode' => $validated['payment_method'],
                    ];

                    // Update bank account based on payment method
                    if ($validated['payment_method'] !== 'cash' && isset($validated['bank_account_id'])) {
                        // Non-cash: update to the new bank account
                        $transactionData['bank_account_id'] = $validated['bank_account_id'];
                    }
                    // For cash payments, don't change the transaction's bank_account_id
                    // (transactions require bank_account_id, so we keep the existing one)

                    $transaction->update($transactionData);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while updating the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::back()->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillPayment $billPayment): RedirectResponse
    {
        // Load relationships
        $billPayment->load(['bill.vendor', 'transaction']);
        $bill = $billPayment->bill;
        $transaction = $billPayment->transaction;
        $billId = $billPayment->bill_id;

        DB::beginTransaction();
        try {
            // Create a reverse transaction (credit) to offset the original debit
            // The original transaction remains untouched - both will show on account sheets
            if ($transaction && $transaction->bank_account_id) {
                Transaction::create([
                    'bank_account_id' => $transaction->bank_account_id,
                    'transaction_date' => now()->toDateString(),
                    'type' => 'credit', // Reverse transaction is a credit
                    'payment_mode' => $transaction->payment_mode,
                    'reference_number' => 'REV-' . ($transaction->reference_number ?? 'PAY-' . $billPayment->id),
                    'payee' => $bill->vendor->name ?? 'Vendor',
                    'amount' => $transaction->amount,
                    'description' => 'Reversal: Payment for bill #' . ($bill->bill_number ?? $bill->id) . ' - ' . ($bill->description ?? ''),
                    'category' => 'vendor_payment_reversal',
                    'created_by' => Auth::id(),
                ]);

                // Reverse transaction credits the account to offset the original debit
            }

            // Delete the bill payment (this will trigger the model's deleted event)
            // This will update the bill's amount_paid and status
            // We do NOT delete the original transaction - it stays for audit trail
            $billPayment->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while deleting the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::back()->with('success', 'Payment deleted successfully. A reverse transaction has been created to credit the account.');
    }
}
