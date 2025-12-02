<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MiscellaneousController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Transaction::query()
            ->with(['bankAccount', 'branch', 'creator'])
            ->where('category', 'miscellaneous')
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
            ->when($request->input('bank_account_id'), function ($query, $bankAccountId) {
                $query->where('bank_account_id', $bankAccountId);
            })
            ->when($request->input('branch_id'), function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->input('category'), function ($query, $category) {
                $query->where('subcategory', $category);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->whereDate('transaction_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->whereDate('transaction_date', '<=', $dateTo);
            })
            ->orderBy('transaction_date', 'desc');

        // Calculate summary statistics with same filters
        $summaryQuery = clone $query;
        $summaryStats = [
            'total_transactions' => $summaryQuery->count(),
            'total_debits' => $summaryQuery->where('type', 'debit')->sum('amount'),
            'total_credits' => $summaryQuery->where('type', 'credit')->sum('amount'),
            'net_amount' => $summaryQuery->where('type', 'credit')->sum('amount') -
                $summaryQuery->where('type', 'debit')->sum('amount'),
        ];

        $transactions = $query->paginate(5)->withQueryString()
            ->through(fn($transaction) => [
                'id' => $transaction->id,
                'reference_number' => $transaction->reference_number,
                'transaction_date' => $transaction->transaction_date,
                'description' => $transaction->description,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'category' => $transaction->subcategory,
                'bank_account' => $transaction->bankAccount ? [
                    'id' => $transaction->bankAccount->id,
                    'name' => $transaction->bankAccount->name,
                ] : null,
                'branch' => $transaction->branch ? [
                    'id' => $transaction->branch->id,
                    'name' => $transaction->branch->name,
                ] : null,
                'creator' => $transaction->creator ? [
                    'id' => $transaction->creator->id,
                    'name' => $transaction->creator->name,
                ] : null,
            ]);

        return Inertia::render('Miscellaneous/Index', [
            'filters' => $request->only(['search', 'type', 'bank_account_id', 'branch_id', 'category', 'date_from', 'date_to']),
            'transactions' => $transactions,
            'totalTransactions' => $summaryStats['total_transactions'],
            'totalDebits' => $summaryStats['total_debits'],
            'totalCredits' => $summaryStats['total_credits'],
            'netAmount' => $summaryStats['net_amount'],
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'categories' => [
                'office_equipment',
                'furniture',
                'appliances',
                'renovations',
                'repairs',
                'software',
                'licenses',
                'other'
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Miscellaneous/Create', [
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'categories' => [
                'office_equipment',
                'furniture',
                'appliances',
                'renovations',
                'repairs',
                'software',
                'licenses',
                'other'
            ],
            'paymentModes' => [
                'cash',
                'cheque',
                'bank_transfer',
                'nafa',
                'wave',
                'other'
            ]
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
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'payment_mode' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'category' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'receipt_image' => 'nullable|image|max:2048',
            'notes' => 'nullable|string'
        ]);

        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
        }

        // Remove receipt_image from validated data since it's not a database column
        unset($validated['receipt_image']);
        unset($validated['notes']);

        // Save category as subcategory
        $subcategory = $validated['category'];
        unset($validated['category']);

        Transaction::create([
            ...$validated,
            'category' => 'miscellaneous',
            'subcategory' => $subcategory,
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        return Redirect::route('miscellaneous.index')->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->category !== 'miscellaneous') {
            abort(404);
        }

        $transaction->load(['bankAccount', 'branch', 'creator']);

        return Inertia::render('Miscellaneous/Show', [
            'transaction' => [
                'id' => $transaction->id,
                'reference_number' => $transaction->reference_number,
                'transaction_date' => $transaction->transaction_date,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'category' => $transaction->subcategory,
                'payment_mode' => $transaction->payment_mode,
                'bank_account' => $transaction->bankAccount ? [
                    'id' => $transaction->bankAccount->id,
                    'name' => $transaction->bankAccount->name,
                ] : null,
                'branch' => $transaction->branch ? [
                    'id' => $transaction->branch->id,
                    'name' => $transaction->branch->name,
                ] : null,
                'creator' => $transaction->creator ? [
                    'id' => $transaction->creator->id,
                    'name' => $transaction->creator->name,
                ] : null,
                'image_path' => $transaction->image_path,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->category !== 'miscellaneous') {
            abort(404);
        }

        return Inertia::render('Miscellaneous/Edit', [
            'transaction' => [
                'id' => $transaction->id,
                'reference_number' => $transaction->reference_number,
                'transaction_date' => $transaction->transaction_date,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'category' => $transaction->subcategory,
                'payment_mode' => $transaction->payment_mode,
                'bank_account_id' => $transaction->bank_account_id,
                'branch_id' => $transaction->branch_id,
                'image_path' => $transaction->image_path,
            ],
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'categories' => [
                'office_equipment',
                'furniture',
                'appliances',
                'renovations',
                'repairs',
                'software',
                'licenses',
                'other'
            ],
            'paymentModes' => [
                'cash',
                'cheque',
                'bank_transfer',
                'nafa',
                'wave',
                'other'
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->category !== 'miscellaneous') {
            abort(404);
        }

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'category' => 'required|string',
            'payment_mode' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
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

        // Remove receipt_image from validated data since it's not a database column
        unset($validated['receipt_image']);

        // Save category as subcategory
        $subcategory = $validated['category'];
        unset($validated['category']);

        $transaction->update([
            ...$validated,
            'subcategory' => $subcategory,
        ]);

        return Redirect::route('miscellaneous.index')->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->category !== 'miscellaneous') {
            abort(404);
        }

        // Delete receipt image if exists
        if ($transaction->image_path) {
            Storage::disk('public')->delete($transaction->image_path);
        }

        $transaction->delete();

        return Redirect::route('miscellaneous.index')->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Remove the receipt image from the transaction.
     */
    public function removeImage(string $id): RedirectResponse
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->category !== 'miscellaneous') {
            abort(404);
        }

        if ($transaction->image_path) {
            Storage::disk('public')->delete($transaction->image_path);
            $transaction->update(['image_path' => null]);
        }

        return Redirect::back()->with('success', 'Receipt image removed successfully.');
    }
}
