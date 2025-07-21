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
            ->with(['user', 'transaction', 'transaction.bankAccount', 'items'])
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
            ->when($request->input('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->input('expense_type'), function ($query, $expenseType) {
                $query->where('expense_type', $expenseType);
            })
            ->latest();

        $claims = $query->paginate(10);

        return Inertia::render('ExpenseClaims/Index', [
            'filters' => $request->only(['search', 'status', 'category', 'expense_type']),
            'claims' => [
                'data' => collect($claims->items())->map(function ($claim) {
                    return [
                        'id' => $claim->id,
                        'claim_number' => $claim->reference_id,
                        'claim_date' => $claim->claim_date,
                        'title' => $claim->title,
                        'category' => $claim->category,
                        'expense_type' => $claim->expense_type,
                        'payee' => $claim->payee,
                        'description' => $claim->notes,
                        'total' => $claim->total,
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
            'draftClaims' => ExpenseClaim::where('status', 'draft')->count(),
            'activeClaims' => ExpenseClaim::where('status', 'active')->count(),
            'cancelledClaims' => ExpenseClaim::where('status', 'cancelled')->count(),
            'totalAmount' => ExpenseClaim::sum('total'),
            'categories' => [
                'office_supplies' => 'Office Supplies',
                'travel' => 'Travel',
                'meals' => 'Meals',
                'utilities' => 'Utilities',
                'maintenance' => 'Maintenance',
                'other' => 'Other'
            ],
            'expenseTypes' => [
                'petty_cash' => 'Petty Cash',
                'cash_on_hand' => 'Cash on Hand',
                'other' => 'Other'
            ],
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
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image|max:2048',
            'expense_type' => 'required|string|in:petty_cash,cash_on_hand,other',
            'branch_id' => 'nullable|exists:branches,id',
            'payee' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $receiptPath = null;
            if ($request->hasFile('receipt_image')) {
                $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
            }

            $expenseClaim = ExpenseClaim::create([
                'user_id' => auth()->id(),
                'reference_id' => $validated['reference_id'],
                'claim_date' => $validated['claim_date'],
                'title' => $validated['title'],
                'category' => $validated['category'],
                'receipt_image_path' => $receiptPath,
                'status' => 'active',
                'expense_type' => $validated['expense_type'],
                'branch_id' => $validated['branch_id'],
                'payee' => $validated['payee'],
                'notes' => $validated['notes'],
                'total' => collect($validated['items'])->sum(function($item) {
                    return $item['unit_price'] * ($item['quantity'] ?? 1);
                }),
            ]);

            foreach ($validated['items'] as $item) {
                $expenseClaim->items()->create([
                    'description' => $item['description'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'] ?? 1,
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
        $expenseClaim->load(['user', 'transaction', 'transaction.bankAccount', 'items', 'branch']);

        return Inertia::render('ExpenseClaims/Show', [
            'expenseClaim' => [
                'id' => $expenseClaim->id,
                'claim_number' => $expenseClaim->reference_id,
                'date_posted' => $expenseClaim->created_at,
                'claim_date' => $expenseClaim->claim_date,
                'title' => $expenseClaim->title,
                'category' => $expenseClaim->category,
                'receipt_image_path' => $expenseClaim->receipt_image_path,
                'payee' => $expenseClaim->payee,
                'description' => $expenseClaim->notes,
                'total' => $expenseClaim->total,
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
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                ]),
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

        $expenseClaim->load(['user', 'items', 'branch']);

        return Inertia::render('ExpenseClaims/Edit', [
            'expenseClaim' => [
                'id' => $expenseClaim->id,
                'reference_id' => $expenseClaim->reference_id,
                'claim_date' => $expenseClaim->claim_date->format('Y-m-d'),
                'title' => $expenseClaim->title,
                'category' => $expenseClaim->category,
                'receipt_image_path' => $expenseClaim->receipt_image_path,
                'payee' => $expenseClaim->payee,
                'expense_type' => $expenseClaim->expense_type,
                'total' => $expenseClaim->total,
                'status' => $expenseClaim->status,
                'branch_id' => $expenseClaim->branch_id,
                'notes' => $expenseClaim->notes,
                'items' => $expenseClaim->items,
            ],
            'expenseTypes' => ['petty_cash', 'cash_on_hand', 'other'],
            'categories' => ['office_supplies', 'travel', 'meals', 'utilities', 'maintenance', 'other'],
            'branches' => Branch::orderBy('name')
                ->get()
                ->map(fn($branch) => [
                    'value' => $branch->id,
                    'label' => $branch->name,
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
            'claim_date' => 'required|date',
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image|max:2048',
            'payee' => 'required|string|max:255',
            'expense_type' => 'required|string|in:petty_cash,cash_on_hand,other',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $receiptPath = $expenseClaim->receipt_image_path;
            if ($request->hasFile('receipt_image')) {
                if ($receiptPath) {
                    Storage::disk('public')->delete($receiptPath);
                }
                $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
            }

            // Update the expense claim
            $expenseClaim->update([
                'claim_date' => $validated['claim_date'],
                'title' => $validated['title'],
                'category' => $validated['category'],
                'receipt_image_path' => $receiptPath,
                'payee' => $validated['payee'],
                'expense_type' => $validated['expense_type'],
                'branch_id' => $validated['branch_id'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing items
            $expenseClaim->items()->delete();

            // Create new items
            foreach ($validated['items'] as $itemData) {
                $expenseClaim->items()->create([
                    'description' => $itemData['description'],
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            // Update total amount
            $expenseClaim->updateTotalAmount();

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
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== 'draft' && $expenseClaim->status !== 'cancelled') {
            return Redirect::back()->with('error', 'Only draft or cancelled expense claims can be deleted.');
        }

        // Delete receipt image if it exists
        if ($expenseClaim->receipt_image_path) {
            Storage::disk('public')->delete($expenseClaim->receipt_image_path);
        }

        // Delete all expense items first
        $expenseClaim->items()->delete();

        $expenseClaim->delete();

        return Redirect::route('expense-claims')->with('success', 'Expense claim deleted successfully.');
    }
}