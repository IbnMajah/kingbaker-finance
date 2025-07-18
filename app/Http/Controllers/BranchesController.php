<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BranchesController extends Controller
{
    public function index(): Response
    {
        $query = Branch::query()
            ->orderBy('name');

        if (Request::input('search')) {
            $search = Request::input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (Request::input('status')) {
            $status = Request::input('status');
            if ($status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($status === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        }

        $branches = $query->withTrashed()->paginate(10)
            ->through(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'location' => $branch->location,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'manager_name' => $branch->manager_name,
                'description' => $branch->description,
                'daily_sales_target' => $branch->daily_sales_target,
                'monthly_sales_target' => $branch->monthly_sales_target,
                'daily_progress' => $branch->daily_progress,
                'monthly_progress' => $branch->monthly_progress,
                'active' => $branch->deleted_at === null,
                'deleted_at' => $branch->deleted_at,
            ]);

        return Inertia::render('Branches/Index', [
            'branches' => $branches,
            'filters' => Request::only(['search', 'status'])
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Branches/Create');
    }

    public function store(): RedirectResponse
    {
        $validated = Request::validate([
            'name' => ['required', 'max:100'],
            'location' => ['required', 'max:200'],
            'phone' => ['nullable', 'max:20'],
            'email' => ['nullable', 'email', 'max:50'],
            'manager_name' => ['nullable', 'max:100'],
            'description' => ['nullable', 'max:500'],
            'daily_sales_target' => ['nullable', 'numeric', 'min:0'],
            'monthly_sales_target' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
        ]);

        Branch::create($validated);

        return Redirect::route('branches')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch): Response
    {
        return Inertia::render('Branches/Edit', [
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'location' => $branch->location,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'manager_name' => $branch->manager_name,
                'description' => $branch->description,
                'daily_sales_target' => $branch->daily_sales_target,
                'monthly_sales_target' => $branch->monthly_sales_target,
                'active' => $branch->deleted_at === null,
                'deleted_at' => $branch->deleted_at,
            ],
        ]);
    }

    public function show(Branch $branch): Response
    {
        return Inertia::render('Branches/Show', [
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'location' => $branch->location,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'manager_name' => $branch->manager_name,
                'description' => $branch->description,
                'daily_sales_target' => $branch->daily_sales_target,
                'monthly_sales_target' => $branch->monthly_sales_target,
                'daily_progress' => $branch->daily_progress,
                'monthly_progress' => $branch->monthly_progress,
                'active' => $branch->deleted_at === null,
                'deleted_at' => $branch->deleted_at,
            ],
        ]);
    }

    public function update(Branch $branch): RedirectResponse
    {
        $validated = Request::validate([
            'name' => ['required', 'max:100'],
            'location' => ['required', 'max:200'],
            'phone' => ['nullable', 'max:20'],
            'email' => ['nullable', 'email', 'max:50'],
            'manager_name' => ['nullable', 'max:100'],
            'description' => ['nullable', 'max:500'],
            'daily_sales_target' => ['nullable', 'numeric', 'min:0'],
            'monthly_sales_target' => ['nullable', 'numeric', 'min:0'],
            'active' => ['boolean'],
        ]);

        $branch->update($validated);

        return Redirect::back()->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        // Check for related records
        if ($branch->users()->count() > 0) {
            return Redirect::back()->with('error', 'Cannot delete branch. There are users associated with this branch.');
        }

        if ($branch->sales()->count() > 0) {
            return Redirect::back()->with('error', 'Cannot delete branch. There are sales records associated with this branch.');
        }

        if ($branch->transactions()->count() > 0) {
            return Redirect::back()->with('error', 'Cannot delete branch. There are transactions associated with this branch.');
        }

        if ($branch->expenseClaims()->count() > 0) {
            return Redirect::back()->with('error', 'Cannot delete branch. There are expense claims associated with this branch.');
        }

        $branch->delete();

        return Redirect::back()->with('success', 'Branch deleted successfully.');
    }

    public function restore(Branch $branch): RedirectResponse
    {
        $branch->restore();

        return Redirect::back()->with('success', 'Branch restored successfully.');
    }
}