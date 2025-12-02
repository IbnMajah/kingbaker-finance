<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Deposit;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DepositController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Deposit::query()
            ->with(['bankAccount', 'branch', 'creator'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('bankAccount', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('deposit_type'), function ($query, $type) {
                $query->where('deposit_type', $type);
            })
            ->when($request->input('bank_account_id'), function ($query, $bankAccountId) {
                $query->where('bank_account_id', $bankAccountId);
            })
            ->when($request->input('branch_id'), function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->whereDate('deposit_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->whereDate('deposit_date', '<=', $dateTo);
            })
            ->orderBy('deposit_date', 'desc');

        // Calculate summary statistics with same filters
        $summaryQuery = clone $query;
        $summaryStats = [
            'total_deposits' => $summaryQuery->count(),
            'total_amount' => $summaryQuery->sum('amount'),
            'this_month' => $summaryQuery->whereMonth('deposit_date', now()->month)
                ->whereYear('deposit_date', now()->year)
                ->sum('amount'),
            'today' => $summaryQuery->whereDate('deposit_date', now()->toDateString())
                ->sum('amount'),
        ];

        $deposits = $query->paginate(100)->withQueryString()
            ->through(fn($deposit) => [
                'id' => $deposit->id,
                'deposit_date' => $deposit->deposit_date,
                'description' => $deposit->description,
                'amount' => $deposit->amount,
                'deposit_type' => $deposit->deposit_type,
                'reference_number' => $deposit->reference_number,
                'bank_account' => $deposit->bankAccount ? [
                    'id' => $deposit->bankAccount->id,
                    'name' => $deposit->bankAccount->name,
                ] : null,
                'branch' => $deposit->branch ? [
                    'id' => $deposit->branch->id,
                    'name' => $deposit->branch->name,
                ] : null,

                'creator' => $deposit->creator ? [
                    'id' => $deposit->creator->id,
                    'name' => $deposit->creator->first_name . ' ' . $deposit->creator->last_name,
                ] : null,
                'image_path' => $deposit->image_path,
                'created_at' => $deposit->created_at,
            ]);

        return Inertia::render('Deposits/Index', [
            'filters' => $request->only(['search', 'deposit_type', 'bank_account_id', 'branch_id', 'date_from', 'date_to']),
            'deposits' => $deposits,
            'summary' => $summaryStats,
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'depositTypes' => [
                ['value' => 'cash_deposit', 'label' => 'Cash Deposit'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'nafa_deposit', 'label' => 'NAFA Deposit'],
                ['value' => 'wave_deposit', 'label' => 'Wave Deposit'],
                ['value' => 'cheque_deposit', 'label' => 'Cheque Deposit'],
                ['value' => 'daily_sales_deposit', 'label' => 'Daily Sales Deposit'],
            ],
        ]);
    }

    public function create(): Response
    {
        // Get all sales for frontend filtering
        $availableSales = Sale::with(['branch', 'shift'])
            ->orderBy('sales_date', 'desc')
            ->get()
            ->map(fn($sale) => [
                'id' => $sale->id,
                'sales_date' => $sale->sales_date->format('Y-m-d'),
                'amount' => $sale->amount,
                'cashier' => $sale->cashier,
                'branch' => $sale->branch?->name,
                'shift' => $sale->shift?->name,
                'is_deposited' => $sale->deposits()->exists(),
            ]);

        return Inertia::render('Deposits/Create', [
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),

            'availableSales' => $availableSales,
            'depositTypes' => [
                ['value' => 'cash_deposit', 'label' => 'Cash Deposit'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'nafa_deposit', 'label' => 'NAFA Deposit'],
                ['value' => 'wave_deposit', 'label' => 'Wave Deposit'],
                ['value' => 'cheque_deposit', 'label' => 'Cheque Deposit'],
                ['value' => 'daily_sales_deposit', 'label' => 'Daily Sales Deposit'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'deposit_date' => 'required|date',
            'deposit_type' => 'required|in:cash_deposit,bank_transfer,nafa_deposit,wave_deposit,cheque_deposit,daily_sales_deposit',
            'amount' => 'required|numeric|min:0.01',
            'branch_id' => 'nullable|exists:branches,id',

            'reference_number' => 'required|string|max:255',
            'depositor_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|mimes:jpeg,jpg,png,pdf|max:5120',
            'selected_sales' => 'nullable|array',
            'selected_sales.*' => 'exists:sales,id',
        ]);

        // If sales are selected, calculate total amount from selected sales
        if (!empty($validated['selected_sales'])) {
            $selectedSales = Sale::whereIn('id', $validated['selected_sales'])->get();
            $validated['amount'] = $selectedSales->sum('amount');
        }

        // Handle file uploads separately
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('deposits', 'public');
        }

        // Handle multiple attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('deposits/attachments', 'public');
                $attachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        // Remove non-database fields from validated data
        $selectedSalesIds = $validated['selected_sales'] ?? [];
        unset($validated['image'], $validated['attachments'], $validated['selected_sales']);

        $deposit = Deposit::create([
            ...$validated,
            'image_path' => $imagePath,
            'attachments' => $attachments,
            'created_by' => Auth::id(),
        ]);

        // Attach selected sales to the deposit
        if (!empty($selectedSalesIds)) {
            $deposit->sales()->attach($selectedSalesIds);
        }

        // Create corresponding transaction if bank account exists
        if ($deposit->bankAccount) {
            $deposit->bankAccount->transactions()->create([
                'transaction_date' => $deposit->deposit_date,
                'type' => 'credit',
                'payment_mode' => $this->mapDepositTypeToPaymentMode($deposit->deposit_type),
                'reference_number' => $deposit->reference_number,
                'amount' => $deposit->amount,
                'description' => 'Deposit of ' . $deposit->reference_number . ' to ' . $deposit->bankAccount->name,
                'branch_id' => $deposit->branch_id,
                'category' => 'deposit',
                'image_path' => $deposit->image_path,
                'created_by' => $deposit->created_by,
            ]);
        }

        return Redirect::route('deposits')->with('success', 'Deposit recorded successfully.');
    }

    public function show(Deposit $deposit): Response
    {
        return Inertia::render('Deposits/Show', [
            'deposit' => $deposit->load(['bankAccount', 'branch', 'sales.branch']),
        ]);
    }

    public function edit(Deposit $deposit): Response
    {
        // Get current sales associated with this deposit
        $currentSales = $deposit->sales()->with(['branch', 'shift'])->get()->map(fn($sale) => [
            'id' => $sale->id,
            'sales_date' => $sale->sales_date->format('Y-m-d'),
            'amount' => $sale->amount,
            'cashier' => $sale->cashier,
            'branch' => $sale->branch?->name,
            'shift' => $sale->shift?->name,
        ]);

        // Get all sales for frontend filtering
        $availableSales = Sale::with(['branch', 'shift'])
            ->orderBy('sales_date', 'desc')
            ->get()
            ->map(fn($sale) => [
                'id' => $sale->id,
                'sales_date' => $sale->sales_date->format('Y-m-d'),
                'amount' => $sale->amount,
                'cashier' => $sale->cashier,
                'branch' => $sale->branch?->name,
                'shift' => $sale->shift?->name,
                'is_deposited' => $sale->deposits()->exists(),
                'current_deposit_id' => $sale->deposits()->where('deposit_id', $deposit->id)->exists() ? $deposit->id : null,
            ]);

        return Inertia::render('Deposits/Edit', [
            'deposit' => $deposit->load(['bankAccount', 'branch']),
            'currentSales' => $currentSales,
            'availableSales' => $availableSales,
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),

            'depositTypes' => [
                ['value' => 'cash_deposit', 'label' => 'Cash Deposit'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'nafa_deposit', 'label' => 'NAFA Deposit'],
                ['value' => 'wave_deposit', 'label' => 'Wave Deposit'],
                ['value' => 'cheque_deposit', 'label' => 'Cheque Deposit'],
                ['value' => 'daily_sales_deposit', 'label' => 'Daily Sales Deposit'],
            ],
        ]);
    }

    public function update(Request $request, Deposit $deposit): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'deposit_date' => 'required|date',
            'deposit_type' => 'required|in:cash_deposit,bank_transfer,nafa_deposit,wave_deposit,cheque_deposit,daily_sales_deposit',
            'amount' => 'required|numeric|min:0.01',
            'branch_id' => 'nullable|exists:branches,id',

            'reference_number' => 'required|string|max:255',
            'depositor_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|mimes:jpeg,jpg,png,pdf|max:5120',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'string',
            'sales_to_add' => 'nullable|array',
            'sales_to_add.*' => 'exists:sales,id',
            'sales_to_remove' => 'nullable|array',
            'sales_to_remove.*' => 'exists:sales,id',
        ]);

        // Handle file uploads separately
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($deposit->image_path) {
                Storage::disk('public')->delete($deposit->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('deposits', 'public');
        }

        // Handle multiple attachments
        $currentAttachments = $deposit->attachments ?? [];

        // Remove selected attachments
        if (!empty($validated['remove_attachments'])) {
            foreach ($validated['remove_attachments'] as $pathToRemove) {
                // Delete file from storage
                Storage::disk('public')->delete($pathToRemove);
                // Remove from attachments array
                $currentAttachments = array_filter($currentAttachments, function ($attachment) use ($pathToRemove) {
                    return $attachment['path'] !== $pathToRemove;
                });
            }
        }

        // Add new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('deposits/attachments', 'public');
                $currentAttachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $validated['attachments'] = array_values($currentAttachments); // Re-index array

        // Handle sales relationship changes
        $salesToAdd = $validated['sales_to_add'] ?? [];
        $salesToRemove = $validated['sales_to_remove'] ?? [];

        // Remove non-database fields from validated data
        unset($validated['image'], $validated['remove_attachments'], $validated['sales_to_add'], $validated['sales_to_remove']);

        // If sales mode and sales are being modified, recalculate amount
        if ($validated['deposit_type'] === 'daily_sales_deposit' && (!empty($salesToAdd) || !empty($salesToRemove))) {
            // Get current sales associated with this deposit
            $currentSalesIds = $deposit->sales()->pluck('sales.id')->toArray();

            // Calculate new sales IDs after adding and removing
            $newSalesIds = array_diff($currentSalesIds, $salesToRemove);
            $newSalesIds = array_unique(array_merge($newSalesIds, $salesToAdd));

            // Calculate total amount from new sales selection
            if (!empty($newSalesIds)) {
                $totalAmount = Sale::whereIn('id', $newSalesIds)->sum('amount');
                $validated['amount'] = $totalAmount;
            }
        }

        $deposit->update($validated);

        // Update sales relationships
        if (!empty($salesToRemove)) {
            $deposit->sales()->detach($salesToRemove);
        }

        if (!empty($salesToAdd)) {
            $deposit->sales()->attach($salesToAdd);
        }

        // Update corresponding transaction if bank account exists
        if ($deposit->bankAccount) {
            $transaction = $deposit->bankAccount->transactions()
                ->where('category', 'deposit')
                ->where('created_at', $deposit->created_at)
                ->first();

            if ($transaction) {
                $transaction->update([
                    'transaction_date' => $deposit->deposit_date,
                    'payment_mode' => $this->mapDepositTypeToPaymentMode($deposit->deposit_type),
                    'reference_number' => $deposit->reference_number,
                    'amount' => $deposit->amount,
                    'description' => $deposit->description,
                    'branch_id' => $deposit->branch_id,
                    'image_path' => $deposit->image_path,
                ]);
            }
        }

        return Redirect::route('deposits')->with('success', 'Deposit updated successfully.');
    }

    public function destroy(Deposit $deposit): RedirectResponse
    {
        // Delete associated image if exists
        if ($deposit->image_path) {
            Storage::disk('public')->delete($deposit->image_path);
        }

        // Delete all attachments
        if ($deposit->attachments) {
            foreach ($deposit->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        // Delete corresponding transaction if bank account exists
        if ($deposit->bankAccount) {
            $transaction = $deposit->bankAccount->transactions()
                ->where('category', 'deposit')
                ->where('created_at', $deposit->created_at)
                ->first();

            if ($transaction) {
                $transaction->delete();
            }
        }

        $deposit->delete();

        return Redirect::route('deposits')->with('success', 'Deposit deleted successfully.');
    }

    /**
     * Map deposit type to transaction payment mode
     */
    private function mapDepositTypeToPaymentMode(string $depositType): string
    {
        $mapping = [
            'cash_deposit' => 'cash',
            'bank_transfer' => 'bank_transfer',
            'nafa_deposit' => 'nafa',
            'wave_deposit' => 'wave',
            'cheque_deposit' => 'cheque',
            'daily_sales_deposit' => 'sales',
        ];

        return $mapping[$depositType] ?? 'other';
    }
}
