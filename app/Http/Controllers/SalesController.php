<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Sale::query()
            ->with(['branch', 'shift'])
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

        $sales = $query->paginate(25)
            ->through(fn ($sale) => [
                'id' => $sale->id,
                'sales_date' => $sale->sales_date,
                'amount' => $sale->amount,
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
        return Inertia::render('Sales/Create', [
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'shifts' => Shift::orderBy('name')->get()->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'sales_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        Sale::create($validated);

        return Redirect::route('sales')->with('success', 'Sale created successfully.');
    }

    public function edit(Sale $sale): Response
    {
        return Inertia::render('Sales/Edit', [
            'sale' => [
                'id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'shift_id' => $sale->shift_id,
                'sales_date' => $sale->sales_date->format('Y-m-d'),
                'amount' => $sale->amount,
                'branch' => $sale->branch,
                'shift' => $sale->shift,
                'deleted_at' => $sale->deleted_at,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
            ],
            'branches' => Branch::orderBy('name')->get()->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name
            ]),
            'shifts' => Shift::orderBy('name')->get()->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name
            ]),
        ]);
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'sales_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $sale->update($validated);

        return Redirect::route('sales')->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->delete();

        return Redirect::route('sales')->with('success', 'Sale deleted successfully.');
    }

    public function restore(Sale $sale): RedirectResponse
    {
        $sale->restore();

        return Redirect::route('sales')->with('success', 'Sale restored successfully.');
    }
}