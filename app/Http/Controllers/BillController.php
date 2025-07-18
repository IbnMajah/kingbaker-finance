<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

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
            ->orderBy('due_date', 'desc');

        $bills = $query->paginate(10)
            ->through(fn ($bill) => [
                'id' => $bill->id,
                'reference' => $bill->reference,
                'description' => $bill->description,
                'amount' => $bill->amount,
                'due_date' => $bill->due_date,
                'status' => $bill->status,
                'vendor' => $bill->vendor ? [
                    'id' => $bill->vendor->id,
                    'name' => $bill->vendor->name,
                ] : null,
            ]);

        return Inertia::render('Bills/Index', [
            'filters' => $request->only(['search', 'status']),
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
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'nullable|string|max:255',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0.01',
            'bill_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recurring_frequency' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
            'bill_image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('bill_image')) {
            $imagePath = $request->file('bill_image')->store('bills', 'public');
        }

        // Remove bill_image from validated data since it's not a database column
        unset($validated['bill_image']);

        Bill::create([
            ...$validated,
            'status' => 'pending',
            'amount_paid' => 0,
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        return Redirect::route('bills')->with('success', 'Bill created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill): Response
    {
        $bill->load(['vendor', 'creator', 'payments.bankAccount']);

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
                })
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill): Response
    {
        $vendors = Vendor::where('active', true)->orderBy('name')->get();

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
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'nullable|string|max:255',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0.01',
            'bill_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recurring_frequency' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
            'bill_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('bill_image')) {
            // Delete old image if exists
            if ($bill->image_path) {
                Storage::disk('public')->delete($bill->image_path);
            }
            $imagePath = $request->file('bill_image')->store('bills', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Remove bill_image from validated data since it's not a database column
        unset($validated['bill_image']);

        $bill->update($validated);

        // Update status in case amount changed
        $bill->updateStatus();

        return Redirect::route('bills')->with('success', 'Bill updated successfully.');
    }

    /**
     * Record payment for a bill
     */
    public function recordPayment(Request $request, Bill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mobile_money',
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