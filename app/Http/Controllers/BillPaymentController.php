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
     * Remove the specified resource from storage.
     */
    public function destroy(BillPayment $billPayment): RedirectResponse
    {
        $billId = $billPayment->bill_id;
        $transactionId = $billPayment->transaction_id;

        DB::beginTransaction();
        try {
            // Delete the bill payment
            $billPayment->delete();

            // Delete the associated transaction
            $transaction = Transaction::find($transactionId);
            if ($transaction) {
                $transaction->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while deleting the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('bills.show', $billId)->with('success', 'Payment deleted successfully.');
    }
}