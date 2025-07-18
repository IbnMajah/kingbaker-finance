<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ExpenseClaim;
use App\Models\ExpenseItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = ExpenseClaim::query()
            ->with(['user', 'approver', 'transaction', 'transaction.bankAccount', 'items'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference_id', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhere('payee', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest();

        $claims = $query->paginate(10);

        return Inertia::render('ExpenseClaims/Index', [
            'filters' => $request->only(['search', 'status']),
            'claims' => [
                'data' => collect($claims->items())->map(function ($claim) {
                    return [
                        'id' => $claim->id,
                        'claim_number' => $claim->reference_id,
                        'transaction_date' => $claim->claim_date,
                        'payee' => $claim->payee,
                        'description' => $claim->notes,
                        'amount' => $claim->total_amount,
                        'status' => $claim->status,
                        'branch' => $claim->branch ? [
                            'id' => $claim->branch->id,
                            'name' => $claim->branch->name,
                        ] : null,
                        'creator' => $claim->user ? [
                            'id' => $claim->user->id,
                            'first_name' => $claim->user->first_name,
                            'last_name' => $claim->user->last_name,
                        ] : null,
                        'approver' => $claim->approver ? [
                            'id' => $claim->approver->id,
                            'name' => $claim->approver->name,
                        ] : null,
                        'transaction' => $claim->transaction ? [
                            'id' => $claim->transaction->id,
                            'reference_number' => $claim->transaction->reference_number,
                            'amount' => $claim->transaction->amount,
                            'bank_account' => $claim->transaction->bankAccount ? [
                                'id' => $claim->transaction->bankAccount->id,
                                'name' => $claim->transaction->bankAccount->name,
                            ] : null,
                        ] : null,
                    ];
                }),
                'current_page' => $claims->currentPage(),
                'per_page' => $claims->perPage(),
                'total' => $claims->total(),
                'from' => $claims->firstItem(),
                'to' => $claims->lastItem(),
                'prev_page_url' => $claims->previousPageUrl(),
                'next_page_url' => $claims->nextPageUrl(),
            ],
            'totalClaims' => ExpenseClaim::count(),
            'pendingClaims' => ExpenseClaim::where('status', 'submitted')->count(),
            'approvedClaims' => ExpenseClaim::where('status', 'approved')->count(),
            'totalAmount' => ExpenseClaim::sum('total_amount'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ExpenseClaims/Create', [
            'expenseTypes' => ['petty_cash', 'cash_on_hand', 'other'],
            'categories' => ['office_supplies', 'travel', 'meals', 'utilities', 'maintenance', 'other'],
            'bankAccounts' => BankAccount::select('id as value', 'name as label')->get(),
            'branches' => Branch::select('id as value', 'name as label')->get(),
            'referenceId' => 'EXP-' . strtoupper(Str::random(7)),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference_id' => 'required|string|unique:expense_claims,reference_id',
            'claim_date' => 'required|date',
            'expense_type' => 'required|string|in:petty_cash,cash_on_hand,other',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'payee' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.expense_date' => 'required|date',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.category' => 'nullable|string',
            'items.*.receipt_image' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $expenseClaim = ExpenseClaim::create([
                'user_id' => auth()->id(),
                'reference_id' => $validated['reference_id'],
                'claim_date' => $validated['claim_date'],
                'status' => 'draft',
                'expense_type' => $validated['expense_type'],
                'bank_account_id' => $validated['bank_account_id'],
                'branch_id' => $validated['branch_id'],
                'payee' => $validated['payee'],
                'notes' => $validated['notes'],
                'total_amount' => collect($validated['items'])->sum('amount'),
            ]);

            foreach ($validated['items'] as $item) {
                $imagePath = null;
                if (isset($item['receipt_image']) && $item['receipt_image']) {
                    $imagePath = $item['receipt_image']->store('receipts', 'public');
                }

                $expenseClaim->items()->create([
                    'expense_date' => $item['expense_date'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'quantity' => $item['quantity'] ?? 1,
                    'category' => $item['category'] ?? null,
                    'receipt_image_path' => $imagePath,
                ]);
            }

            DB::commit();
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('success', 'Expense claim created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while creating the expense claim: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseClaim $expenseClaim): Response
    {
        $expenseClaim->load(['user', 'approver', 'transaction', 'transaction.bankAccount', 'items', 'branch']);

        return Inertia::render('ExpenseClaims/Show', [
            'expenseClaim' => [
                'id' => $expenseClaim->id,
                'claim_number' => $expenseClaim->reference_id,
                'date_posted' => $expenseClaim->created_at,
                'transaction_date' => $expenseClaim->claim_date,
                'payee' => $expenseClaim->payee,
                'description' => $expenseClaim->notes,
                'amount' => $expenseClaim->total_amount,
                'status' => $expenseClaim->status,
                'branch' => $expenseClaim->branch ? [
                    'id' => $expenseClaim->branch->id,
                    'name' => $expenseClaim->branch->name,
                ] : null,
                'creator' => $expenseClaim->user ? [
                    'id' => $expenseClaim->user->id,
                    'first_name' => $expenseClaim->user->first_name,
                    'last_name' => $expenseClaim->user->last_name,
                ] : null,
                'approver' => $expenseClaim->approver ? [
                    'id' => $expenseClaim->approver->id,
                    'name' => $expenseClaim->approver->name,
                ] : null,
                'transaction' => $expenseClaim->transaction ? [
                    'id' => $expenseClaim->transaction->id,
                    'reference_number' => $expenseClaim->transaction->reference_number,
                    'amount' => $expenseClaim->transaction->amount,
                    'bank_account' => $expenseClaim->transaction->bankAccount ? [
                        'id' => $expenseClaim->transaction->bankAccount->id,
                        'name' => $expenseClaim->transaction->bankAccount->name,
                    ] : null,
                ] : null,
                'items' => $expenseClaim->items->map(fn($item) => [
                    'id' => $item->id,
                    'expense_date' => $item->expense_date,
                    'title' => $item->title,
                    'description' => $item->description,
                    'amount' => $item->amount,
                    'quantity' => $item->quantity,
                    'category' => $item->category,
                    'receipt_image_path' => $item->receipt_image_path,
                ]),
                'receipts' => $expenseClaim->items->map(fn($item) => [
                    'id' => $item->id,
                    'name' => basename($item->receipt_image_path),
                    'path' => $item->receipt_image_path,
                ])->filter(fn($receipt) => $receipt['path']),
                'updated_at' => $expenseClaim->updated_at,
                'notes' => $expenseClaim->notes,
            ],
        ]);
    }

    /**
     * Edit the expense claim.
     */
    public function edit(ExpenseClaim $expenseClaim): Response|RedirectResponse
    {
        if ($expenseClaim->status !== 'draft') {
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('error', 'Only draft expense claims can be edited.');
        }

        $expenseClaim->load(['user', 'items', 'bankAccount', 'branch']);

        return Inertia::render('ExpenseClaims/Edit', [
            'expenseClaim' => [
                'id' => $expenseClaim->id,
                'claim_number' => $expenseClaim->reference_id,
                'transaction_date' => $expenseClaim->claim_date->format('Y-m-d'),
                'payee' => $expenseClaim->payee,
                'amount' => $expenseClaim->total_amount,
                'status' => $expenseClaim->status,
                'bank_account_id' => $expenseClaim->bank_account_id,
                'branch_id' => $expenseClaim->branch_id,
                'description' => $expenseClaim->notes,
                'notes' => '',
                'receipts' => $expenseClaim->items->map(fn($item) => [
                    'id' => $item->id,
                    'name' => basename($item->receipt_image_path),
                    'path' => $item->receipt_image_path,
                ])->filter(fn($receipt) => $receipt['path']),
            ],
            'bankAccounts' => BankAccount::where('active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($account) => [
                    'id' => $account->id,
                    'name' => $account->name,
                ]),
            'branches' => Branch::orderBy('name')
                ->get()
                ->map(fn($branch) => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== 'draft') {
            return Redirect::back()->with('error', 'Only draft expense claims can be updated.');
        }

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'payee' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'description' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expenseClaim->update([
                'claim_date' => $validated['transaction_date'],
                'payee' => $validated['payee'],
                'total_amount' => $validated['amount'],
                'bank_account_id' => $validated['bank_account_id'],
                'branch_id' => $validated['branch_id'],
                'notes' => $validated['description'],
            ]);

            DB::commit();
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('success', 'Expense claim updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while updating the expense claim: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Submit the expense claim for approval.
     */
    public function submit(ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== 'draft') {
            return Redirect::back()->with('error', 'Only draft expense claims can be submitted.');
        }

        $expenseClaim->update([
            'status' => 'submitted',
        ]);

        return Redirect::route('expense-claims')->with('success', 'Expense claim submitted for approval.');
    }

    /**
     * Approve the expense claim.
     */
    public function approve(ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== 'submitted') {
            return Redirect::back()->with('error', 'Only submitted expense claims can be approved.');
        }

        $expenseClaim->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return Redirect::route('expense-claims')->with('success', 'Expense claim approved.');
    }

    /**
     * Reject the expense claim.
     */
    public function reject(Request $request, ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== 'submitted') {
            return Redirect::back()->with('error', 'Only submitted expense claims can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $notes = $expenseClaim->notes ?? '';
        $notes .= "\n\nRejection Reason: " . $validated['rejection_reason'];

        $expenseClaim->update([
            'status' => 'rejected',
            'notes' => $notes,
        ]);

        return Redirect::route('expense-claims')->with('success', 'Expense claim rejected.');
    }

    /**
     * Pay the expense claim.
     */
    public function pay(Request $request, ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== 'approved') {
            return Redirect::back()->with('error', 'Only approved expense claims can be paid.');
        }

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'payment_mode' => 'required|in:cash,cheque,bank_transfer,nafa,wave,other',
            'reference_number' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create the transaction
            $transaction = Transaction::create([
                'bank_account_id' => $validated['bank_account_id'],
                'transaction_date' => $validated['transaction_date'],
                'type' => 'debit',
                'payment_mode' => $validated['payment_mode'],
                'reference_number' => $validated['reference_number'],
                'payee' => $expenseClaim->user->name,
                'amount' => $expenseClaim->total_amount,
                'description' => 'Payment for expense claim #' . $expenseClaim->reference_id,
                'category' => $expenseClaim->expense_type,
                'created_by' => Auth::id(),
            ]);

            // Update the expense claim
            $expenseClaim->update([
                'status' => 'paid',
                'transaction_id' => $transaction->id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while processing the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('expense-claims')->with('success', 'Expense claim paid successfully.');
    }

    /**
     * Show the form for paying an expense claim.
     */
    public function showPayForm(ExpenseClaim $expenseClaim): Response|RedirectResponse
    {
        if ($expenseClaim->status !== 'approved') {
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('error', 'Only approved expense claims can be paid.');
        }

        $expenseClaim->load(['user', 'items']);
        $bankAccounts = BankAccount::where('active', true)->orderBy('name')->get();

        return Inertia::render('ExpenseClaims/Pay', [
            'expenseClaim' => $expenseClaim,
            'bankAccounts' => $bankAccounts,
            'paymentModes' => ['cash', 'cheque', 'bank_transfer', 'nafa', 'wave', 'other'],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== 'draft' && $expenseClaim->status !== 'rejected') {
            return Redirect::back()->with('error', 'Only draft or rejected expense claims can be deleted.');
        }

        // Delete all expense items first
        foreach ($expenseClaim->items as $item) {
            if ($item->receipt_image_path) {
                Storage::disk('public')->delete($item->receipt_image_path);
            }
            $item->delete();
        }

        $expenseClaim->delete();

        return Redirect::route('expense-claims')->with('success', 'Expense claim deleted successfully.');
    }
}