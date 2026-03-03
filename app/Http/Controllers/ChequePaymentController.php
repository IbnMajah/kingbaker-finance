<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillPayment;
use App\Models\Branch;
use App\Models\ChequePayment;
use App\Models\PaymentCategory;
use App\Models\Transaction;
use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ChequePaymentController extends Controller
{
    /**
     * Create reverse transactions for a cancelled cheque payment
     */
    private function createReverseTransactions(ChequePayment $chequePayment): void
    {
        // Find related transactions for this cheque payment
        // Transactions can be linked via reference_number matching payment_number or reference_number
        $transactions = Transaction::where('bank_account_id', $chequePayment->bank_account_id)
            ->where('amount', $chequePayment->amount)
            ->where(function ($query) use ($chequePayment) {
                $query->where('reference_number', $chequePayment->payment_number)
                    ->orWhere('reference_number', $chequePayment->reference_number)
                    ->orWhere('description', 'like', '%' . $chequePayment->payment_number . '%');
            })
            ->where('type', 'debit')
            ->where('deleted_at', null)
            ->get();

        // Create reverse transactions for each found transaction
        foreach ($transactions as $transaction) {
            // Check if a reverse transaction already exists
            $existingReverse = Transaction::where('bank_account_id', $transaction->bank_account_id)
                ->where('amount', $transaction->amount)
                ->where('type', 'credit')
                ->where('reference_number', 'REV-' . ($transaction->reference_number ?? $chequePayment->payment_number))
                ->whereNull('deleted_at')
                ->first();

            if (!$existingReverse) {
                Transaction::create([
                    'bank_account_id' => $transaction->bank_account_id,
                    'transaction_date' => now()->toDateString(),
                    'type' => 'credit', // Reverse transaction is a credit
                    'payment_mode' => $transaction->payment_mode,
                    'reference_number' => 'REV-' . ($transaction->reference_number ?? $chequePayment->payment_number),
                    'payee' => $chequePayment->payee,
                    'amount' => $transaction->amount,
                    'description' => 'Reversal: ' . $chequePayment->payment_number . ' - ' . $chequePayment->description,
                    'category' => $chequePayment->payment_category . '_reversal',
                    'branch_id' => $chequePayment->branch_id,
                    'created_by' => Auth::id(),
                ]);
            }
        }

        // Also check if this is a bill payment and handle BillPayment transactions
        if ($chequePayment->bill_id) {
            $billPayment = BillPayment::where('bill_id', $chequePayment->bill_id)
                ->where('bank_account_id', $chequePayment->bank_account_id)
                ->where('amount', $chequePayment->amount)
                ->whereHas('transaction', function ($query) {
                    $query->where('deleted_at', null);
                })
                ->with('transaction')
                ->first();

            if ($billPayment && $billPayment->transaction) {
                $transaction = $billPayment->transaction;

                // Check if reverse transaction already exists
                $existingReverse = Transaction::where('bank_account_id', $transaction->bank_account_id)
                    ->where('amount', $transaction->amount)
                    ->where('type', 'credit')
                    ->where('reference_number', 'like', 'REV-%' . ($transaction->reference_number ?? ''))
                    ->where('description', 'like', '%Reversal: ' . $chequePayment->payment_number . '%')
                    ->where('deleted_at', null)
                    ->first();

                if (!$existingReverse) {
                    Transaction::create([
                        'bank_account_id' => $transaction->bank_account_id,
                        'transaction_date' => now()->toDateString(),
                        'type' => 'credit',
                        'payment_mode' => $transaction->payment_mode,
                        'reference_number' => 'REV-' . ($transaction->reference_number ?? $chequePayment->payment_number),
                        'payee' => $chequePayment->payee,
                        'amount' => $transaction->amount,
                        'description' => 'Reversal: ' . $chequePayment->payment_number . ' - ' . $chequePayment->description,
                        'category' => 'vendor_payment_reversal',
                        'branch_id' => $chequePayment->branch_id,
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = ChequePayment::query()
            ->with(['bankAccount', 'branch', 'creator', 'vendor', 'bill'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('payee', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('cheque_number', 'like', "%{$search}%")
                        ->orWhere('payment_number', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhereHas('vendor', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('bankAccount', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('payment_category'), function ($query, $category) {
                $query->where('payment_category', $category);
            })
            ->when($request->input('payment_mode'), function ($query, $mode) {
                $query->where('payment_mode', $mode);
            })
            ->when($request->input('bank_account_id'), function ($query, $bankAccountId) {
                $query->where('bank_account_id', $bankAccountId);
            })
            ->when($request->input('branch_id'), function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->whereDate('payment_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->whereDate('payment_date', '<=', $dateTo);
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('payment_date', 'desc');

        // Calculate summary statistics with same filters
        $summaryQuery = clone $query;
        $summaryStats = [
            'total_payments' => $summaryQuery->count(),
            'total_amount' => $summaryQuery->sum('amount'),
            'this_month' => $summaryQuery->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'today' => $summaryQuery->whereDate('payment_date', today())->sum('amount'),
        ];

        $payments = $query->paginate(100)->withQueryString()
            ->through(fn($payment) => [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'payee' => $payment->payee,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date,
                'payment_category' => $payment->payment_category,
                'payment_mode' => $payment->payment_mode,
                'cheque_number' => $payment->cheque_number,
                'reference_number' => $payment->reference_number,
                'description' => $payment->description,
                'status' => $payment->status,
                'bank_account' => $payment->bankAccount ? [
                    'id' => $payment->bankAccount->id,
                    'name' => $payment->bankAccount->name,
                ] : null,
                'branch' => $payment->branch ? [
                    'id' => $payment->branch->id,
                    'name' => $payment->branch->name,
                ] : null,
                'creator' => $payment->creator ? [
                    'id' => $payment->creator->id,
                    'name' => $payment->creator->first_name . ' ' . $payment->creator->last_name,
                ] : null,
                'vendor' => $payment->vendor ? [
                    'id' => $payment->vendor->id,
                    'name' => $payment->vendor->name,
                ] : null,
                'bill' => $payment->bill ? [
                    'id' => $payment->bill->id,
                    'reference' => $payment->bill->reference ?: "BILL-{$payment->bill->id}",
                    'amount' => $payment->bill->amount,
                ] : null,
                'recurring_frequency' => $payment->recurring_frequency,
                'created_at' => $payment->created_at,
            ]);

        return Inertia::render('ChequePayments/Index', [
            'filters' => $request->only(['search', 'payment_category', 'payment_mode', 'bank_account_id', 'branch_id', 'date_from', 'date_to', 'status']),
            'payments' => $payments,
            'summary' => $summaryStats,
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'paymentCategories' => PaymentCategory::orderBy('label')->get()->map(fn($cat) => ['value' => $cat->value, 'label' => $cat->label, 'description' => $cat->description, 'description_placeholder' => $cat->description_placeholder]),
            'paymentModes' => [
                ['value' => 'cheque', 'label' => 'Cheque'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'nafa', 'label' => 'NAFA'],
                ['value' => 'wave', 'label' => 'Wave'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'statuses' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'rejected', 'label' => 'Rejected'],
                ['value' => 'issued', 'label' => 'Issued'],
                ['value' => 'cleared', 'label' => 'Cleared'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ChequePayments/Create', [
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'vendors' => Vendor::where('active', true)->orderBy('name')->get()->map(fn($v) => [
                'value' => $v->id,
                'label' => $v->name
            ]),
            'paymentCategories' => PaymentCategory::orderBy('label')->get()->map(fn($cat) => ['value' => $cat->value, 'label' => $cat->label, 'description' => $cat->description, 'description_placeholder' => $cat->description_placeholder]),
            'paymentModes' => [
                ['value' => 'cheque', 'label' => 'Cheque'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'nafa', 'label' => 'NAFA'],
                ['value' => 'wave', 'label' => 'Wave'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'recurringFrequencies' => [
                ['value' => 'daily', 'label' => 'Daily'],
                ['value' => 'weekly', 'label' => 'Weekly'],
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'quarterly', 'label' => 'Quarterly'],
                ['value' => 'annually', 'label' => 'Annually'],
            ],
            'billTypes' => [
                ['value' => 'inventory', 'label' => 'Inventory'],
                ['value' => 'utility', 'label' => 'Utility'],
                ['value' => 'service', 'label' => 'Service'],
                ['value' => 'recurring', 'label' => 'Recurring'],
                ['value' => 'miscellaneous', 'label' => 'Miscellaneous'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'payee' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_category' => ['required', Rule::exists('payment_categories', 'value')],
            'payment_mode' => 'required|in:cheque,bank_transfer,cash,nafa,wave,other',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,quarterly,annually|required_if:is_recurring,true',
            'cheque_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'required|string',
            'notes' => 'nullable|string',
            'receipt_image' => 'nullable|image|max:2048',
        ];

        if ($request->input('payment_category') === 'bill') {
            $rules['bill_type'] = 'required|in:inventory,utility,service,recurring,miscellaneous';
            $rules['bill_number'] = 'nullable|string|max:255';
            $rules['due_date'] = 'nullable|date';
            $rules['bill_image'] = 'nullable|image|max:2048';
            $rules['vendor_id'] = 'nullable|exists:vendors,id';

            if ($request->input('bill_type') === 'inventory') {
                $rules['items'] = 'required|array|min:1';
                $rules['items.*.description'] = 'required|string|max:255';
                $rules['items.*.unit_price'] = 'required|numeric|min:0';
                $rules['items.*.quantity'] = 'required|numeric|min:0.01';
                $rules['items.*.unit_measurement'] = 'nullable|string|max:50';
            }
        }

        $validated = $request->validate($rules);

        $year = date('Y');
        $count = ChequePayment::whereYear('created_at', $year)->count() + 1;
        $paymentNumber = 'PAY-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('payments', 'public');
        }

        DB::beginTransaction();
        try {
            $billId = null;

            // If this is a bill payment, create a Bill record first
            if ($validated['payment_category'] === 'bill') {
                $billImagePath = null;
                if ($request->hasFile('bill_image')) {
                    $billImagePath = $request->file('bill_image')->store('bills', 'public');
                }

                $billAmount = $validated['amount'];
                if (($validated['bill_type'] ?? null) === 'inventory' && !empty($validated['items'])) {
                    $billAmount = collect($validated['items'])->sum(fn($item) => $item['unit_price'] * $item['quantity']);
                }

                $bill = Bill::create([
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'bill_number' => $validated['bill_number'] ?? null,
                    'bill_date' => $validated['payment_date'],
                    'due_date' => $validated['due_date'] ?? null,
                    'amount' => $billAmount,
                    'amount_paid' => 0,
                    'status' => 'pending',
                    'bill_type' => $validated['bill_type'],
                    'description' => $validated['description'],
                    'recurring_frequency' => ($validated['is_recurring'] ?? false) ? ($validated['recurring_frequency'] ?? null) : null,
                    'image_path' => $billImagePath,
                    'created_by' => Auth::id(),
                ]);

                if (($validated['bill_type'] ?? null) === 'inventory' && !empty($validated['items'])) {
                    foreach ($validated['items'] as $item) {
                        BillItem::create([
                            'bill_id' => $bill->id,
                            'description' => $item['description'],
                            'unit_price' => $item['unit_price'],
                            'unit_measurement' => $item['unit_measurement'] ?? null,
                            'quantity' => $item['quantity'],
                            'total' => $item['unit_price'] * $item['quantity'],
                        ]);
                    }
                }

                $billId = $bill->id;
                $validated['amount'] = $billAmount;
            }

            $payment = ChequePayment::create([
                'payee' => $validated['payee'],
                'phone_number' => $validated['phone_number'] ?? null,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_category' => $validated['payment_category'],
                'payment_mode' => $validated['payment_mode'],
                'bank_account_id' => $validated['bank_account_id'],
                'branch_id' => $validated['branch_id'] ?? null,
                'vendor_id' => $validated['vendor_id'] ?? null,
                'bill_id' => $billId,
                'is_recurring' => $validated['is_recurring'] ?? false,
                'recurring_frequency' => $validated['recurring_frequency'] ?? null,
                'cheque_number' => $validated['cheque_number'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'description' => $validated['description'],
                'notes' => $validated['notes'] ?? null,
                'payment_number' => $paymentNumber,
                'status' => 'pending',
                'receipt_image_path' => $imagePath,
                'created_by' => Auth::id(),
            ]);

            if ($validated['payment_category'] === 'bill' && $billId) {
                BillPayment::create([
                    'bill_id' => $billId,
                    'bank_account_id' => $validated['bank_account_id'],
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_mode'],
                    'reference_number' => $validated['reference_number'] ?: $paymentNumber,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);
            } else {
                Transaction::create([
                    'bank_account_id' => $validated['bank_account_id'],
                    'transaction_date' => $validated['payment_date'],
                    'type' => 'debit',
                    'payment_mode' => $validated['payment_mode'],
                    'reference_number' => $validated['reference_number'] ?: $paymentNumber,
                    'payee' => $validated['payee'],
                    'amount' => $validated['amount'],
                    'description' => $validated['description'],
                    'branch_id' => $validated['branch_id'] ?? null,
                    'category' => $validated['payment_category'],
                    'image_path' => $imagePath,
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while processing the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('cheque-payments')->with('success', 'Payment created successfully. Awaiting approval.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChequePayment $chequePayment): Response
    {
        $chequePayment->load(['bankAccount', 'branch', 'creator', 'approver', 'rejector', 'vendor', 'bill.items']);

        return Inertia::render('ChequePayments/Show', [
            'payment' => $chequePayment,
            'paymentCategories' => PaymentCategory::orderBy('label')->get()->map(fn($cat) => ['value' => $cat->value, 'label' => $cat->label, 'description' => $cat->description, 'description_placeholder' => $cat->description_placeholder]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChequePayment $chequePayment): Response|RedirectResponse
    {
        if (in_array($chequePayment->status, ['cleared', 'rejected'])) {
            return Redirect::back()->with('error', 'Cannot edit a payment that has been ' . $chequePayment->status . '.');
        }

        return Inertia::render('ChequePayments/Edit', [
            'payment' => $chequePayment->load(['bankAccount', 'branch', 'vendor', 'bill.items']),
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'vendors' => Vendor::where('active', true)->orderBy('name')->get()->map(fn($v) => [
                'value' => $v->id,
                'label' => $v->name
            ]),
            'paymentCategories' => PaymentCategory::orderBy('label')->get()->map(fn($cat) => ['value' => $cat->value, 'label' => $cat->label, 'description' => $cat->description, 'description_placeholder' => $cat->description_placeholder]),
            'paymentModes' => [
                ['value' => 'cheque', 'label' => 'Cheque'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'nafa', 'label' => 'NAFA'],
                ['value' => 'wave', 'label' => 'Wave'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'recurringFrequencies' => [
                ['value' => 'daily', 'label' => 'Daily'],
                ['value' => 'weekly', 'label' => 'Weekly'],
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'quarterly', 'label' => 'Quarterly'],
                ['value' => 'annually', 'label' => 'Annually'],
            ],
            'billTypes' => [
                ['value' => 'inventory', 'label' => 'Inventory'],
                ['value' => 'utility', 'label' => 'Utility'],
                ['value' => 'service', 'label' => 'Service'],
                ['value' => 'recurring', 'label' => 'Recurring'],
                ['value' => 'miscellaneous', 'label' => 'Miscellaneous'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChequePayment $chequePayment): RedirectResponse
    {
        if (in_array($chequePayment->status, ['cleared', 'rejected'])) {
            return Redirect::back()->with('error', 'Cannot edit a payment that has been ' . $chequePayment->status . '.');
        }

        $rules = [
            'payee' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_category' => ['required', Rule::exists('payment_categories', 'value')],
            'payment_mode' => 'required|in:cheque,bank_transfer,cash,nafa,wave,other',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,quarterly,annually|required_if:is_recurring,true',
            'cheque_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'required|string',
            'notes' => 'nullable|string',
            'receipt_image' => 'nullable|image|max:2048',
        ];

        if ($request->input('payment_category') === 'bill') {
            $rules['bill_type'] = 'required|in:inventory,utility,service,recurring,miscellaneous';
            $rules['bill_number'] = 'nullable|string|max:255';
            $rules['due_date'] = 'nullable|date';
            $rules['bill_image'] = 'nullable|image|max:2048';

            if ($request->input('bill_type') === 'inventory') {
                $rules['items'] = 'required|array|min:1';
                $rules['items.*.description'] = 'required|string|max:255';
                $rules['items.*.unit_price'] = 'required|numeric|min:0';
                $rules['items.*.quantity'] = 'required|numeric|min:0.01';
                $rules['items.*.unit_measurement'] = 'nullable|string|max:50';
            }
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('receipt_image')) {
            if ($chequePayment->receipt_image_path) {
                Storage::disk('public')->delete($chequePayment->receipt_image_path);
            }
            $validated['receipt_image_path'] = $request->file('receipt_image')->store('payments', 'public');
        }

        unset($validated['receipt_image']);

        DB::beginTransaction();
        try {
            // Handle bill update if category is bill
            if ($validated['payment_category'] === 'bill') {
                $billData = [
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'bill_number' => $validated['bill_number'] ?? null,
                    'bill_date' => $validated['payment_date'],
                    'due_date' => $validated['due_date'] ?? null,
                    'bill_type' => $validated['bill_type'],
                    'description' => $validated['description'],
                    'recurring_frequency' => ($validated['is_recurring'] ?? false) ? ($validated['recurring_frequency'] ?? null) : null,
                ];

                if ($request->hasFile('bill_image')) {
                    $billData['image_path'] = $request->file('bill_image')->store('bills', 'public');
                }

                $billAmount = $validated['amount'];
                if (($validated['bill_type'] ?? null) === 'inventory' && !empty($validated['items'])) {
                    $billAmount = collect($validated['items'])->sum(fn($item) => $item['unit_price'] * $item['quantity']);
                }
                $billData['amount'] = $billAmount;
                $validated['amount'] = $billAmount;

                if ($chequePayment->bill_id && $chequePayment->bill) {
                    $chequePayment->bill->update($billData);

                    if (($validated['bill_type'] ?? null) === 'inventory' && !empty($validated['items'])) {
                        $chequePayment->bill->items()->delete();
                        foreach ($validated['items'] as $item) {
                            BillItem::create([
                                'bill_id' => $chequePayment->bill_id,
                                'description' => $item['description'],
                                'unit_price' => $item['unit_price'],
                                'unit_measurement' => $item['unit_measurement'] ?? null,
                                'quantity' => $item['quantity'],
                                'total' => $item['unit_price'] * $item['quantity'],
                            ]);
                        }
                    }
                }

                unset($validated['bill_type'], $validated['bill_number'], $validated['due_date'], $validated['bill_image'], $validated['items']);
            }

            $chequePayment->update($validated);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while updating the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('cheque-payments')->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChequePayment $chequePayment): RedirectResponse
    {
        if (in_array($chequePayment->status, ['cleared'])) {
            return Redirect::back()->with('error', 'Cannot delete a payment that has been ' . $chequePayment->status . '. You can only cancel or reverse it.');
        }

        DB::beginTransaction();
        try {
            // Create reverse transactions if there are any related transactions
            // This must be done BEFORE deleting the payment
            $this->createReverseTransactions($chequePayment);

            // Delete associated image if exists
            if ($chequePayment->receipt_image_path) {
                Storage::disk('public')->delete($chequePayment->receipt_image_path);
            }

            $chequePayment->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while deleting the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::route('cheque-payments')->with('success', 'Payment deleted successfully. Reverse transactions have been created to credit the account(s).');
    }

    public function approve(Request $request, ChequePayment $chequePayment): RedirectResponse
    {
        if ($chequePayment->status !== 'pending') {
            return Redirect::back()->with('error', 'Only pending payments can be approved.');
        }

        $user = Auth::user();

        if (!$user->hasPermission('approve_cheque_payments')) {
            return Redirect::back()->with('error', 'You do not have permission to approve payments.');
        }

        if ($chequePayment->created_by === $user->id) {
            return Redirect::back()->with('error', 'You cannot approve a payment you created.');
        }

        $chequePayment->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return Redirect::back()->with('success', 'Payment approved successfully.');
    }

    public function reject(Request $request, ChequePayment $chequePayment): RedirectResponse
    {
        if ($chequePayment->status !== 'pending') {
            return Redirect::back()->with('error', 'Only pending payments can be rejected.');
        }

        $user = Auth::user();

        if (!$user->hasPermission('approve_cheque_payments')) {
            return Redirect::back()->with('error', 'You do not have permission to reject payments.');
        }

        if ($chequePayment->created_by === $user->id) {
            return Redirect::back()->with('error', 'You cannot reject a payment you created.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $chequePayment->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return Redirect::back()->with('success', 'Payment rejected.');
    }

    public function markAsIssued(ChequePayment $chequePayment): RedirectResponse
    {
        if ($chequePayment->status !== 'approved') {
            return Redirect::back()->with('error', 'Only approved payments can be marked as issued.');
        }

        $chequePayment->update(['status' => 'issued']);

        return Redirect::back()->with('success', 'Payment marked as issued.');
    }

    public function markAsCleared(ChequePayment $chequePayment): RedirectResponse
    {
        if ($chequePayment->status !== 'issued') {
            return Redirect::back()->with('error', 'Only issued payments can be marked as cleared.');
        }

        $chequePayment->update(['status' => 'cleared']);

        return Redirect::back()->with('success', 'Payment marked as cleared.');
    }

    public function cancel(ChequePayment $chequePayment): RedirectResponse
    {
        if (in_array($chequePayment->status, ['cleared', 'rejected', 'cancelled'])) {
            return Redirect::back()->with('error', 'Cannot cancel this payment.');
        }

        DB::beginTransaction();
        try {
            $this->createReverseTransactions($chequePayment);

            $chequePayment->update(['status' => 'cancelled']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withErrors([
                'error' => 'An error occurred while cancelling the payment: ' . $e->getMessage(),
            ]);
        }

        return Redirect::back()->with('success', 'Payment cancelled successfully. Reverse transactions have been created to credit the account(s).');
    }
}
