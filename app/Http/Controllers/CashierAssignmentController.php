<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCashierAssignmentRequest;
use App\Models\Branch;
use App\Models\HrCashier;
use App\Models\Shift;
use App\Services\HrCashierSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CashierAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $cashiers = HrCashier::query()
            ->with(['branch:id,name', 'defaultShift:id,name,start_time,end_time'])
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn ($query) => $query
                    ->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('hr_branch_name', 'like', "%{$search}%"));
            })
            ->when(in_array($request->input('status'), ['active', 'inactive'], true), fn ($query) => $query
                ->where('status', $request->input('status')))
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (HrCashier $cashier) => [
                'id' => $cashier->id,
                'employee_id' => $cashier->employee_id,
                'name' => $cashier->name,
                'email' => $cashier->email,
                'status' => $cashier->status,
                'hr_branch_code' => $cashier->hr_branch_code,
                'hr_branch_name' => $cashier->hr_branch_name,
                'branch_id' => $cashier->branch_id,
                'branch' => $cashier->branch,
                'default_shift_id' => $cashier->default_shift_id,
                'default_shift' => $cashier->defaultShift,
                'last_synced_at' => $cashier->last_synced_at?->toIso8601String(),
            ]);

        $lastSyncedAt = HrCashier::query()->max('last_synced_at');
        $staleAfter = max(60, (int) config('services.hr.cache_stale_after', 3600));
        $cacheAge = $lastSyncedAt ? abs(now()->diffInSeconds($lastSyncedAt)) : null;

        return Inertia::render('CashierAssignments/Index', [
            'cashiers' => $cashiers,
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'shifts' => Shift::query()->where('active', true)->orderBy('start_time')->get(['id', 'name', 'start_time', 'end_time']),
            'filters' => $request->only(['search', 'status']),
            'cache' => [
                'last_synced_at' => $lastSyncedAt,
                'age_seconds' => $cacheAge,
                'stale' => $cacheAge === null || $cacheAge > $staleAfter,
                'stale_after_seconds' => $staleAfter,
            ],
        ]);
    }

    public function update(UpdateCashierAssignmentRequest $request, HrCashier $cashier): RedirectResponse
    {
        $cashier->update($request->validated());

        return back()->with('success', 'Cashier assignment updated.');
    }

    public function sync(HrCashierSyncService $service): RedirectResponse
    {
        try {
            $count = $service->sync();

            return back()->with('success', "Cashier cache refreshed ({$count} active).");
        } catch (RuntimeException $exception) {
            Log::warning('HR cashier cache refresh failed.');

            return back()->with('error', $exception->getMessage());
        }
    }
}
