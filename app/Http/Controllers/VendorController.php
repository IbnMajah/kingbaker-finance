<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Vendor::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('active', $status === 'active');
            })
            ->orderBy('name');

        $vendors = $query->paginate(10)
            ->through(fn ($vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'contact_person' => $vendor->contact_person,
                'email' => $vendor->email,
                'phone' => $vendor->phone,
                'address' => $vendor->address,
                'vendor_type' => $vendor->vendor_type,
                'active' => $vendor->active,
            ]);

        return Inertia::render('Vendors/Index', [
            'filters' => $request->only(['search', 'status']),
            'vendors' => $vendors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $organizations = Organization::orderBy('name')->get();

        return Inertia::render('Vendors/Create', [
            'organizations' => $organizations,
            'vendorTypes' => ['supplier', 'service_provider', 'utility'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'vendor_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'nullable|exists:organizations,id',
            'active' => 'boolean',
        ]);

        Vendor::create($validated);

        return Redirect::route('vendors')->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor): Response
    {
        $vendor->load([
            'organization',
            'bills' => function ($query) {
                $query->latest('bill_date')->limit(10);
            }
        ]);

        return Inertia::render('Vendors/Show', [
            'vendor' => $vendor,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor): Response
    {
        $organizations = Organization::orderBy('name')->get();

        return Inertia::render('Vendors/Edit', [
            'vendor' => $vendor,
            'organizations' => $organizations,
            'vendorTypes' => ['supplier', 'service_provider', 'utility'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'vendor_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'nullable|exists:organizations,id',
            'active' => 'boolean',
        ]);

        $vendor->update($validated);

        return Redirect::route('vendors')->with('success', 'Vendor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor): RedirectResponse
    {
        $vendor->delete();

        return Redirect::route('vendors')->with('success', 'Vendor deleted successfully.');
    }
}