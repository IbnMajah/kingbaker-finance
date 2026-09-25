<?php

namespace App\Http\Controllers;

use App\Models\HrCashier;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashierController extends Controller
{
    public function show(Request $request, HrCashier $cashier): Response
    {
        abort_unless(
            $request->user()->hasPermission('view_cashier_variances')
                || $request->user()->hasPermission('view_sales')
                || $request->user()->hasPermission('manage_cashier_assignments'),
            403
        );

        $canViewAllBranches = $request->user()->owner
            || $request->user()->role === 'admin'
            || $request->user()->hasPermission('view_all_branches');

        abort_unless(
            $canViewAllBranches || $request->user()->canAccessBranch($cashier->branch_id),
            403
        );

        $cashier->load(['branch:id,name', 'defaultShift:id,name,start_time,end_time']);

        $salesQuery = Sale::query()
            ->with(['branch:id,name', 'shift:id,name', 'reconciliation'])
            ->where('hr_cashier_id', $cashier->id)
            ->when(! $canViewAllBranches, fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->orderByDesc('sales_date')
            ->orderByDesc('id');

        $ledgerQuery = $cashier->ledgerEntries()
            ->when(! $canViewAllBranches, fn ($query) => $query->where('branch_id', $request->user()->branch_id));

        $ledgerSummary = (clone $ledgerQuery)->selectRaw(
            "COALESCE(SUM(CASE WHEN entry_type IN ('shortage', 'shortage_reversal') THEN variance_amount ELSE 0 END), 0) AS shortages,
            COALESCE(ABS(SUM(CASE WHEN entry_type IN ('surplus', 'surplus_reversal') THEN variance_amount ELSE 0 END)), 0) AS surpluses,
            COALESCE(SUM(variance_amount), 0) AS net_variance"
        )->first();

        $salesSummary = (clone $salesQuery)->selectRaw(
            'COUNT(*) as sales_count,
            COALESCE(SUM(total_amount), 0) as total_amount,
            COALESCE(SUM(expected_total), 0) as expected_total'
        )->first();

        return Inertia::render('Cashiers/Show', [
            'cashier' => [
                'id' => $cashier->id,
                'employee_id' => $cashier->employee_id,
                'name' => $cashier->name,
                'email' => $cashier->email,
                'status' => $cashier->status,
                'hr_branch_name' => $cashier->hr_branch_name,
                'branch' => $cashier->branch,
                'default_shift' => $cashier->defaultShift,
            ],
            'summary' => [
                'sales_count' => (int) ($salesSummary->sales_count ?? 0),
                'total_amount' => $salesSummary->total_amount ?? 0,
                'shortages' => $ledgerSummary->shortages ?? 0,
                'surpluses' => $ledgerSummary->surpluses ?? 0,
                'net_variance' => $ledgerSummary->net_variance ?? 0,
            ],
            'sales' => $salesQuery->paginate(25, ['*'], 'sales_page')->withQueryString()->through(fn (Sale $sale) => [
                'id' => $sale->id,
                'sales_date' => $sale->sales_date?->toDateString(),
                'branch' => $sale->branch,
                'shift' => $sale->shift,
                'total_amount' => $sale->total_amount,
                'expected_total' => $sale->expected_total,
                'amount' => $sale->amount,
                'variance' => $sale->reconciliation?->calculated_variance,
                'classification' => $sale->reconciliation?->classification,
                'status' => $sale->reconciliation?->status,
                'reconciliation_id' => $sale->reconciliation?->id,
            ]),
            'ledger' => $ledgerQuery->with(['branch:id,name', 'shift:id,name'])
                ->latest('entry_date')
                ->latest('id')
                ->paginate(25, ['*'], 'ledger_page')
                ->withQueryString(),
        ]);
    }
}
