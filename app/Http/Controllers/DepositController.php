<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Deposit;
use App\Models\Shift;
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
            ->with(['bankAccount', 'branch', 'shift', 'creator'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('bankAccount', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
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
                $query->whereDate('transaction_date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->whereDate('transaction_date', '<=', $dateTo);
            })
            ->orderBy('transaction_date', 'desc');

        // Calculate summary statistics with same filters
        $summaryQuery = clone $query;
        $summaryStats = [
            'total_deposits' => $summaryQuery->count(),
            'total_amount' => $summaryQuery->sum('amount'),
            'this_month' => $summaryQuery->whereMonth('transaction_date', now()->month)
                                      ->whereYear('transaction_date', now()->year)
                                      ->sum('amount'),
            'today' => $summaryQuery->whereDate('transaction_date', now()->toDateString())
                                   ->sum('amount'),
        ];

        $deposits = $query->paginate(25)
            ->through(fn ($deposit) => [
                'id' => $deposit->id,
                'transaction_date' => $deposit->transaction_date,
                'description' => $deposit->description,
                'amount' => $deposit->amount,
                'payment_mode' => $deposit->payment_mode,
                'reference_number' => $deposit->reference_number,
                'bank_account' => $deposit->bankAccount ? [
                    'id' => $deposit->bankAccount->id,
                    'name' => $deposit->bankAccount->name,
                ] : null,
                'branch' => $deposit->branch ? [
                    'id' => $deposit->branch->id,
                    'name' => $deposit->branch->name,
                ] : null,
                'shift' => $deposit->shift ? [
                    'id' => $deposit->shift->id,
                    'name' => $deposit->shift->name,
                ] : null,
                'creator' => $deposit->creator ? [
                    'id' => $deposit->creator->id,
                    'name' => $deposit->creator->first_name . ' ' . $deposit->creator->last_name,
                ] : null,
                'image_path' => $deposit->image_path,
                'created_at' => $deposit->created_at,
            ]);

        return Inertia::render('Deposits/Index', [
            'filters' => $request->only(['search', 'payment_mode', 'bank_account_id', 'branch_id', 'date_from', 'date_to']),
            'deposits' => $deposits,
            'summary' => $summaryStats,
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'paymentModes' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'nafa', 'label' => 'NAFA'],
                ['value' => 'wave', 'label' => 'Wave'],
                ['value' => 'cheque', 'label' => 'Cheque'],
                ['value' => 'sales', 'label' => 'Daily Sales'],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Deposits/Create', [
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'shifts' => Shift::all()->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name
            ]),
            'paymentModes' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'nafa', 'label' => 'NAFA'],
                ['value' => 'wave', 'label' => 'Wave'],
                ['value' => 'cheque', 'label' => 'Cheque'],
                ['value' => 'sales', 'label' => 'Daily Sales'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'payment_mode' => 'required|in:cash,bank_transfer,nafa,wave,cheque,sales',
            'amount' => 'required|numeric|min:0.01',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        // Generate reference number if not provided
        if (empty($validated['reference_number'])) {
            $year = date('Y');
            $count = Deposit::whereYear('created_at', $year)->count() + 1;
            $validated['reference_number'] = 'DEP-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        // Handle file upload separately
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('deposits', 'public');
        }

        // Remove image from validated data since it's not a database column
        unset($validated['image']);

        $deposit = Deposit::create([
            ...$validated,
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
        ]);

        // Create corresponding transaction if bank account exists
        if ($deposit->bankAccount) {
            $deposit->bankAccount->transactions()->create([
                'transaction_date' => $deposit->transaction_date,
                'type' => 'credit',
                'payment_mode' => $deposit->payment_mode,
                'reference_number' => $deposit->reference_number,
                'amount' => $deposit->amount,
                'description' => $deposit->description,
                'branch_id' => $deposit->branch_id,
                'shift_id' => $deposit->shift_id,
                'category' => 'deposit',
                'image_path' => $deposit->image_path,
                'created_by' => $deposit->created_by,
            ]);
        }

        return Redirect::route('deposits')->with('success', 'Deposit recorded successfully.');
    }

    public function edit(Deposit $deposit): Response
    {
        return Inertia::render('Deposits/Edit', [
            'deposit' => $deposit->load(['bankAccount', 'branch', 'shift']),
            'bankAccounts' => BankAccount::where('active', true)->orderBy('name')->get()->map(fn($a) => [
                'value' => $a->id,
                'label' => $a->name
            ]),
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'shifts' => Shift::all()->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name
            ]),
            'paymentModes' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'nafa', 'label' => 'NAFA'],
                ['value' => 'wave', 'label' => 'Wave'],
                ['value' => 'cheque', 'label' => 'Cheque'],
                ['value' => 'sales', 'label' => 'Daily Sales'],
            ],
        ]);
    }

    public function update(Request $request, Deposit $deposit): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'payment_mode' => 'required|in:cash,bank_transfer,nafa,wave,cheque,sales',
            'amount' => 'required|numeric|min:0.01',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        // Handle file upload separately
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($deposit->image_path) {
                Storage::disk('public')->delete($deposit->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('deposits', 'public');
        }

        // Remove image from validated data since it's not a database column
        unset($validated['image']);

        $deposit->update($validated);

        // Update corresponding transaction if bank account exists
        if ($deposit->bankAccount) {
            $transaction = $deposit->bankAccount->transactions()
                ->where('category', 'deposit')
                ->where('created_at', $deposit->created_at)
                ->first();

            if ($transaction) {
                $transaction->update([
                    'transaction_date' => $deposit->transaction_date,
                    'payment_mode' => $deposit->payment_mode,
                    'reference_number' => $deposit->reference_number,
                    'amount' => $deposit->amount,
                    'description' => $deposit->description,
                    'branch_id' => $deposit->branch_id,
                    'shift_id' => $deposit->shift_id,
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
}