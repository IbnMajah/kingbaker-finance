<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Transaction::query()
            ->with(['bankAccount', 'branch', 'shift', 'creator'])
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
            ->when($request->input('status'), function ($query, $status) {
                if ($status === 'reconciled') {
                    $query->where('is_reconciled', true);
                } elseif ($status === 'pending') {
                    $query->where('is_reconciled', false);
                }
            })
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        $transactions = $query->paginate(25)
            ->through(fn ($transaction) => [
                'id' => $transaction->id,
                'date' => $transaction->transaction_date,
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'payment_mode' => $transaction->payment_mode,
                'payee' => $transaction->payee,
                'type' => $transaction->type,
                'reference' => $transaction->reference_number,
                'is_reconciled' => $transaction->is_reconciled,
                'category' => $transaction->category,
                'bank_account' => $transaction->bankAccount ? [
                    'id' => $transaction->bankAccount->id,
                    'name' => $transaction->bankAccount->name,
                ] : null,
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

        // Calculate summary statistics for all transactions (not just current page)
        $baseQuery = Transaction::query();

        // Apply same filters for summary calculations
        $baseQuery->when($request->input('search'), function ($query, $search) {
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
        ->when($request->input('status'), function ($query, $status) {
            if ($status === 'reconciled') {
                $query->where('is_reconciled', true);
            } elseif ($status === 'pending') {
                $query->where('is_reconciled', false);
            }
        });

        $summary = [
            'total_transactions' => $baseQuery->count(),
            'total_credits' => $baseQuery->clone()->where('type', 'credit')->count(),
            'total_debits' => $baseQuery->clone()->where('type', 'debit')->count(),
            'pending_reconciliation' => $baseQuery->clone()->where('is_reconciled', false)->count(),
            'total_credit_amount' => $baseQuery->clone()->where('type', 'credit')->sum('amount'),
            'total_debit_amount' => $baseQuery->clone()->where('type', 'debit')->sum('amount'),
        ];

        return Inertia::render('Transactions/Index', [
            'filters' => $request->only(['search', 'type', 'payment_mode', 'status']),
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $bankAccounts = BankAccount::where('active', true)->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $shifts = Shift::all();

        return Inertia::render('Transactions/Create', [
            'bankAccounts' => $bankAccounts,
            'branches' => $branches,
            'shifts' => $shifts,
            'paymentTypes' => ['credit', 'debit'],
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:credit,debit',
            'payment_mode' => 'required|in:cash,cheque,bank_transfer,nafa,wave,other',
            'reference_number' => 'nullable|string|max:255',
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'category' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
        }

        $transaction = Transaction::create([
            ...$validated,
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        return Redirect::route('transactions')->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): Response
    {
        $transaction->load(['bankAccount', 'branch', 'shift', 'creator']);

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction): Response
    {
        $bankAccounts = BankAccount::where('active', true)->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $shifts = Shift::all();

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'bankAccounts' => $bankAccounts,
            'branches' => $branches,
            'shifts' => $shifts,
            'paymentTypes' => ['credit', 'debit'],
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:credit,debit',
            'payment_mode' => 'required|in:cash,cheque,bank_transfer,nafa,wave,other',
            'reference_number' => 'nullable|string|max:255',
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'category' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('receipt_image')) {
            // Delete old image if exists
            if ($transaction->image_path) {
                Storage::disk('public')->delete($transaction->image_path);
            }
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
            $validated['image_path'] = $imagePath;
        }

        $transaction->update($validated);

        return Redirect::route('transactions')->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        // Delete associated image if exists
        if ($transaction->image_path) {
            Storage::disk('public')->delete($transaction->image_path);
        }

        $transaction->delete();

        return Redirect::route('transactions')->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Reconcile a transaction.
     */
    public function reconcile(Transaction $transaction): RedirectResponse
    {
        $transaction->update([
            'is_reconciled' => true
        ]);

        return Redirect::back()->with('success', 'Transaction reconciled successfully.');
    }

    /**
     * Unreconcile a transaction.
     */
    public function unreconcile(Transaction $transaction): RedirectResponse
    {
        $transaction->update([
            'is_reconciled' => false
        ]);

        return Redirect::back()->with('success', 'Transaction marked as pending.');
    }
}