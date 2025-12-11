<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = BankAccount::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('bank_name', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('active', $status === 'active');
            })
            ->orderBy('name');

        $bankAccounts = $query->paginate(100)->withQueryString()
            ->through(fn($account) => [
                'id' => $account->id,
                'name' => $account->name,
                'account_number' => $account->account_number,
                'bank_name' => $account->bank_name,
                'current_balance' => $account->current_balance,
                'active' => $account->active,
                'photo_path' => $account->photo_path,
            ]);

        return Inertia::render('BankAccounts/Index', [
            'filters' => $request->only(['search', 'status']),
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $organizations = Organization::orderBy('name')->get();

        return Inertia::render('BankAccounts/Create', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
            'bank_name' => 'required|string|max:255',
            'current_balance' => 'required|numeric',
            'active' => 'required|boolean',
        ]);

        $bankAccount = BankAccount::create([
            'name' => $validated['name'],
            'account_number' => $validated['account_number'],
            'bank_name' => $validated['bank_name'],
            'opening_balance' => $validated['current_balance'],
            'current_balance' => $validated['current_balance'], // Set current balance to opening balance initially
            'active' => $validated['active'],
        ]);

        return Redirect::route('bank-accounts')->with('success', 'Bank account created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, BankAccount $bankAccount): Response
    {
        // Ensure the balance is up to date
        $bankAccount->updateBalance();

        $query = $bankAccount->transactions()
            ->with(['branch', 'creator'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('payee', 'like', "%{$search}%");
                });
            })
            ->when($request->input('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->input('payment_mode'), function ($query, $mode) {
                $query->where('payment_mode', $mode);
            })
            ->when($request->input('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->where('transaction_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->where('transaction_date', '<=', $dateTo);
            })
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        $transactions = $query->paginate(25)
            ->through(fn($transaction) => [
                'id' => $transaction->id,
                'transaction_date' => $transaction->transaction_date,
                'type' => $transaction->type,
                'payment_mode' => $transaction->payment_mode,
                'reference_number' => $transaction->reference_number,
                'payee' => $transaction->payee,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'category' => $transaction->category,
                'is_reconciled' => $transaction->is_reconciled,
                'branch' => $transaction->branch ? [
                    'id' => $transaction->branch->id,
                    'name' => $transaction->branch->name,
                ] : null,
                'shift' => $transaction->shift ? [
                    'id' => $transaction->shift->id,
                    'name' => $transaction->shift->name,
                ] : null,
                'creator' => $transaction->creator ? [
                    'id' => $transaction->creator->id,
                    'name' => $transaction->creator->name,
                ] : null,
            ]);

        // Calculate running balance for statement view
        $runningBalance = $bankAccount->opening_balance;
        $creditTotal = $bankAccount->transactions()->where('type', 'credit')->sum('amount');
        $debitTotal = $bankAccount->transactions()->where('type', 'debit')->sum('amount');

        return Inertia::render('BankAccounts/Show', [
            'bankAccount' => [
                'id' => $bankAccount->id,
                'name' => $bankAccount->name,
                'account_number' => $bankAccount->account_number,
                'bank_name' => $bankAccount->bank_name,
                'branch' => $bankAccount->branch,
                'opening_balance' => $bankAccount->opening_balance,
                'current_balance' => $bankAccount->current_balance,
                'active' => $bankAccount->active,
                'photo_path' => $bankAccount->photo_path,
                'updated_at' => $bankAccount->updated_at,
            ],
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'type', 'payment_mode', 'category', 'date_from', 'date_to']),
            'summary' => [
                'total_credits' => $creditTotal,
                'total_debits' => $debitTotal,
                'net_movement' => $creditTotal - $debitTotal,
            ],
            'paymentModes' => ['cash', 'cheque', 'bank_transfer', 'nafa', 'wave', 'other'],
            'categories' => [
                'deposit',
                'sales',
                'vendor_payment',
                'petty_cash',
                'recurring_bill',
                'miscellaneous',
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): Response
    {
        $organizations = Organization::orderBy('name')->get();

        return Inertia::render('BankAccounts/Edit', [
            'bankAccount' => [
                'id' => $bankAccount->id,
                'name' => $bankAccount->name,
                'account_number' => $bankAccount->account_number,
                'bank_name' => $bankAccount->bank_name,
                'current_balance' => $bankAccount->current_balance,
                'active' => $bankAccount->active,
                'photo_path' => $bankAccount->photo_path,
                'updated_at' => $bankAccount->updated_at,
            ],
            'organizations' => $organizations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number,' . $bankAccount->id,
            'bank_name' => 'required|string|max:255',
            'current_balance' => 'required|numeric',
            'active' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Remove photo from validated data as it's not a database field
        unset($validated['photo']);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($bankAccount->photo_path) {
                Storage::disk('public')->delete($bankAccount->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('bank-accounts', 'public');
        }

        // If current_balance is being manually adjusted, we need to adjust opening_balance
        // to maintain the relationship: current_balance = opening_balance + credits - debits
        // This ensures that when updateBalance() is called later, it will still result in the manually set balance
        if (isset($validated['current_balance']) && $validated['current_balance'] != $bankAccount->current_balance) {
            // Calculate current credits and debits
            $credits = $bankAccount->transactions()->where('type', 'credit')->sum('amount');
            $debits = $bankAccount->transactions()->where('type', 'debit')->sum('amount');

            // Calculate what opening_balance should be to achieve the desired current_balance
            // Formula: current_balance = opening_balance + credits - debits
            // Therefore: opening_balance = current_balance - credits + debits
            $validated['opening_balance'] = $validated['current_balance'] - $credits + $debits;
        }

        $bankAccount->update($validated);

        return Redirect::route('bank-accounts')->with('success', 'Bank account updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        // Check if bank account has any deposits (including soft-deleted ones)
        $depositsCount = $bankAccount->deposits()->withTrashed()->count();

        if ($depositsCount > 0) {
            return Redirect::back()->with(
                'error',
                "Cannot delete bank account '{$bankAccount->name}' because it has {$depositsCount} related deposit(s). Please deactivate the account instead."
            );
        }

        // Check if bank account has any transactions
        $transactionsCount = $bankAccount->transactions()->count();

        if ($transactionsCount > 0) {
            return Redirect::back()->with(
                'error',
                "Cannot delete bank account '{$bankAccount->name}' because it has {$transactionsCount} related transaction(s). Please deactivate the account instead."
            );
        }

        // Delete associated photo if exists
        if ($bankAccount->photo_path) {
            Storage::disk('public')->delete($bankAccount->photo_path);
        }

        $bankAccount->delete();

        return Redirect::route('bank-accounts')->with('success', 'Bank account deleted successfully.');
    }
}
