<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Branch;
use App\Models\ChequePayment;
use App\Models\Transaction;
use App\Models\Vendor;
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
                        ->orWhere('cheque_number', 'like', "%{$search}%");
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

        $payments = $query->paginate(25)
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
            'paymentCategories' => [
                ['value' => 'vendor_payment', 'label' => 'Vendor Payment'],
                ['value' => 'bill', 'label' => 'Bill'],
                ['value' => 'staff_advance', 'label' => 'Staff Advance'],
                ['value' => 'loan_payment', 'label' => 'Loan Payment'],
                ['value' => 'institutional_payment', 'label' => 'Institutional Payment'],
                ['value' => 'other_payment', 'label' => 'Other Payment'],
            ],
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
            'bills' => Bill::with('vendor')->whereIn('status', ['pending', 'partially_paid'])->orderBy('bill_date', 'desc')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => ($b->reference ?: "BILL-{$b->id}") . " - " . ($b->vendor->name ?? 'No Vendor') . " - " . number_format($b->amount, 2),
                'vendor_id' => $b->vendor_id,
                'amount' => $b->amount,
                'due_date' => $b->due_date?->format('Y-m-d'),
                'description' => $b->description,
            ]),
            'paymentCategories' => [
                ['value' => 'vendor_payment', 'label' => 'Vendor Payment'],
                ['value' => 'bill', 'label' => 'Bill'],
                ['value' => 'staff_advance', 'label' => 'Staff Advance'],
                ['value' => 'loan_payment', 'label' => 'Loan Payment'],
                ['value' => 'institutional_payment', 'label' => 'Institutional Payment'],
                ['value' => 'other_payment', 'label' => 'Other Payment'],
            ],
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
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_category' => 'required|in:vendor_payment,bill,staff_advance,loan_payment,institutional_payment,other_payment',
            'payment_mode' => 'required|in:cheque,bank_transfer,cash,nafa,wave,other',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'vendor_id' => 'nullable|exists:vendors,id|required_if:payment_category,vendor_payment',
            'bill_id' => 'nullable|exists:bills,id|required_if:payment_category,bill',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,quarterly,annually|required_if:is_recurring,true',
            'cheque_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'required|string',
            'notes' => 'nullable|string',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        // Generate payment number if not provided
        $year = date('Y');
        $count = ChequePayment::whereYear('created_at', $year)->count() + 1;
        $paymentNumber = 'PAY-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        // Handle file upload
        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('payments', 'public');
        }

        DB::beginTransaction();
        try {
            // Create the cheque payment
            $payment = ChequePayment::create([
                'payee' => $validated['payee'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_category' => $validated['payment_category'],
                'payment_mode' => $validated['payment_mode'],
                'bank_account_id' => $validated['bank_account_id'],
                'branch_id' => $validated['branch_id'],
                'vendor_id' => $validated['vendor_id'] ?? null,
                'bill_id' => $validated['bill_id'] ?? null,
                'is_recurring' => $validated['is_recurring'] ?? false,
                'recurring_frequency' => $validated['recurring_frequency'] ?? null,
                'cheque_number' => $validated['cheque_number'],
                'reference_number' => $validated['reference_number'],
                'description' => $validated['description'],
                'notes' => $validated['notes'],
                'payment_number' => $paymentNumber,
                'status' => $validated['payment_mode'] === 'cheque' ? 'pending' : 'issued',
                'receipt_image_path' => $imagePath,
                'created_by' => Auth::id(),
            ]);

            // If this is a bill payment, create a corresponding bill payment record
            // The BillPayment model will handle creating the transaction
            if ($validated['payment_category'] === 'bill' && $validated['bill_id']) {
                $bill = Bill::findOrFail($validated['bill_id']);

                BillPayment::create([
                    'bill_id' => $validated['bill_id'],
                    'bank_account_id' => $validated['bank_account_id'],
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_mode'],
                    'reference_number' => $validated['reference_number'] ?: $paymentNumber,
                    'notes' => $validated['notes'],
                    'created_by' => Auth::id(),
                ]);
            } else {
                // Create corresponding transaction for non-bill payments (DEBIT as per requirements)
                Transaction::create([
                    'bank_account_id' => $validated['bank_account_id'],
                    'transaction_date' => $validated['payment_date'],
                    'type' => 'debit', // Payments are always debit as per requirements
                    'payment_mode' => $validated['payment_mode'],
                    'reference_number' => $validated['reference_number'] ?: $paymentNumber,
                    'payee' => $validated['payee'],
                    'amount' => $validated['amount'],
                    'description' => $validated['description'],
                    'branch_id' => $validated['branch_id'],
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

        return Redirect::route('cheque-payments')->with('success', 'Payment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChequePayment $chequePayment): Response
    {
        $chequePayment->load(['bankAccount', 'branch', 'creator']);

        return Inertia::render('ChequePayments/Show', [
            'payment' => $chequePayment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChequePayment $chequePayment): Response
    {
        return Inertia::render('ChequePayments/Edit', [
            'payment' => $chequePayment->load(['bankAccount', 'branch', 'vendor', 'bill']),
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
            'bills' => Bill::with('vendor')->whereIn('status', ['pending', 'partially_paid'])->orderBy('bill_date', 'desc')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => ($b->reference ?: "BILL-{$b->id}") . " - " . ($b->vendor->name ?? 'No Vendor') . " - " . number_format($b->amount, 2),
                'vendor_id' => $b->vendor_id,
                'amount' => $b->amount,
                'due_date' => $b->due_date?->format('Y-m-d'),
                'description' => $b->description,
            ]),
            'paymentCategories' => [
                ['value' => 'vendor_payment', 'label' => 'Vendor Payment'],
                ['value' => 'bill', 'label' => 'Bill'],
                ['value' => 'staff_advance', 'label' => 'Staff Advance'],
                ['value' => 'loan_payment', 'label' => 'Loan Payment'],
                ['value' => 'institutional_payment', 'label' => 'Institutional Payment'],
                ['value' => 'other_payment', 'label' => 'Other Payment'],
            ],
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
                ['value' => 'issued', 'label' => 'Issued'],
                ['value' => 'cleared', 'label' => 'Cleared'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
            'recurringFrequencies' => [
                ['value' => 'daily', 'label' => 'Daily'],
                ['value' => 'weekly', 'label' => 'Weekly'],
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'quarterly', 'label' => 'Quarterly'],
                ['value' => 'annually', 'label' => 'Annually'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChequePayment $chequePayment): RedirectResponse
    {
        $validated = $request->validate([
            'payee' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_category' => 'required|in:vendor_payment,bill,staff_advance,loan_payment,institutional_payment,other_payment',
            'payment_mode' => 'required|in:cheque,bank_transfer,cash,nafa,wave,other',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'vendor_id' => 'nullable|exists:vendors,id|required_if:payment_category,vendor_payment',
            'bill_id' => 'nullable|exists:bills,id|required_if:payment_category,bill',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,quarterly,annually|required_if:is_recurring,true',
            'cheque_number' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,issued,cleared,cancelled',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('receipt_image')) {
            // Delete old image if exists
            if ($chequePayment->receipt_image_path) {
                Storage::disk('public')->delete($chequePayment->receipt_image_path);
            }
            $validated['receipt_image_path'] = $request->file('receipt_image')->store('payments', 'public');
        }

        // Remove receipt_image from validated data since it's not a database column
        unset($validated['receipt_image']);

        $chequePayment->update($validated);

        return Redirect::route('cheque-payments')->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChequePayment $chequePayment): RedirectResponse
    {
        if ($chequePayment->status === 'cleared') {
            return Redirect::back()->with('error', 'Cannot delete a cleared payment.');
        }

        // Delete associated image if exists
        if ($chequePayment->receipt_image_path) {
            Storage::disk('public')->delete($chequePayment->receipt_image_path);
        }

        $chequePayment->delete();

        return Redirect::route('cheque-payments')->with('success', 'Payment deleted successfully.');
    }

    /**
     * Mark payment as issued
     */
    public function markAsIssued(ChequePayment $chequePayment): RedirectResponse
    {
        $chequePayment->update(['status' => 'issued']);

        return Redirect::back()->with('success', 'Payment marked as issued.');
    }

    /**
     * Mark payment as cleared
     */
    public function markAsCleared(ChequePayment $chequePayment): RedirectResponse
    {
        $chequePayment->update(['status' => 'cleared']);

        return Redirect::back()->with('success', 'Payment marked as cleared.');
    }

    /**
     * Cancel payment
     */
    public function cancel(ChequePayment $chequePayment): RedirectResponse
    {
        if ($chequePayment->status === 'cleared') {
            return Redirect::back()->with('error', 'Cannot cancel a cleared payment.');
        }

        $chequePayment->update(['status' => 'cancelled']);

        return Redirect::back()->with('success', 'Payment cancelled successfully.');
    }
}
