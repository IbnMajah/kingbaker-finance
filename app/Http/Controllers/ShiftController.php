<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShiftRequest;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(Request $request): Response
    {
        $shifts = Shift::query()
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->input('search').'%'))
            ->when(in_array($request->input('status'), ['active', 'inactive'], true), fn ($query) => $query
                ->where('active', $request->input('status') === 'active'))
            ->withCount(['sales', 'hrCashiers', 'cashierReconciliations'])
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Shift $shift) => [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => substr($shift->start_time, 0, 5),
                'end_time' => substr($shift->end_time, 0, 5),
                'description' => $shift->description,
                'active' => $shift->active,
                'references_count' => $shift->sales_count
                    + $shift->hr_cashiers_count
                    + $shift->cashier_reconciliations_count,
            ]);

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Shifts/Create');
    }

    public function store(ShiftRequest $request): RedirectResponse
    {
        Shift::create($request->validated());

        return to_route('shifts.index')->with('success', 'Shift created.');
    }

    public function edit(Shift $shift): Response
    {
        return Inertia::render('Shifts/Edit', [
            'shift' => [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => substr($shift->start_time, 0, 5),
                'end_time' => substr($shift->end_time, 0, 5),
                'description' => $shift->description,
                'active' => $shift->active,
            ],
        ]);
    }

    public function update(ShiftRequest $request, Shift $shift): RedirectResponse
    {
        $shift->update($request->validated());

        return to_route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        if (
            $shift->sales()->exists()
            || $shift->hrCashiers()->exists()
            || $shift->cashierReconciliations()->exists()
        ) {
            $shift->update(['active' => false]);

            return back()->with('success', 'Referenced shift was deactivated instead of deleted.');
        }

        $shift->delete();

        return back()->with('success', 'Shift deleted.');
    }
}
