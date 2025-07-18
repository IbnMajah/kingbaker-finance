<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
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
            ->with('branch')
            ->orderBy('first_name');

        if (Request::input('search')) {
            $search = Request::input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (Request::input('role')) {
            $query->where('role', Request::input('role'));
        }

        if (Request::input('branch')) {
            $query->where('branch_id', Request::input('branch'));
        }

        $users = $query->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'branches' => Branch::orderBy('name')->get(),
            'filters' => Request::only(['search', 'role', 'branch'])
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'branches' => Branch::orderBy('name')->get()
        ]);
    }

    public function store(): RedirectResponse
    {
        $validated = Request::validate([
            'first_name' => ['required', 'max:25'],
            'last_name' => ['required', 'max:25'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')],
            'password' => ['required', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'manager', 'accountant', 'cashier', 'staff'])],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'phone' => ['nullable', 'max:20'],
            'permissions' => ['nullable', 'array'],
            'active' => ['boolean'],
        ]);

        if (Request::file('photo')) {
            $validated['photo_path'] = Request::file('photo')->store('users');
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return Redirect::route('users')->with('success', 'User created successfully.');
    }

    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'branch' => $user->branch,
                'permissions' => $user->permissions,
                'photo_path' => $user->photo_path,
                'active' => $user->active,
                'owner' => $user->owner,
                'deleted_at' => $user->deleted_at,
            ]
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'permissions' => $user->permissions,
                'photo_path' => $user->photo_path,
                'active' => $user->active,
                'owner' => $user->owner,
            ],
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function update(User $user): RedirectResponse
    {
        if (App::environment('demo') && $user->isDemoUser()) {
            return Redirect::back()->with('error', 'Updating the demo user is not allowed.');
        }

        $validated = Request::validate([
            'first_name' => ['required', 'max:25'],
            'last_name' => ['required', 'max:25'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'manager', 'accountant', 'cashier', 'staff'])],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'phone' => ['nullable', 'max:20'],
            'permissions' => ['nullable', 'array'],
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

        $user->update($validated);

        return Redirect::back()->with('success', 'User updated successfully.');
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
