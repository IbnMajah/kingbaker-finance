<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Invoice::query()
            ->with(['bankAccount', 'branch', 'creator', 'payments'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->input('invoice_type'), function ($query, $invoiceType) {
                $query->where('invoice_type', $invoiceType);
            })
            ->when($request->input('customer_type'), function ($query, $customerType) {
                $query->where('customer_type', $customerType);
            })
            ->when($request->input('bank_account_id'), function ($query, $bankAccountId) {
                $query->where('bank_account_id', $bankAccountId);
            })
            ->when($request->input('branch_id'), function ($query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->whereDate('invoice_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->whereDate('invoice_date', '<=', $dateTo);
            })
            ->when($request->input('trashed'), function ($query, $trashed) {
                if ($trashed === 'with') {
                    $query->withTrashed();
                } elseif ($trashed === 'only') {
                    $query->onlyTrashed();
                }
            })
            ->orderBy('invoice_date', 'desc');

        // Calculate summary statistics with same filters
        $summaryQuery = clone $query;
        $summaryStats = [
            'total_invoices' => $summaryQuery->count(),
            'total_amount' => $summaryQuery->sum('amount'),
            'total_paid' => $summaryQuery->sum('amount_paid'),
            'pending_amount' => $summaryQuery->where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->sum('amount') - $summaryQuery->where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->sum('amount_paid'),
        ];

        $invoices = $query->paginate(25)
            ->through(fn($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer_name,
                'customer_email' => $invoice->customer_email,
                'customer_phone' => $invoice->customer_phone,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'amount' => $invoice->amount,
                'amount_paid' => $invoice->amount_paid,
                'remaining_amount' => $invoice->remaining_amount,
                'status' => $invoice->status,
                'invoice_type' => $invoice->invoice_type,
                'customer_type' => $invoice->customer_type,
                'bank_account' => $invoice->bankAccount ? [
                    'id' => $invoice->bankAccount->id,
                    'name' => $invoice->bankAccount->name,
                ] : null,
                'branch' => $invoice->branch ? [
                    'id' => $invoice->branch->id,
                    'name' => $invoice->branch->name,
                ] : null,
                'creator' => $invoice->creator ? [
                    'id' => $invoice->creator->id,
                    'name' => $invoice->creator->first_name . ' ' . $invoice->creator->last_name,
                ] : null,
                'description' => $invoice->description,
                'billing_period' => $invoice->billing_period,
                'is_recurring' => $invoice->is_recurring,
                'attachment_path' => $invoice->attachment_path,
                'deleted_at' => $invoice->deleted_at,
                'created_at' => $invoice->created_at,
            ]);

        return Inertia::render('Invoices/Index', [
            'filters' => $request->only(['search', 'status', 'invoice_type', 'customer_type', 'bank_account_id', 'branch_id', 'date_from', 'date_to', 'trashed']),
            'invoices' => $invoices,
            'summary' => $summaryStats,
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'invoiceTypes' => [
                ['value' => 'bulk_sales', 'label' => 'Bulk Sales'],
                ['value' => 'credit_customer', 'label' => 'Credit Customer'],
                ['value' => 'loans', 'label' => 'Loans'],
                ['value' => 'daily_supply', 'label' => 'Daily Supply'],
                ['value' => 'partner_billing', 'label' => 'Partner Billing'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'customerTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'shop', 'label' => 'Shop'],
                ['value' => 'partner', 'label' => 'Partner'],
                ['value' => 'branch', 'label' => 'Branch'],
                ['value' => 'hotel', 'label' => 'Hotel'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'partially_paid', 'label' => 'Partially Paid'],
                ['value' => 'overdue', 'label' => 'Overdue'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Invoices/Create', [
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'customers' => Contact::where('account_id', Auth::user()->account_id)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(fn($contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'address' => $contact->address,
                    'customer_type' => $contact->customer_type,
                ]),
            'invoiceTypes' => [
                ['value' => 'bulk_sales', 'label' => 'Bulk Sales'],
                ['value' => 'credit_customer', 'label' => 'Credit Customer'],
                ['value' => 'loans', 'label' => 'Loans'],
                ['value' => 'daily_supply', 'label' => 'Daily Supply'],
                ['value' => 'partner_billing', 'label' => 'Partner Billing'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'customerTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'shop', 'label' => 'Shop'],
                ['value' => 'partner', 'label' => 'Partner'],
                ['value' => 'branch', 'label' => 'Branch'],
                ['value' => 'hotel', 'label' => 'Hotel'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'recurringFrequencies' => [
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
            'invoice_number' => 'nullable|string|unique:invoices,invoice_number',
            'customer_id' => 'required|exists:contacts,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'amount' => 'nullable|numeric|min:0', // Amount will be calculated from items
            'invoice_type' => 'required|in:bulk_sales,credit_customer,loans,daily_supply,partner_billing,other',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'billing_period' => 'nullable|string|max:255',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:monthly,quarterly,annually',
            'next_invoice_date' => 'nullable|date|after:invoice_date',
            'attachment' => 'nullable|file|max:2048',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.unit_measurement' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        // Get customer information from the selected customer
        $customer = Contact::findOrFail($validated['customer_id']);

        // Generate invoice number if not provided
        if (empty($validated['invoice_number'])) {
            $validated['invoice_number'] = $this->generateUniqueInvoiceNumber();
        }

        // Handle file upload separately
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('invoices', 'public');
        }

        // Remove attachment and items from validated data since they're not invoice columns
        $items = $validated['items'];
        unset($validated['attachment'], $validated['items'], $validated['customer_id']);

        // Calculate total amount from items
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['unit_price'] * $item['quantity'];
        }

        $invoice = Invoice::create([
            ...$validated,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_address' => $customer->address,
            'customer_type' => $customer->customer_type ?? 'individual',
            'amount' => $totalAmount,
            'status' => 'draft',
            'amount_paid' => 0,
            'attachment_path' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        // Create invoice items
        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'unit_price' => $item['unit_price'],
                'unit_measurement' => $item['unit_measurement'] ?? null,
                'quantity' => $item['quantity'],
                'total' => $item['unit_price'] * $item['quantity'],
            ]);
        }

        return Redirect::route('invoices')->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load(['bankAccount', 'branch', 'creator', 'items', 'payments']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice): Response
    {
        $invoice->load(['bankAccount', 'branch', 'payments', 'items']);

        return Inertia::render('Invoices/Edit', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer_name,
                'customer_email' => $invoice->customer_email,
                'customer_phone' => $invoice->customer_phone,
                'customer_address' => $invoice->customer_address,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '',
                'amount' => $invoice->amount,
                'amount_paid' => $invoice->amount_paid,
                'remaining_amount' => $invoice->remaining_amount,
                'status' => $invoice->status,
                'invoice_type' => $invoice->invoice_type,
                'customer_type' => $invoice->customer_type,
                'bank_account_id' => $invoice->bank_account_id,
                'branch_id' => $invoice->branch_id,
                'description' => $invoice->description,
                'billing_period' => $invoice->billing_period,
                'is_recurring' => $invoice->is_recurring,
                'recurring_frequency' => $invoice->recurring_frequency,
                'attachment_path' => $invoice->attachment_path,
                'bankAccount' => $invoice->bankAccount,
                'branch' => $invoice->branch,
                'payments' => $invoice->payments,
                'items' => $invoice->items->map(fn($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ]),
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
                'deleted_at' => $invoice->deleted_at,
            ],
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'customers' => Contact::where('account_id', Auth::user()->account_id)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(fn($contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'address' => $contact->address,
                    'customer_type' => $contact->customer_type,
                ]),
            'invoiceTypes' => [
                ['value' => 'bulk_sales', 'label' => 'Bulk Sales'],
                ['value' => 'credit_customer', 'label' => 'Credit Customer'],
                ['value' => 'loans', 'label' => 'Loans'],
                ['value' => 'daily_supply', 'label' => 'Daily Supply'],
                ['value' => 'partner_billing', 'label' => 'Partner Billing'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'customerTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'shop', 'label' => 'Shop'],
                ['value' => 'partner', 'label' => 'Partner'],
                ['value' => 'branch', 'label' => 'Branch'],
                ['value' => 'hotel', 'label' => 'Hotel'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'recurringFrequencies' => [
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'quarterly', 'label' => 'Quarterly'],
                ['value' => 'annually', 'label' => 'Annually'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $invoice->id,
            'customer_id' => 'required|exists:contacts,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'invoice_type' => 'required|in:bulk_sales,credit_customer,loans,daily_supply,partner_billing,other',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'billing_period' => 'nullable|string|max:255',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:monthly,quarterly,annually',
            'next_invoice_date' => 'nullable|date|after:invoice_date',
            'attachment' => 'nullable|file|max:2048',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.unit_measurement' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        // Get customer information from the selected customer
        $customer = Contact::findOrFail($validated['customer_id']);

        // Handle file upload separately
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($invoice->attachment_path) {
                Storage::disk('public')->delete($invoice->attachment_path);
            }
            $validated['attachment_path'] = $request->file('attachment')->store('invoices', 'public');
        }

        // Remove attachment and items from validated data since they're not invoice columns
        $items = $validated['items'];
        unset($validated['attachment'], $validated['items'], $validated['customer_id']);

        // Calculate total amount from items
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['unit_price'] * $item['quantity'];
        }

        $invoice->update([
            ...$validated,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_address' => $customer->address,
            'customer_type' => $customer->customer_type ?? 'individual',
            'amount' => $totalAmount,
        ]);

        // Update items - delete old ones and create new ones
        $invoice->items()->delete();
        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'unit_price' => $item['unit_price'],
                'unit_measurement' => $item['unit_measurement'] ?? null,
                'quantity' => $item['quantity'],
                'total' => $item['unit_price'] * $item['quantity'],
            ]);
        }

        // Update status in case amount changed
        $invoice->updateStatus();

        return Redirect::route('invoices')->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        try {
            DB::transaction(function () use ($invoice) {
                // Create reverse transactions for any payments made on this invoice
                if ($invoice->amount_paid > 0) {
                    $this->createReverseTransactions($invoice);
                }

                // Delete attachment if exists
                if ($invoice->attachment_path) {
                    Storage::disk('public')->delete($invoice->attachment_path);
                }

                // Hard delete the invoice (no soft delete)
                $invoice->delete();
            });

            return Redirect::route('invoices')->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(Invoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => 'sent']);

        return Redirect::back()->with('success', 'Invoice marked as sent.');
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice): RedirectResponse
    {
        if ($invoice->amount_paid > 0) {
            return Redirect::back()->with('error', 'Cannot cancel an invoice with payments.');
        }

        $invoice->update(['status' => 'cancelled']);

        return Redirect::back()->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,mobile_money',
            'payment_reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Check if payment amount doesn't exceed remaining amount
        $remainingAmount = $invoice->amount - $invoice->amount_paid;
        if ($validated['amount'] > $remainingAmount) {
            return Redirect::back()->with('error', 'Payment amount cannot exceed remaining balance.');
        }

        // Update invoice paid amount
        $invoice->increment('amount_paid', $validated['amount']);

        // Update status based on payment
        $invoice->updateStatus();

        // Create transaction record for the payment
        $invoice->bankAccount->transactions()->create([
            'type' => 'credit',
            'payment_mode' => $this->mapPaymentMethod($validated['payment_method']),
            'amount' => $validated['amount'],
            'description' => "Payment received for Invoice #{$invoice->invoice_number}",
            'transaction_date' => $validated['payment_date'],
            'reference_number' => $validated['payment_reference'],
            'payee' => $invoice->customer_name,
            'branch_id' => $invoice->branch_id,
            'created_by' => Auth::id(),
        ]);

        // Note: Bank account balance is automatically updated by Transaction model events

        return Redirect::back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Map payment method to database payment mode enum
     */
    private function mapPaymentMethod($paymentMethod): string
    {
        $mapping = [
            'cash' => 'cash',
            'cheque' => 'cheque',
            'bank_transfer' => 'bank_transfer',
            'mobile_money' => 'wave', // Default mobile money to wave
        ];

        return $mapping[$paymentMethod] ?? 'other';
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return Redirect::back()->with('error', 'Invoice is already marked as paid.');
        }

        $remainingAmount = $invoice->amount - $invoice->amount_paid;

        if ($remainingAmount > 0) {
            // Record the remaining amount as payment
            $invoice->update([
                'amount_paid' => $invoice->amount,
                'status' => 'paid'
            ]);

            // Create transaction record
            $invoice->bankAccount->transactions()->create([
                'type' => 'credit',
                'payment_mode' => 'other', // Default for automatic marking as paid
                'amount' => $remainingAmount,
                'description' => "Full payment received for Invoice #{$invoice->invoice_number}",
                'transaction_date' => now(),
                'reference_number' => null, // No specific reference for automatic marking as paid
                'payee' => $invoice->customer_name,
                'branch_id' => $invoice->branch_id,
                'created_by' => Auth::id(),
            ]);

            // Note: Bank account balance is automatically updated by Transaction model events
        } else {
            $invoice->update(['status' => 'paid']);
        }

        return Redirect::back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Generate printable invoice
     */
    public function print(Invoice $invoice): Response
    {
        $invoice->load(['bankAccount', 'branch', 'creator', 'items']);

        $remainingAmount = $invoice->amount - $invoice->amount_paid;

        return Inertia::render('Invoices/Print', [
            'invoice' => $invoice,
            'remaining_amount' => $remainingAmount,
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        // This would require a PDF library like dompdf or tcpdf
        // For now, we'll redirect to print view
        return $this->print($invoice);
    }


    /**
     * Validate if an invoice number is available
     */
    public function validateInvoiceNumber(Request $request)
    {
        $invoiceNumber = $request->input('number');

        if (empty($invoiceNumber)) {
            return response()->json(['available' => true]);
        }

        // This includes ALL invoices, even soft-deleted ones
        $exists = Invoice::withTrashed()->where('invoice_number', $invoiceNumber)->exists();

        return response()->json(['available' => !$exists]);
    }

    /**
     * Create reverse transactions for invoice payments when invoice is deleted
     */
    private function createReverseTransactions(Invoice $invoice): void
    {
        if (!$invoice->bankAccount) {
            throw new \RuntimeException("Cannot create reverse transaction: Invoice #{$invoice->invoice_number} has no associated bank account.");
        }
        // Create a reverse transaction (debit) to offset the original credit transaction
        $invoice->bankAccount->transactions()->create([
            'type' => 'debit',
            'payment_mode' => 'other',
            'amount' => $invoice->amount_paid,
            'description' => "Invoice deletion reversal for Invoice #{$invoice->invoice_number}",
            'transaction_date' => now()->toDateString(),
            'reference_number' => "INV-REV-{$invoice->invoice_number}",
            'payee' => $invoice->customer_name,
            'branch_id' => $invoice->branch_id,
            'created_by' => Auth::id(),
        ]);

        // Note: Bank account balance is automatically updated by Transaction model events
    }

    /**
     * Search for customers by name, email, or phone
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $customers = Contact::where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
        })
            ->where('account_id', Auth::user()->account_id)
            ->limit(10)
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'address' => $contact->address,
                    'customer_type' => $contact->customer_type,
                ];
            });

        return response()->json($customers);
    }

    /**
     * Create or find a customer
     */
    public function createOrFindCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'customer_type' => 'nullable|in:individual,shop,partner,branch,hotel,other',
        ]);

        // Split name into first and last name
        $nameParts = explode(' ', trim($validated['name']), 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

        // Check if customer already exists by email or phone
        $existingCustomer = null;
        if ($validated['email']) {
            $existingCustomer = Contact::where('email', $validated['email'])
                ->where('account_id', Auth::user()->account_id)
                ->first();
        }

        if (!$existingCustomer && $validated['phone']) {
            $existingCustomer = Contact::where('phone', $validated['phone'])
                ->where('account_id', Auth::user()->account_id)
                ->first();
        }

        if ($existingCustomer) {
            // Update existing customer with new information
            $existingCustomer->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['email'] ?: $existingCustomer->email,
                'phone' => $validated['phone'] ?: $existingCustomer->phone,
                'address' => $validated['address'] ?: $existingCustomer->address,
                'customer_type' => $validated['customer_type'] ?: $existingCustomer->customer_type,
            ]);

            return response()->json([
                'id' => $existingCustomer->id,
                'name' => $existingCustomer->name,
                'email' => $existingCustomer->email,
                'phone' => $existingCustomer->phone,
                'address' => $existingCustomer->address,
                'customer_type' => $existingCustomer->customer_type,
                'created' => false,
            ]);
        }

        // Create new customer
        $customer = Contact::create([
            'account_id' => Auth::user()->account_id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'customer_type' => $validated['customer_type'],
        ]);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'customer_type' => $customer->customer_type,
            'created' => true,
        ]);
    }

    /**
     * Generate a unique invoice number
     */
    private function generateUniqueInvoiceNumber(): string
    {
        $year = date('Y');
        $maxRetries = 5;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                return DB::transaction(function () use ($year) {
                    // Lock the table for consistent reads
                    $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}-%")
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
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // Retry on deadlock or duplicate key
                if ($attempt === $maxRetries - 1) {
                    throw $e;
                }
                usleep(100000); // 100ms delay before retry
            }
        }

        throw new \RuntimeException('Failed to generate unique invoice number after maximum retries');
    }
}
