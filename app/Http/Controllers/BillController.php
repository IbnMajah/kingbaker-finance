<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Vendor;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Bill::query()
            ->with('vendor')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->whereDate('bill_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->whereDate('bill_date', '<=', $dateTo);
            })
            ->orderBy('due_date', 'desc');

        $bills = $query->paginate(10)
            ->through(fn($bill) => [
                'id' => $bill->id,
                'reference' => $bill->reference,
                'description' => $bill->description,
                'amount' => $bill->amount,
                'bill_date' => $bill->bill_date,
                'due_date' => $bill->due_date,
                'status' => $bill->status,
                'vendor' => $bill->vendor ? [
                    'id' => $bill->vendor->id,
                    'name' => $bill->vendor->name,
                ] : null,
            ]);

        return Inertia::render('Bills/Index', [
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
            'bills' => $bills,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $vendors = Vendor::where('active', true)->orderBy('name')->get();

        return Inertia::render('Bills/Create', [
            'vendors' => $vendors,
            'billTypes' => ['inventory', 'utility', 'service', 'recurring', 'miscellaneous'],
            'recurringFrequencies' => ['monthly', 'quarterly', 'annually'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $baseRules = [
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'nullable|string|max:255',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'bill_type' => 'required|string|max:255', //
            'description' => 'nullable|string',
            'recurring_frequency' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
            'bill_image' => 'nullable|image|max:2048',
        ];

        // For inventory bills, require items; for others, require amount
        if ($request->input('bill_type') === 'inventory') {
            $baseRules = array_merge($baseRules, [
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string|max:255',
                'items.*.unit_price' => 'required|numeric|min:0.01',
                'items.*.unit_measurement' => 'nullable|string|max:50',
                'items.*.quantity' => 'required|integer|min:1',
            ]);
        } else {
            $baseRules['amount'] = 'required|numeric|min:0.01';
        }

        $validated = $request->validate($baseRules);

        $imagePath = null;
        if ($request->hasFile('bill_image')) {
            $imagePath = $request->file('bill_image')->store('bills', 'public');
        }

        // Remove bill_image and items from validated data since they're not bill columns
        $items = $validated['items'] ?? [];
        unset($validated['bill_image'], $validated['items']);

        // For inventory bills, calculate total amount from items; for others, use provided amount
        if ($request->input('bill_type') === 'inventory') {
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['unit_price'] * $item['quantity'];
            }
            $validated['amount'] = $totalAmount;
        }

        $bill = Bill::create([
            ...$validated,
            'status' => 'pending',
            'amount_paid' => 0,
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        // Create bill items only for inventory bills
        if ($request->input('bill_type') === 'inventory' && !empty($items)) {
            foreach ($items as $item) {
                $bill->items()->create([
                    'description' => $item['description'],
                    'unit_price' => $item['unit_price'],
                    'unit_measurement' => $item['unit_measurement'] ?? null,
                    'quantity' => $item['quantity'],
                    'total' => $item['unit_price'] * $item['quantity'],
                ]);
            }
        }

        return Redirect::route('bills')->with('success', 'Bill created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill): Response
    {
        $bill->load(['vendor', 'creator', 'payments.bankAccount', 'items']);

        return Inertia::render('Bills/Show', [
            'bill' => [
                'id' => $bill->id,
                'vendor' => $bill->vendor,
                'bill_number' => $bill->bill_number,
                'bill_date' => $bill->bill_date,
                'due_date' => $bill->due_date,
                'amount' => $bill->amount,
                'amount_paid' => $bill->amount_paid,
                'bill_type' => $bill->bill_type,
                'description' => $bill->description,
                'recurring_frequency' => $bill->recurring_frequency,
                'next_due_date' => $bill->next_due_date,
                'status' => $bill->status,
                'image_path' => $bill->image_path,
                'creator' => $bill->creator,
                'payments' => $bill->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'payment_method' => $payment->payment_method,
                        'reference_number' => $payment->reference_number,
                        'notes' => $payment->notes,
                        'bank_account' => $payment->bankAccount ? [
                            'id' => $payment->bankAccount->id,
                            'name' => $payment->bankAccount->name,
                        ] : null
                    ];
                }),
                'items' => $bill->items->map(fn($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'unit_measurement' => $item->unit_measurement,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ])
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill): Response
    {
        $vendors = Vendor::where('active', true)->orderBy('name')->get();
        $bill->load(['items']);

        return Inertia::render('Bills/Edit', [
            'bill' => $bill,
            'vendors' => $vendors,
            'billTypes' => ['inventory', 'utility', 'service', 'recurring', 'miscellaneous'],
            'recurringFrequencies' => ['monthly', 'quarterly', 'annually'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill): RedirectResponse
    {
        $baseRules = [
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'nullable|string|max:255',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'bill_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recurring_frequency' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
            'bill_image' => 'nullable|image|max:2048',
        ];

        // For inventory bills, require items; for others, require amount
        if ($request->input('bill_type') === 'inventory') {
            $baseRules = array_merge($baseRules, [
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:bills_items,id',
                'items.*.description' => 'required|string|max:255',
                'items.*.unit_price' => 'required|numeric|min:0.01',
                'items.*.unit_measurement' => 'nullable|string|max:50',
                'items.*.quantity' => 'required|integer|min:1',
            ]);
        } else {
            $baseRules['amount'] = 'required|numeric|min:0.01';
        }

        $validated = $request->validate($baseRules);

        if ($request->hasFile('bill_image')) {
            // Delete old image if exists
            if ($bill->image_path) {
                Storage::disk('public')->delete($bill->image_path);
            }
            $imagePath = $request->file('bill_image')->store('bills', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Remove bill_image and items from validated data since they're not bill columns
        $items = $validated['items'] ?? [];
        unset($validated['bill_image'], $validated['items']);

        // For inventory bills, calculate total amount from items; for others, use provided amount
        if ($request->input('bill_type') === 'inventory') {
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['unit_price'] * $item['quantity'];
            }
            $validated['amount'] = $totalAmount;
        }

        $bill->update($validated);

        // Update items only for inventory bills
        if ($request->input('bill_type') === 'inventory' && !empty($items)) {
            // Delete old items and create new ones
            $bill->items()->delete();
            foreach ($items as $item) {
                $bill->items()->create([
                    'description' => $item['description'],
                    'unit_price' => $item['unit_price'],
                    'unit_measurement' => $item['unit_measurement'] ?? null,
                    'quantity' => $item['quantity'],
                    'total' => $item['unit_price'] * $item['quantity'],
                ]);
            }
        } elseif ($request->input('bill_type') !== 'inventory') {
            // For non-inventory bills, delete any existing items
            $bill->items()->delete();
        }

        // Update status in case amount changed
        $bill->updateStatus();

        return Redirect::route('bills')->with('success', 'Bill updated successfully.');
    }

    /**
     * Show the payment form for the bill
     */
    public function pay(Bill $bill): Response
    {
        $bill->load(['vendor', 'creator', 'payments.bankAccount']);

        // Get bank accounts for payment
        $bankAccounts = BankAccount::where('active', true)->orderBy('name')->get();



        return Inertia::render('Bills/Pay', [
            'bill' => $bill,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Record payment for a bill
     */
    public function recordPayment(Request $request, Bill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mobile_money',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
        ]);

        // Check if payment amount doesn't exceed remaining amount
        $remainingAmount = $bill->amount - ($bill->amount_paid ?? 0);
        if ($validated['amount'] > $remainingAmount) {
            return Redirect::back()->with('error', 'Payment amount cannot exceed remaining balance.');
        }

        // Create payment record
        $bill->payments()->create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        // The amount_paid and status will be automatically updated via the BillPayment model's observers

        return Redirect::back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill): RedirectResponse
    {
        // Delete associated image if exists
        if ($bill->image_path) {
            Storage::disk('public')->delete($bill->image_path);
        }

        $bill->delete();

        return Redirect::route('bills')->with('success', 'Bill deleted successfully.');
    }
}
