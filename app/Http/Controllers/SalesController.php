<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleCreditItem;
use App\Models\Branch;
use App\Models\Shift;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $query = Sale::query()
            ->with(['branch', 'shift'])
            // Apply branch filtering for non-admin users
            ->when(!($user->role === 'admin' || $user->owner), function ($query) use ($user) {
                if ($user->branch_id) {
                    $query->where('branch_id', $user->branch_id);
                } else {
                    // Non-admin users without a branch should see no sales
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('amount', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('shift', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('branch_id'), function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->input('shift_id'), function ($query, $shiftId) {
                $query->where('shift_id', $shiftId);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->whereDate('sales_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->whereDate('sales_date', '<=', $dateTo);
            })
            ->when($request->input('trashed'), function ($query, $trashed) {
                if ($trashed === 'with') {
                    $query->withTrashed();
                } elseif ($trashed === 'only') {
                    $query->onlyTrashed();
                }
            })
            ->orderBy('sales_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Calculate summary statistics with same filters
        $summaryQuery = clone $query;
        $summaryStats = [
            'total_sales' => $summaryQuery->count(),
            'total_amount' => $summaryQuery->sum('amount'),
            'this_month' => $summaryQuery->whereMonth('sales_date', now()->month)
                ->whereYear('sales_date', now()->year)
                ->sum('amount'),
            'today' => $summaryQuery->whereDate('sales_date', now()->toDateString())
                ->sum('amount'),
        ];

        $sales = $query->paginate(100)->withQueryString()
            ->through(fn($sale) => [
                'id' => $sale->id,
                'sales_date' => $sale->sales_date,
                'amount' => $sale->amount,
                'cashier' => $sale->cashier,
                'closing_manager' => $sale->closing_manager,
                'branch' => $sale->branch ? [
                    'id' => $sale->branch->id,
                    'name' => $sale->branch->name,
                ] : null,
                'shift' => $sale->shift ? [
                    'id' => $sale->shift->id,
                    'name' => $sale->shift->name,
                ] : null,
                'deleted_at' => $sale->deleted_at,
                'created_at' => $sale->created_at,
            ]);

        return Inertia::render('Sales/Index', [
            'filters' => $request->only(['search', 'branch_id', 'shift_id', 'date_from', 'date_to', 'trashed']),
            'sales' => $sales,
            'summary' => $summaryStats,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'shifts' => Shift::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        $user = Auth::user();

        return Inertia::render('Sales/Create', [
            'branches' => ($user->role === 'admin' || $user->owner
                ? Branch::orderBy('name')->get()
                : Branch::where('id', $user->branch_id)->orderBy('name')->get()
            )->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'shifts' => Shift::orderBy('name')->get()->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name
            ]),
            'customers' => Contact::when($user->account_id ?? null, function ($query) use ($user) {
                $query->where('account_id', $user->account_id);
            })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'customer_type' => $c->customer_type,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'sales_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'], // depositable_amount
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_transfer_amount' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_my_nafa' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_aps' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_wave' => ['nullable', 'numeric', 'min:0'],
            'cashier' => ['nullable', 'string', 'max:255'],
            'closing_manager' => ['nullable', 'string', 'max:255'],
            'credit_items' => ['nullable', 'array'],
            'credit_items.*.customer_id' => ['required_with:credit_items', 'exists:contacts,id'],
            'credit_items.*.description' => ['required_with:credit_items', 'string', 'max:255'],
            'credit_items.*.unit_price' => ['required_with:credit_items', 'numeric', 'min:0.01'],
            'credit_items.*.unit_measurement' => ['nullable', 'string', 'max:50'],
            'credit_items.*.quantity' => ['required_with:credit_items', 'numeric', 'min:0.01'],
        ]);

        // Calculate total amount if not provided
        $totalAmount = $validated['total_amount'] ?? 0;
        if (!$totalAmount) {
            $totalAmount = ($validated['cash_amount'] ?? 0) +
                ($validated['bank_transfer_amount'] ?? 0) +
                ($validated['mobile_money_my_nafa'] ?? 0) +
                ($validated['mobile_money_aps'] ?? 0) +
                ($validated['mobile_money_wave'] ?? 0);

            // Add credit items total
            if (isset($validated['credit_items']) && is_array($validated['credit_items'])) {
                foreach ($validated['credit_items'] as $item) {
                    $totalAmount += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
                }
            }
        }

        $creditItems = $validated['credit_items'] ?? [];
        unset($validated['credit_items']);

        DB::transaction(function () use ($validated, $totalAmount, $creditItems) {
            // Create the sale
            $sale = Sale::create([
                ...$validated,
                'total_amount' => $totalAmount,
                'cash_amount' => $validated['cash_amount'] ?? 0,
                'bank_transfer_amount' => $validated['bank_transfer_amount'] ?? 0,
                'mobile_money_my_nafa' => $validated['mobile_money_my_nafa'] ?? 0,
                'mobile_money_aps' => $validated['mobile_money_aps'] ?? 0,
                'mobile_money_wave' => $validated['mobile_money_wave'] ?? 0,
            ]);

            // Create credit items and invoices for customers with positive amounts
            $customerTotals = [];
            foreach ($creditItems as $item) {
                $customerId = $item['customer_id'];
                $itemTotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);

                if ($itemTotal > 0) {
                    // Store credit item
                    $sale->creditItems()->create([
                        'customer_id' => $customerId,
                        'description' => $item['description'],
                        'unit_price' => $item['unit_price'],
                        'unit_measurement' => $item['unit_measurement'] ?? null,
                        'quantity' => $item['quantity'],
                        'total' => $itemTotal,
                    ]);

                    // Accumulate total per customer
                    if (!isset($customerTotals[$customerId])) {
                        $customerTotals[$customerId] = [
                            'customer' => Contact::findOrFail($customerId),
                            'items' => [],
                            'total' => 0,
                        ];
                    }
                    $customerTotals[$customerId]['items'][] = $item;
                    $customerTotals[$customerId]['total'] += $itemTotal;
                }
            }

            // Create invoices for each customer with credit
            foreach ($customerTotals as $customerData) {
                $customer = $customerData['customer'];
                $customerTotal = $customerData['total'];

                if ($customerTotal > 0) {
                    // Generate unique invoice number (within transaction)
                    $invoiceNumber = $this->generateUniqueInvoiceNumberInTransaction();

                    // Create invoice
                    $invoice = Invoice::create([
                        'invoice_number' => $invoiceNumber,
                        'customer_name' => $customer->name,
                        'customer_email' => $customer->email,
                        'customer_phone' => $customer->phone,
                        'customer_address' => $customer->address,
                        'customer_type' => $customer->customer_type ?? 'individual',
                        'invoice_date' => $validated['sales_date'],
                        'due_date' => null,
                        'amount' => $customerTotal,
                        'amount_paid' => 0,
                        'status' => 'draft',
                        'invoice_type' => 'credit_customer',
                        'description' => 'Credit sale from sales transaction',
                        'bank_account_id' => $this->getDefaultBankAccountId(),
                        'branch_id' => $validated['branch_id'],
                        'created_by' => Auth::id(),
                    ]);

                    // Create invoice items
                    foreach ($customerData['items'] as $item) {
                        $invoice->items()->create([
                            'description' => $item['description'],
                            'unit_price' => $item['unit_price'],
                            'unit_measurement' => $item['unit_measurement'] ?? null,
                            'quantity' => $item['quantity'],
                            'total' => ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0),
                        ]);
                    }
                }
            }
        });

        return Redirect::route('sales')->with('success', 'Sale created successfully.');
    }

    public function edit(Sale $sale): Response
    {
        $user = Auth::user();

        // Ensure non-admin users can only edit sales from their branch
        if (!($user->role === 'admin' || $user->owner) && $sale->branch_id !== $user->branch_id) {
            abort(403, 'You can only edit sales from your branch.');
        }

        $sale->load(['creditItems.customer']);

        return Inertia::render('Sales/Edit', [
            'sale' => [
                'id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'shift_id' => $sale->shift_id,
                'sales_date' => $sale->sales_date->format('Y-m-d'),
                'amount' => $sale->amount,
                'total_amount' => $sale->total_amount ?? 0,
                'cash_amount' => $sale->cash_amount ?? 0,
                'bank_transfer_amount' => $sale->bank_transfer_amount ?? 0,
                'mobile_money_my_nafa' => $sale->mobile_money_my_nafa ?? 0,
                'mobile_money_aps' => $sale->mobile_money_aps ?? 0,
                'mobile_money_wave' => $sale->mobile_money_wave ?? 0,
                'cashier' => $sale->cashier,
                'closing_manager' => $sale->closing_manager,
                'branch' => $sale->branch,
                'shift' => $sale->shift,
                'credit_items' => $sale->creditItems->map(fn($item) => [
                    'id' => $item->id,
                    'customer_id' => $item->customer_id,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'unit_measurement' => $item->unit_measurement,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                    'customer' => $item->customer ? [
                        'id' => $item->customer->id,
                        'name' => $item->customer->name,
                    ] : null,
                ]),
                'deleted_at' => $sale->deleted_at,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
            ],
            'branches' => ($user->role === 'admin' || $user->owner
                ? Branch::orderBy('name')->get()
                : Branch::where('id', $user->branch_id)->orderBy('name')->get()
            )->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'shifts' => Shift::orderBy('name')->get()->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name
            ]),
            'customers' => Contact::when($user->account_id ?? null, function ($query) use ($user) {
                $query->where('account_id', $user->account_id);
            })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'address' => $c->address,
                    'customer_type' => $c->customer_type,
                ]),
        ]);
    }

    public function show(Sale $sale): Response
    {
        $user = Auth::user();

        // Ensure non-admin users can only view sales from their branch
        if (!($user->role === 'admin' || $user->owner) && $sale->branch_id !== $user->branch_id) {
            abort(403, 'You can only view sales from your branch.');
        }

        $sale->load(['branch', 'shift', 'creditItems.customer']);

        return Inertia::render('Sales/Show', [
            'sale' => [
                'id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'shift_id' => $sale->shift_id,
                'sales_date' => $sale->sales_date->format('Y-m-d'),
                'amount' => $sale->amount,
                'total_amount' => $sale->total_amount ?? 0,
                'cash_amount' => $sale->cash_amount ?? 0,
                'bank_transfer_amount' => $sale->bank_transfer_amount ?? 0,
                'mobile_money_my_nafa' => $sale->mobile_money_my_nafa ?? 0,
                'mobile_money_aps' => $sale->mobile_money_aps ?? 0,
                'mobile_money_wave' => $sale->mobile_money_wave ?? 0,
                'cashier' => $sale->cashier,
                'closing_manager' => $sale->closing_manager,
                'branch' => $sale->branch ? [
                    'id' => $sale->branch->id,
                    'name' => $sale->branch->name,
                ] : null,
                'shift' => $sale->shift ? [
                    'id' => $sale->shift->id,
                    'name' => $sale->shift->name,
                ] : null,
                'credit_items' => $sale->creditItems->map(fn($item) => [
                    'id' => $item->id,
                    'customer_id' => $item->customer_id,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'unit_measurement' => $item->unit_measurement,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                    'customer' => $item->customer ? [
                        'id' => $item->customer->id,
                        'name' => $item->customer->name,
                    ] : null,
                ]),
                'deleted_at' => $sale->deleted_at,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
            ],
        ]);
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $user = Auth::user();

        // Ensure non-admin users can only update sales from their branch
        if (!($user->role === 'admin' || $user->owner) && $sale->branch_id !== $user->branch_id) {
            abort(403, 'You can only update sales from your branch.');
        }

        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'sales_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'], // depositable_amount
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_transfer_amount' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_my_nafa' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_aps' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_wave' => ['nullable', 'numeric', 'min:0'],
            'cashier' => ['nullable', 'string', 'max:255'],
            'closing_manager' => ['nullable', 'string', 'max:255'],
            'credit_items' => ['nullable', 'array'],
            'credit_items.*.customer_id' => ['required_with:credit_items', 'exists:contacts,id'],
            'credit_items.*.description' => ['required_with:credit_items', 'string', 'max:255'],
            'credit_items.*.unit_price' => ['required_with:credit_items', 'numeric', 'min:0.01'],
            'credit_items.*.unit_measurement' => ['nullable', 'string', 'max:50'],
            'credit_items.*.quantity' => ['required_with:credit_items', 'numeric', 'min:0.01'],
        ]);

        // Calculate total amount if not provided
        $totalAmount = $validated['total_amount'] ?? 0;
        if (!$totalAmount) {
            $totalAmount = ($validated['cash_amount'] ?? 0) +
                ($validated['bank_transfer_amount'] ?? 0) +
                ($validated['mobile_money_my_nafa'] ?? 0) +
                ($validated['mobile_money_aps'] ?? 0) +
                ($validated['mobile_money_wave'] ?? 0);

            // Add credit items total
            if (isset($validated['credit_items']) && is_array($validated['credit_items'])) {
                foreach ($validated['credit_items'] as $item) {
                    $totalAmount += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
                }
            }
        }

        $creditItems = $validated['credit_items'] ?? [];
        unset($validated['credit_items']);

        DB::transaction(function () use ($sale, $validated, $totalAmount, $creditItems) {
            // Update the sale
            $sale->update([
                ...$validated,
                'total_amount' => $totalAmount,
                'cash_amount' => $validated['cash_amount'] ?? 0,
                'bank_transfer_amount' => $validated['bank_transfer_amount'] ?? 0,
                'mobile_money_my_nafa' => $validated['mobile_money_my_nafa'] ?? 0,
                'mobile_money_aps' => $validated['mobile_money_aps'] ?? 0,
                'mobile_money_wave' => $validated['mobile_money_wave'] ?? 0,
            ]);

            // Delete existing credit items
            $sale->creditItems()->delete();

            // Create new credit items
            foreach ($creditItems as $item) {
                $itemTotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
                if ($itemTotal > 0) {
                    $sale->creditItems()->create([
                        'customer_id' => $item['customer_id'],
                        'description' => $item['description'],
                        'unit_price' => $item['unit_price'],
                        'unit_measurement' => $item['unit_measurement'] ?? null,
                        'quantity' => $item['quantity'],
                        'total' => $itemTotal,
                    ]);
                }
            }
        });

        return Redirect::route('sales')->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $user = Auth::user();

        // Ensure non-admin users can only delete sales from their branch
        if (!($user->role === 'admin' || $user->owner) && $sale->branch_id !== $user->branch_id) {
            abort(403, 'You can only delete sales from your branch.');
        }

        $sale->delete();

        return Redirect::route('sales')->with('success', 'Sale deleted successfully.');
    }

    public function restore(Sale $sale): RedirectResponse
    {
        $user = Auth::user();

        // Ensure non-admin users can only restore sales from their branch
        if (!($user->role === 'admin' || $user->owner) && $sale->branch_id !== $user->branch_id) {
            abort(403, 'You can only restore sales from your branch.');
        }

        $sale->restore();

        return Redirect::route('sales')->with('success', 'Sale restored successfully.');
    }

    /**
     * Generate a unique invoice number (must be called within a transaction)
     */
    private function generateUniqueInvoiceNumberInTransaction(): string
    {
        $year = date('Y');

        // Lock the table for consistent reads (include soft-deleted invoices)
        $lastInvoice = Invoice::withTrashed()
            ->where('invoice_number', 'like', "INV-{$year}-%")
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED) DESC')
            ->first();

        $nextSequence = 1;
        if ($lastInvoice) {
            preg_match('/INV-' . $year . '-(\d+)/', $lastInvoice->invoice_number, $matches);
            if (isset($matches[1])) {
                $nextSequence = (int)$matches[1] + 1;
            }
        }

        return 'INV-' . $year . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get default bank account ID (first active account)
     */
    private function getDefaultBankAccountId(): int
    {
        $bankAccount = BankAccount::where('active', true)->first();
        return $bankAccount ? $bankAccount->id : 1; // Fallback to 1 if no active account
    }
}
