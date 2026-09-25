<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashierReconciliation;
use App\Models\HrCashier;
use App\Models\Sale;
use App\Services\CashierVarianceCalculator;
use App\Services\CashierVarianceWorkflow;
use App\Services\SaleCashierVarianceSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CashierVarianceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = CashierReconciliation::query()
            ->with(['branch:id,name', 'shift:id,name'])
            ->when(!$this->canViewAllBranches($request), function ($query) use ($request) {
                $query->where('branch_id', $request->user()->branch_id);
            })
            ->when($request->input('cashier'), fn ($query, $value) => $query->where('cashier_employee_id', $value))
            ->when($request->input('branch'), fn ($query, $value) => $query->where('branch_id', $value))
            ->when($request->input('shift'), fn ($query, $value) => $query->where('shift_id', $value))
            ->when($request->input('status'), fn ($query, $value) => $query->where('status', $value))
            ->when($request->input('classification'), fn ($query, $value) => $query->where('classification', $value))
            ->when($request->input('from'), fn ($query, $value) => $query->whereDate('business_date', '>=', $value))
            ->when($request->input('to'), fn ($query, $value) => $query->whereDate('business_date', '<=', $value));

        $summary = (clone $query)
            ->where('status', 'approved')
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN classification = 'shortage' THEN calculated_variance ELSE 0 END), 0) AS shortages,
                COALESCE(ABS(SUM(CASE WHEN classification = 'surplus' THEN calculated_variance ELSE 0 END)), 0) AS surpluses,
                COALESCE(SUM(calculated_variance), 0) AS net_variance"
            )
            ->first();

        $alertQuery = CashierReconciliation::query()
            ->where('status', 'approved')
            ->where('classification', 'shortage')
            ->when(!$this->canViewAllBranches($request), function ($query) use ($request) {
                $query->where('branch_id', $request->user()->branch_id);
            });
        $repeatSince = now()->subDays((int) config('cashier_variances.repeat_window_days', 30));
        $trendRows = CashierReconciliation::query()
            ->where('status', 'approved')
            ->whereDate('business_date', '>=', now()->subYear())
            ->when(!$this->canViewAllBranches($request), function ($query) use ($request) {
                $query->where('branch_id', $request->user()->branch_id);
            })
            ->get(['business_date', 'calculated_variance']);

        return Inertia::render('CashierVariances/Index', [
            'filters' => $request->only([
                'cashier', 'branch', 'shift', 'status', 'classification', 'from', 'to',
            ]),
            'reconciliations' => $query->latest('business_date')->latest('closed_at')
                ->paginate(50)->withQueryString(),
            'summary' => $summary,
            'alerts' => [
                'highValueCount' => (clone $alertQuery)
                    ->where('calculated_variance', '>=', config('cashier_variances.high_value_threshold', 500))
                    ->count(),
                'repeatedCashiers' => (clone $alertQuery)
                    ->whereDate('business_date', '>=', $repeatSince)
                    ->select('cashier_employee_id', 'cashier_name')
                    ->selectRaw('COUNT(*) AS shortage_count')
                    ->groupBy('cashier_employee_id', 'cashier_name')
                    ->having('shortage_count', '>=', config('cashier_variances.repeat_count', 3))
                    ->orderByDesc('shortage_count')
                    ->get(),
            ],
            'trends' => [
                'daily' => $this->summarizePeriods(
                    $trendRows->where('business_date', '>=', now()->subDays(13)),
                    fn ($row) => $row->business_date->format('Y-m-d')
                ),
                'monthly' => $this->summarizePeriods(
                    $trendRows,
                    fn ($row) => $row->business_date->format('Y-m')
                ),
            ],
            'cashiers' => HrCashier::query()->orderBy('name')->get(['employee_id', 'name']),
            'branches' => $this->availableBranches($request),
            'shifts' => \App\Models\Shift::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('CashierVariances/Create', [
            'sales' => Sale::query()
                ->with(['branch:id,name', 'shift:id,name', 'hrCashier:id,name,employee_id'])
                ->whereNotNull('hr_cashier_id')
                ->whereDoesntHave('reconciliation')
                ->when(! $this->canViewAllBranches($request), fn ($query) => $query->where('branch_id', $request->user()->branch_id))
                ->orderByDesc('sales_date')
                ->orderByDesc('id')
                ->limit(100)
                ->get()
                ->map(fn (Sale $sale) => [
                    'id' => $sale->id,
                    'sales_date' => $sale->sales_date?->toDateString(),
                    'total_amount' => $sale->total_amount,
                    'expected_total' => $sale->expected_total,
                    'cashier' => $sale->hrCashier?->name ?? $sale->cashier,
                    'employee_id' => $sale->hrCashier?->employee_id,
                    'branch' => $sale->branch?->name,
                    'shift' => $sale->shift?->name,
                ]),
        ]);
    }

    public function store(Request $request, SaleCashierVarianceSynchronizer $synchronizer): RedirectResponse
    {
        $validated = $request->validate([
            'sale_id' => ['required', 'integer', 'exists:sales,id', Rule::unique('cashier_reconciliations', 'sale_id')],
            'expected_total' => ['required', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string', 'max:2000'],
            'submit' => ['nullable', 'boolean'],
        ]);

        $sale = Sale::with(['branch', 'shift', 'hrCashier'])->findOrFail($validated['sale_id']);
        abort_unless($sale->hr_cashier_id, 422, 'The selected sale does not have an assigned cashier.');
        abort_unless(
            $this->canViewAllBranches($request) || $request->user()->canAccessBranch($sale->branch_id),
            403
        );

        $sale->update(['expected_total' => $validated['expected_total']]);
        $reconciliation = $synchronizer->sync($sale->fresh(['branch', 'shift', 'hrCashier', 'reconciliation']), $request->user());

        if ($reconciliation && filled($validated['comments'] ?? null)) {
            $reconciliation->update(['comments' => $validated['comments']]);
        }

        if ($reconciliation && $request->boolean('submit')) {
            $reconciliation->update([
                'status' => 'submitted',
                'verified_by' => $request->user()->id,
                'submitted_at' => now(),
            ]);
            $reconciliation->events()->create([
                'event_type' => 'submitted',
                'actor_id' => $request->user()->id,
                'new_values' => ['status' => 'submitted'],
            ]);
        }

        return redirect()->route('cashier-variances.show', $reconciliation)
            ->with('success', $reconciliation?->status === 'submitted'
                ? 'Reconciliation submitted for approval.'
                : 'Reconciliation saved as draft.');
    }

    public function show(Request $request, CashierReconciliation $cashierVariance): Response
    {
        $this->authorizeBranch($request, $cashierVariance);

        $cashierVariance->load([
            'sale:id,sales_date,total_amount,expected_total,amount',
            'branch:id,name',
            'shift:id,name',
            'creator:id,first_name,last_name',
            'verifier:id,first_name,last_name',
            'approver:id,first_name,last_name',
            'rejector:id,first_name,last_name',
            'ledgerEntries.creator:id,first_name,last_name',
            'events.actor:id,first_name,last_name',
            'outboxEvents',
        ]);

        return Inertia::render('CashierVariances/Show', [
            'reconciliation' => $cashierVariance,
            'canApprove' => $request->user()->hasPermission('approve_cashier_variances')
                && (
                    $cashierVariance->created_by !== $request->user()->id
                    || $request->user()->isAdminOrManager()
                ),
            'canAdjust' => $request->user()->hasPermission('adjust_cashier_variances'),
        ]);
    }

    public function receipt(Request $request, CashierReconciliation $cashierVariance)
    {
        $this->authorizeBranch($request, $cashierVariance);
        abort_unless(
            $cashierVariance->receipt_image_path
                && Storage::disk('public')->exists($cashierVariance->receipt_image_path),
            404
        );

        return Storage::disk('public')->response($cashierVariance->receipt_image_path);
    }

    public function update(
        Request $request,
        CashierReconciliation $cashierVariance,
        SaleCashierVarianceSynchronizer $synchronizer
    ): RedirectResponse {
        $this->authorizeBranch($request, $cashierVariance);
        abort_unless($cashierVariance->isMutable(), 422);

        $validated = $request->validate([
            'expected_total' => ['required', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($cashierVariance->sale_id) {
            $sale = $cashierVariance->sale()->firstOrFail();
            $sale->update(['expected_total' => $validated['expected_total']]);
            $synchronizer->sync($sale->fresh(['branch', 'shift', 'hrCashier', 'reconciliation']), $request->user());
            $cashierVariance->refresh()->update(['comments' => $validated['comments'] ?? null]);
        } else {
            $calculator = app(CashierVarianceCalculator::class);
            $expected = $calculator->format($validated['expected_total']);
            $actual = $calculator->format((string) $cashierVariance->actual_total);
            $calculation = $calculator->calculate($expected, $actual);

            $cashierVariance->sale?->update(['expected_total' => $validated['expected_total']]);
            $cashierVariance->update([
                'expected_total' => $expected,
                'calculated_variance' => $calculation['amount'],
                'classification' => $calculation['classification'],
                'comments' => $validated['comments'] ?? null,
            ]);
        }

        return back()->with('success', 'Reconciliation updated.');
    }

    public function submit(Request $request, CashierReconciliation $cashierVariance): RedirectResponse
    {
        $this->authorizeBranch($request, $cashierVariance);
        abort_unless($cashierVariance->isMutable(), 422);

        $cashierVariance->update([
            'status' => 'submitted',
            'verified_by' => $request->user()->id,
            'submitted_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
        $cashierVariance->events()->create([
            'event_type' => 'submitted',
            'actor_id' => $request->user()->id,
            'new_values' => ['status' => 'submitted'],
        ]);

        return back()->with('success', 'Reconciliation submitted for approval.');
    }

    public function approve(
        Request $request,
        CashierReconciliation $cashierVariance,
        CashierVarianceWorkflow $workflow
    ): RedirectResponse {
        abort_unless($request->user()->hasPermission('approve_cashier_variances'), 403);
        $this->authorizeBranch($request, $cashierVariance);
        $validated = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);

        try {
            $workflow->approve($cashierVariance, $request->user(), $validated['comment'] ?? null);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reconciliation approved and posted to the cashier ledger.');
    }

    public function reject(
        Request $request,
        CashierReconciliation $cashierVariance,
        CashierVarianceWorkflow $workflow
    ): RedirectResponse {
        abort_unless($request->user()->hasPermission('approve_cashier_variances'), 403);
        $this->authorizeBranch($request, $cashierVariance);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        try {
            $workflow->reject($cashierVariance, $request->user(), $validated['reason']);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reconciliation rejected.');
    }

    public function reverse(
        Request $request,
        CashierReconciliation $cashierVariance,
        CashierVarianceWorkflow $workflow
    ): RedirectResponse {
        abort_unless($request->user()->hasPermission('adjust_cashier_variances'), 403);
        $this->authorizeBranch($request, $cashierVariance);
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        try {
            $workflow->reverse($cashierVariance, $request->user(), $validated['reason']);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reconciliation reversed with an offsetting ledger entry.');
    }

    public function destroy(Request $request, CashierReconciliation $cashierVariance): RedirectResponse
    {
        $this->authorizeBranch($request, $cashierVariance);
        abort_unless($cashierVariance->status === 'draft', 422);

        $receiptPath = $cashierVariance->receipt_image_path;
        $cashierVariance->update(['receipt_image_path' => null]);
        $cashierVariance->delete();
        if ($receiptPath) {
            Storage::disk('public')->delete($receiptPath);
        }

        return redirect()->route('cashier-variances.index')
            ->with('success', 'Draft reconciliation deleted.');
    }

    public function ledger(Request $request, string $employeeId): RedirectResponse
    {
        $cashier = HrCashier::query()->where('employee_id', $employeeId)->firstOrFail();

        abort_unless(
            $this->canViewAllBranches($request)
                || $request->user()->canAccessBranch($cashier->branch_id),
            403
        );

        return redirect()->route('cashiers.show', $cashier);
    }

    private function availableBranches(Request $request)
    {
        return Branch::query()
            ->when(!$this->canViewAllBranches($request), fn ($query) => $query->whereKey($request->user()->branch_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function canViewAllBranches(Request $request): bool
    {
        return $request->user()->owner
            || $request->user()->role === 'admin'
            || $request->user()->hasPermission('view_all_branches');
    }

    private function authorizeBranch(Request $request, CashierReconciliation $reconciliation): void
    {
        abort_unless(
            $this->canViewAllBranches($request)
                || $request->user()->canAccessBranch($reconciliation->branch_id),
            403
        );
    }

    private function summarizePeriods($rows, callable $keyBy)
    {
        return $rows->groupBy($keyBy)->map(function ($periodRows, $period) {
            $cents = $periodRows->map(
                fn ($row) => (int) round((float) $row->calculated_variance * 100)
            );
            $net = $cents->sum() / 100;
            $shortages = $cents->filter(fn ($amount) => $amount > 0)->sum() / 100;
            $surpluses = abs($cents->filter(fn ($amount) => $amount < 0)->sum()) / 100;

            return compact('period', 'shortages', 'surpluses', 'net');
        })->sortBy('period')->values();
    }
}
