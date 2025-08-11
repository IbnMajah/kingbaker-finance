<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(): Response
    {
        $query = User::query()
            ->with(['branch', 'roles'])
            ->orderBy('first_name');

        if (Request::input('search')) {
            $search = Request::input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (Request::input('role')) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', Request::input('role'));
            });
        }

        if (Request::input('branch')) {
            $query->where('branch_id', Request::input('branch'));
        }

        $users = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role, // Keep legacy role field
                'roles' => $user->roles->pluck('name')->toArray(), // New roles array
                'primary_role' => $user->roles->first()?->name, // Primary role for display
                'branch' => $user->branch,
                'active' => $user->active,
                'owner' => $user->owner,
                'deleted_at' => $user->deleted_at,
            ];
        });

        return Inertia::render('Users/Index', [
            'users' => $users,
            'branches' => Branch::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
            'filters' => Request::only(['search', 'role', 'branch'])
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'branches' => Branch::orderBy('name')->get(),
            'roles' => Role::where('active', true)->orderBy('name')->get()
        ]);
    }

    public function store(): RedirectResponse
    {
        $roleNames = Role::where('active', true)->pluck('name')->toArray();

        $validated = Request::validate([
            'first_name' => ['required', 'max:25'],
            'last_name' => ['required', 'max:25'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')],
            'password' => ['required', 'min:8'],
            'role_name' => ['required', Rule::in($roleNames)],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'phone' => ['nullable', 'max:20'],
            'active' => ['boolean'],
        ]);

        if (Request::file('photo')) {
            $validated['photo_path'] = Request::file('photo')->store('users');
        }

        $validated['password'] = Hash::make($validated['password']);

        // Keep legacy role field for backward compatibility
        $validated['role'] = $validated['role_name'];
        unset($validated['role_name']);

        $user = User::create($validated);

        // Assign the role using the new role system
        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        return Redirect::route('users')->with('success', 'User created successfully.');
    }

    public function show(User $user): Response
    {
        $user->load(['branch', 'roles']);

        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role, // Legacy role field
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'description' => $role->description,
                    ];
                }),
                'permissions' => $user->getAllPermissions()->map(function ($permission) {
                    return [
                        'name' => $permission->name,
                        'description' => $permission->description,
                        'module' => $permission->module,
                    ];
                }),
                'branch' => $user->branch,
                'photo_path' => $user->photo_path,
                'active' => $user->active,
                'owner' => $user->owner,
                'deleted_at' => $user->deleted_at,
            ]
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load(['branch', 'roles']);

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role, // Legacy role field
                'current_role' => $user->roles->first()?->name,
                'roles' => $user->roles->pluck('name')->toArray(),
                'branch_id' => $user->branch_id,
                'photo_path' => $user->photo_path,
                'active' => $user->active,
                'owner' => $user->owner,
                'deleted_at' => $user->deleted_at,
            ],
            'branches' => Branch::orderBy('name')->get(),
            'roles' => Role::where('active', true)->orderBy('name')->get()
        ]);
    }

    public function update(User $user): RedirectResponse
    {
        if (App::environment('demo') && $user->isDemoUser()) {
            return Redirect::back()->with('error', 'Updating the demo user is not allowed.');
        }

        $roleNames = Role::where('active', true)->pluck('name')->toArray();

        $validated = Request::validate([
            'first_name' => ['required', 'max:25'],
            'last_name' => ['required', 'max:25'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'min:8'],
            'role_name' => ['required', Rule::in($roleNames)],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'phone' => ['nullable', 'max:20'],
            'active' => ['boolean'],
        ]);

        if (Request::file('photo')) {
            $validated['photo_path'] = Request::file('photo')->store('users');
        }

        if ($validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Keep legacy role field for backward compatibility
        $validated['role'] = $validated['role_name'];
        unset($validated['role_name']);

        $user->update($validated);

        // Update the role using the new role system
        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        return Redirect::route('users')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (App::environment('demo') && $user->isDemoUser()) {
            return Redirect::back()->with('error', 'Deleting the demo user is not allowed.');
        }

        if ($user->owner) {
            return Redirect::back()->with('error', 'Cannot delete owner user.');
        }

        $user->delete();

        return Redirect::back()->with('success', 'User deleted successfully.');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->restore();

        return Redirect::back()->with('success', 'User restored.');
    }
}
