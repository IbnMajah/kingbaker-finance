<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ExpenseClaim;
use App\Models\ExpenseClaimPayment;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseClaimPaymentController extends Controller
{
    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $expenseClaim = ExpenseClaim::findOrFail($request->expense_claim_id);

        // Check if user can create payment (admin or finance_manager)
        $user = Auth::user();
        if (!($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'))) {
            abort(403, 'You do not have permission to create payments for expense claims.');
        }

        // Check if expense claim is approved
        if ($expenseClaim->status !== 'approved') {
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('error', 'Only approved expense claims can have payments created.');
        }

        // Check if payment already exists
        if ($expenseClaim->payment) {
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('error', 'A payment already exists for this expense claim.');
        }

        $bankAccounts = BankAccount::where('active', true)->orderBy('name')->get();

        return Inertia::render('ExpenseClaimPayments/Create', [
            'expenseClaim' => $expenseClaim,
            'bankAccounts' => $bankAccounts->map(fn($account) => [
                'value' => $account->id,
                'label' => $account->name,
            ]),
            'paymentMethods' => ['cash', 'cheque', 'bank_transfer', 'nafa', 'wave', 'other'],
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_claim_id' => 'required|exists:expense_claims,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,nafa,wave,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $expenseClaim = ExpenseClaim::findOrFail($validated['expense_claim_id']);

        // Check if user can create payment (admin or finance_manager)
        $user = Auth::user();
        if (!($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'))) {
            abort(403, 'You do not have permission to create payments for expense claims.');
        }

        // Check if expense claim is approved
        if ($expenseClaim->status !== 'approved') {
            return Redirect::back()->with('error', 'Only approved expense claims can have payments created.');
        }

        // Check if payment already exists
        if ($expenseClaim->payment) {
            return Redirect::back()->with('error', 'A payment already exists for this expense claim.');
        }

        // Check if payment amount matches expense claim total
        if (abs($validated['amount'] - $expenseClaim->total) > 0.01) {
            return Redirect::back()->withErrors([
                'amount' => 'Payment amount must match the expense claim total of ' . number_format($expenseClaim->total, 2),
            ]);
        }

        DB::beginTransaction();
        try {
            // Create the payment (transaction will be created automatically by model event)
            ExpenseClaimPayment::create([
                'expense_claim_id' => $validated['expense_claim_id'],
                'bank_account_id' => $validated['bank_account_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while creating the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('expense-claims.show', $expenseClaim->id)
            ->with('success', 'Payment created successfully.');
    }

    /**
     * Show the form for editing a payment.
     */
    public function edit(ExpenseClaimPayment $expenseClaimPayment): Response
    {
        $expenseClaimPayment->load(['expenseClaim', 'bankAccount', 'transaction']);

        // Check if user can edit payment (admin or finance_manager)
        $user = Auth::user();
        if (!($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'))) {
            abort(403, 'You do not have permission to edit payments for expense claims.');
        }

        $bankAccounts = BankAccount::where('active', true)->orderBy('name')->get();

        return Inertia::render('ExpenseClaimPayments/Edit', [
            'payment' => [
                'id' => $expenseClaimPayment->id,
                'expense_claim_id' => $expenseClaimPayment->expense_claim_id,
                'amount' => $expenseClaimPayment->amount,
                'payment_date' => $expenseClaimPayment->payment_date->format('Y-m-d'),
                'payment_method' => $expenseClaimPayment->payment_method,
                'reference_number' => $expenseClaimPayment->reference_number,
                'notes' => $expenseClaimPayment->notes,
                'bank_account_id' => $expenseClaimPayment->bank_account_id,
            ],
            'expenseClaim' => $expenseClaimPayment->expenseClaim,
            'bankAccounts' => $bankAccounts->map(fn($account) => [
                'value' => $account->id,
                'label' => $account->name,
            ]),
            'paymentMethods' => ['cash', 'cheque', 'bank_transfer', 'nafa', 'wave', 'other'],
        ]);
    }

    /**
     * Update the payment.
     */
    public function update(Request $request, ExpenseClaimPayment $expenseClaimPayment): RedirectResponse
    {
        // Check if user can edit payment (admin or finance_manager)
        $user = Auth::user();
        if (!($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'))) {
            abort(403, 'You do not have permission to edit payments for expense claims.');
        }

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,nafa,wave,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $expenseClaim = $expenseClaimPayment->expenseClaim;

        // Check if payment amount matches expense claim total
        if (abs($validated['amount'] - $expenseClaim->total) > 0.01) {
            return Redirect::back()->withErrors([
                'amount' => 'Payment amount must match the expense claim total of ' . number_format($expenseClaim->total, 2),
            ]);
        }

        DB::beginTransaction();
        try {
            $expenseClaimPayment->update($validated);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while updating the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('expense-claims.show', $expenseClaim->id)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the payment.
     */
    public function destroy(ExpenseClaimPayment $expenseClaimPayment): RedirectResponse
    {
        // Check if user can delete payment (admin or finance_manager)
        $user = Auth::user();
        if (!($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'))) {
            abort(403, 'You do not have permission to delete payments for expense claims.');
        }

        $expenseClaim = $expenseClaimPayment->expenseClaim;
        $expenseClaimId = $expenseClaim->id;

        $reversalCreated = false;
        DB::beginTransaction();
        try {
            // Create a reverse transaction (credit) to offset the original debit
            if ($expenseClaimPayment->transaction && $expenseClaimPayment->transaction->bank_account_id) {
                Transaction::create([
                    'bank_account_id' => $expenseClaimPayment->transaction->bank_account_id,
                    'transaction_date' => now()->toDateString(),
                    'type' => 'credit',
                    'payment_mode' => $expenseClaimPayment->transaction->payment_mode,
                    'reference_number' => 'REV-' . ($expenseClaimPayment->transaction->reference_number ?? 'PAY-' . $expenseClaimPayment->id),
                    'payee' => $expenseClaim->payee,
                    'amount' => $expenseClaimPayment->transaction->amount,
                    'description' => 'Reversal: Payment for Expense Claim: ' . $expenseClaim->title,
                    'category' => 'expense_claim_reversal',
                    'created_by' => Auth::id(),
                ]);
                $reversalCreated = true;
            }

            // Delete the payment (this will trigger model events to update expense claim status)
            $expenseClaimPayment->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while deleting the payment: ' . $e->getMessage(),
            ]);
        }
        $message = $reversalCreated
            ? 'Payment deleted successfully. A reverse transaction has been created to credit the account.'
            : 'Payment deleted successfully.';
        return Redirect::route('expense-claims.show', $expenseClaimId)
            ->with('success', $message);
    }
}
