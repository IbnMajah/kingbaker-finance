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
        $user = Auth::user();

        $query = ExpenseClaim::query()
            ->with(['user', 'transaction', 'transaction.bankAccount', 'items'])
            // Apply branch filtering for non-admin users
            ->when(!($user->role === 'admin' || $user->owner), function ($query) use ($user) {
                if ($user->branch_id) {
                    $query->where('branch_id', $user->branch_id);
                }
            })
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
        $user = Auth::user();

        return Inertia::render('ExpenseClaims/Create', [
            'expenseTypes' => ['petty_cash', 'cash_on_hand', 'other'],
            'categories' => ['office_supplies', 'travel', 'meals', 'utilities', 'maintenance', 'other'],
            'branches' => ($user->role === 'admin' || $user->owner
                ? Branch::select('id as value', 'name as label')->get()
                : Branch::where('id', $user->branch_id)->select('id as value', 'name as label')->get()
            ),
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
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
            'expense_type' => 'required|string|in:petty_cash,cash_on_hand,other',
            'branch_id' => 'nullable|exists:branches,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payee' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.receipt_image' => 'nullable|image|max:2048',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $total = collect($validated['items'])->sum(function ($item) {
                return $item['unit_price'] * ($item['quantity'] ?? 1);
            });

            $expenseClaim = ExpenseClaim::create([
                'user_id' => auth()->id(),
                'reference_id' => $validated['reference_id'],
                'claim_date' => $validated['claim_date'],
                'title' => $validated['title'],
                'status' => 'active',
                'expense_type' => $validated['expense_type'],
                'branch_id' => $validated['branch_id'],
                'bank_account_id' => $validated['bank_account_id'],
                'payee' => $validated['payee'],
                'notes' => $validated['notes'],
                'total' => $total,
            ]);

            foreach ($validated['items'] as $index => $item) {
                $receiptPath = null;
                if ($request->hasFile("items.{$index}.receipt_image")) {
                    $receiptPath = $request->file("items.{$index}.receipt_image")->store('receipts', 'public');
                }

                $expenseClaim->items()->create([
                    'description' => $item['description'],
                    'category' => $item['category'] ?? null,
                    'receipt_image_path' => $receiptPath,
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
        $user = Auth::user();

        // Ensure non-admin users can only view expense claims from their branch
        if (!($user->role === 'admin' || $user->owner) && $expenseClaim->branch_id !== $user->branch_id) {
            abort(403, 'You can only view expense claims from your branch.');
        }

        $expenseClaim->load(['user', 'transaction', 'transaction.bankAccount', 'items', 'branch', 'bankAccount']);

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
                'bank_account' => $expenseClaim->bankAccount ? [
                    'id' => $expenseClaim->bankAccount->id,
                    'name' => $expenseClaim->bankAccount->name,
                ] : null,
                'items' => $expenseClaim->items->map(fn($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'category' => $item->category,
                    'receipt_image_path' => $item->receipt_image_path,
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
        $user = Auth::user();

        // Ensure non-admin users can only edit expense claims from their branch
        if (!($user->role === 'admin' || $user->owner) && $expenseClaim->branch_id !== $user->branch_id) {
            abort(403, 'You can only edit expense claims from your branch.');
        }

        if ($expenseClaim->status !== 'active' && $expenseClaim->status !== 'draft') {
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('error', 'Only active or draft expense claims can be edited.');
        }

        $expenseClaim->load(['user', 'items', 'branch']);

        return Inertia::render('ExpenseClaims/Edit', [
            'expenseClaim' => [
                'id' => $expenseClaim->id,
                'reference_id' => $expenseClaim->reference_id,
                'claim_date' => $expenseClaim->claim_date->format('Y-m-d'),
                'title' => $expenseClaim->title,
                'payee' => $expenseClaim->payee,
                'expense_type' => $expenseClaim->expense_type,
                'total' => $expenseClaim->total,
                'status' => $expenseClaim->status,
                'branch_id' => $expenseClaim->branch_id,
                'bank_account_id' => $expenseClaim->bank_account_id,
                'notes' => $expenseClaim->notes,
                'items' => $expenseClaim->items->map(fn($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'category' => $item->category,
                    'receipt_image_path' => $item->receipt_image_path,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                ]),
            ],
            'expenseTypes' => ['petty_cash', 'cash_on_hand', 'other'],
            'categories' => ['office_supplies', 'travel', 'meals', 'utilities', 'maintenance', 'other'],
            'branches' => ($user->role === 'admin' || $user->owner
                ? Branch::orderBy('name')->get()
                : Branch::where('id', $user->branch_id)->orderBy('name')->get()
            )->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name,
            ]),
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($account) => [
                'value' => $account->id,
                'label' => $account->name,
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseClaim $expenseClaim): RedirectResponse
    {
        $user = Auth::user();

        // Ensure non-admin users can only update expense claims from their branch
        if (!($user->role === 'admin' || $user->owner) && $expenseClaim->branch_id !== $user->branch_id) {
            abort(403, 'You can only update expense claims from your branch.');
        }

        if ($expenseClaim->status !== 'active' && $expenseClaim->status !== 'draft') {
            return Redirect::back()->with('error', 'Only active or draft expense claims can be updated.');
        }

        $validated = $request->validate([
            'claim_date' => 'required|date',
            'title' => 'required|string|max:255',
            'payee' => 'required|string|max:255',
            'expense_type' => 'required|string|in:petty_cash,cash_on_hand,other',
            'branch_id' => 'nullable|exists:branches,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.receipt_image' => 'nullable|image|max:2048',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Update the expense claim
            $expenseClaim->update([
                'claim_date' => $validated['claim_date'],
                'title' => $validated['title'],
                'payee' => $validated['payee'],
                'expense_type' => $validated['expense_type'],
                'branch_id' => $validated['branch_id'],
                'bank_account_id' => $validated['bank_account_id'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing items and their receipts
            foreach ($expenseClaim->items as $existingItem) {
                if ($existingItem->receipt_image_path) {
                    Storage::disk('public')->delete($existingItem->receipt_image_path);
                }
            }
            $expenseClaim->items()->delete();

            // Create new items
            foreach ($validated['items'] as $index => $itemData) {
                $receiptPath = null;
                if ($request->hasFile("items.{$index}.receipt_image")) {
                    $receiptPath = $request->file("items.{$index}.receipt_image")->store('receipts', 'public');
                }

                $expenseClaim->items()->create([
                    'description' => $itemData['description'],
                    'category' => $itemData['category'] ?? null,
                    'receipt_image_path' => $receiptPath,
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
        $user = Auth::user();

        // Ensure non-admin users can only delete expense claims from their branch
        if (!($user->role === 'admin' || $user->owner) && $expenseClaim->branch_id !== $user->branch_id) {
            abort(403, 'You can only delete expense claims from your branch.');
        }

        if ($expenseClaim->status !== 'active' && $expenseClaim->status !== 'cancelled') {
            return Redirect::back()->with('error', 'Only active or cancelled expense claims can be deleted.');
        }

        // Delete receipt images from items if they exist
        foreach ($expenseClaim->items as $item) {
            if ($item->receipt_image_path) {
                Storage::disk('public')->delete($item->receipt_image_path);
            }
        }

        // Delete all expense items first
        $expenseClaim->items()->delete();



        $expenseClaim->delete();

        return Redirect::route('expense-claims')->with('success', 'Expense claim deleted successfully.');
    }
}
