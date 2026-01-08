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

        $claims = $query->paginate(100)->appends($request->query());

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
            'totalAmount' => ExpenseClaim::query()
                ->when(!($user->role === 'admin' || $user->owner), function ($query) use ($user) {
                    if ($user->branch_id) {
                        $query->where('branch_id', $user->branch_id);
                    }
                })
                ->whereYear('claim_date', now()->year)
                ->whereMonth('claim_date', now()->month)
                ->sum('total'),
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
            'referenceId' => 'EXP-' . strtoupper(Str::random(7)),
        ]);
    }

    /**
     * Auto-save expense claim as draft
     */
    public function autoSave(Request $request)
    {
        $validated = $request->validate([
            'reference_id' => 'required|string',
            'claim_date' => 'required|date',
            'title' => 'required|string|max:255',
            'expense_type' => 'required|string|in:petty_cash,cash_on_hand,other',
            'branch_id' => 'nullable|exists:branches,id',
            'payee' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.unit_measurement' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $total = collect($validated['items'])->sum(function ($item) {
                return $item['unit_price'] * ($item['quantity'] ?? 1);
            });

            // Check if draft already exists
            $expenseClaim = ExpenseClaim::where('reference_id', $validated['reference_id'])->first();

            if ($expenseClaim) {
                // Update existing draft
                $expenseClaim->update([
                    'claim_date' => $validated['claim_date'],
                    'title' => $validated['title'],
                    'status' => 'draft',
                    'expense_type' => $validated['expense_type'],
                    'branch_id' => $validated['branch_id'],
                    'payee' => $validated['payee'],
                    'notes' => $validated['notes'],
                    'total' => $total,
                ]);

                // Delete existing items
                $expenseClaim->items()->delete();
            } else {
                // Create new draft
                $expenseClaim = ExpenseClaim::create([
                    'user_id' => auth()->id(),
                    'reference_id' => $validated['reference_id'],
                    'claim_date' => $validated['claim_date'],
                    'title' => $validated['title'],
                    'status' => 'draft',
                    'expense_type' => $validated['expense_type'],
                    'branch_id' => $validated['branch_id'],
                    'payee' => $validated['payee'],
                    'notes' => $validated['notes'],
                    'total' => $total,
                ]);
            }

            // Create items
            foreach ($validated['items'] as $item) {
                $expenseClaim->items()->create([
                    'description' => $item['description'],
                    'category' => $item['category'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'unit_measurement' => $item['unit_measurement'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'expense_claim_id' => $expenseClaim->id,
                'message' => 'Draft saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving draft: ' . $e->getMessage()
            ], 500);
        }
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
            'payee' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.receipt_image' => 'nullable|image|max:2048',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.unit_measurement' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
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
                'status' => 'submitted', // Changed to submitted for approval workflow
                'expense_type' => $validated['expense_type'],
                'branch_id' => $validated['branch_id'],
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
                    'unit_measurement' => $item['unit_measurement'] ?? null,
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

        $expenseClaim->load(['user', 'items', 'branch', 'bankAccount', 'payment', 'payment.transaction', 'payment.transaction.bankAccount', 'payment.creator']);

        // Check if user can approve (admin or finance_manager)
        $user = Auth::user();
        $canApprove = ($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'));

        // Load approved_by user if exists
        $approvedBy = null;
        if ($expenseClaim->approved_by) {
            $approvedBy = User::find($expenseClaim->approved_by);
        }

        // Load rejected_by user if exists
        $rejectedBy = null;
        if ($expenseClaim->rejected_by) {
            $rejectedBy = User::find($expenseClaim->rejected_by);
        }

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
                'approved_by' => $approvedBy ? [
                    'id' => $approvedBy->id,
                    'first_name' => $approvedBy->first_name,
                    'last_name' => $approvedBy->last_name,
                ] : null,
                'rejected_by' => $rejectedBy ? [
                    'id' => $rejectedBy->id,
                    'first_name' => $rejectedBy->first_name,
                    'last_name' => $rejectedBy->last_name,
                ] : null,
                'payment' => $expenseClaim->payment ? [
                    'id' => $expenseClaim->payment->id,
                    'amount' => $expenseClaim->payment->amount,
                    'payment_date' => $expenseClaim->payment->payment_date,
                    'payment_method' => $expenseClaim->payment->payment_method,
                    'reference_number' => $expenseClaim->payment->reference_number,
                    'notes' => $expenseClaim->payment->notes,
                    'created_by' => $expenseClaim->payment->creator ? [
                        'id' => $expenseClaim->payment->creator->id,
                        'first_name' => $expenseClaim->payment->creator->first_name,
                        'last_name' => $expenseClaim->payment->creator->last_name,
                    ] : null,
                    'transaction' => $expenseClaim->payment->transaction ? [
                        'id' => $expenseClaim->payment->transaction->id,
                        'reference_number' => $expenseClaim->payment->transaction->reference_number,
                        'amount' => $expenseClaim->payment->transaction->amount,
                        'transaction_date' => $expenseClaim->payment->transaction->transaction_date,
                        'bank_account' => $expenseClaim->payment->transaction->bankAccount ? [
                            'id' => $expenseClaim->payment->transaction->bankAccount->id,
                            'name' => $expenseClaim->payment->transaction->bankAccount->name,
                        ] : null,
                    ] : null,
                    'bank_account' => $expenseClaim->payment->bankAccount ? [
                        'id' => $expenseClaim->payment->bankAccount->id,
                        'name' => $expenseClaim->payment->bankAccount->name,
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
                    'unit_measurement' => $item->unit_measurement,
                    'quantity' => $item->quantity,
                ]),
                'updated_at' => $expenseClaim->updated_at,
                'notes' => $expenseClaim->notes,
                'can_edit' => !$expenseClaim->payment, // Cannot edit if payment exists
                'can_approve' => $canApprove,
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

        // Prevent editing if payment exists
        if ($expenseClaim->payment) {
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('error', 'Expense claim cannot be edited once a payment has been created.');
        }

        if ($expenseClaim->status !== 'submitted' && $expenseClaim->status !== 'draft') {
            return Redirect::route('expense-claims.show', $expenseClaim->id)
                ->with('error', 'Only draft or submitted expense claims can be edited.');
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
                    'unit_measurement' => $item->unit_measurement,
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

        // Prevent editing if payment exists
        if ($expenseClaim->payment) {
            return Redirect::back()->with('error', 'Expense claim cannot be edited once a payment has been created.');
        }

        if ($expenseClaim->status !== 'submitted' && $expenseClaim->status !== 'draft') {
            return Redirect::back()->with('error', 'Only draft or submitted expense claims can be updated.');
        }

        $validated = $request->validate([
            'claim_date' => 'required|date',
            'title' => 'required|string|max:255',
            'payee' => 'required|string|max:255',
            'expense_type' => 'required|string|in:petty_cash,cash_on_hand,other',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.receipt_image' => 'nullable|image|max:2048',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.unit_measurement' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
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
                    'unit_measurement' => $itemData['unit_measurement'] ?? null,
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

        if ($expenseClaim->payment) {
            return Redirect::back()->with('error', 'Expense claim cannot be deleted once a payment has been created.');
        }

        if ($expenseClaim->status === 'paid') {
            return Redirect::back()->with('error', 'Paid expense claims cannot be deleted.');
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

    /**
     * Approve an expense claim
     */
    public function approve(ExpenseClaim $expenseClaim): RedirectResponse
    {
        $user = Auth::user();

        // Check if user can approve (admin or finance_manager)
        if (!($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'))) {
            abort(403, 'You do not have permission to approve expense claims.');
        }

        if ($expenseClaim->status !== 'submitted') {
            return Redirect::back()->with('error', 'Only submitted expense claims can be approved.');
        }

        $expenseClaim->update([
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);

        return Redirect::back()->with('success', 'Expense claim approved successfully.');
    }

    /**
     * Reject an expense claim
     */
    public function reject(Request $request, ExpenseClaim $expenseClaim): RedirectResponse
    {
        $user = Auth::user();

        // Check if user can approve (admin or finance_manager)
        if (!($user->role === 'admin' || $user->owner || $user->hasRoleName('finance_manager'))) {
            abort(403, 'You do not have permission to reject expense claims.');
        }

        if ($expenseClaim->status !== 'submitted') {
            return Redirect::back()->with('error', 'Only submitted expense claims can be rejected.');
        }

        $expenseClaim->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'notes' => ($expenseClaim->notes ?? '') . "\n\nRejection reason: " . ($request->input('rejection_reason') ?? 'No reason provided'),
        ]);

        return Redirect::back()->with('success', 'Expense claim rejected successfully.');
    }
}
