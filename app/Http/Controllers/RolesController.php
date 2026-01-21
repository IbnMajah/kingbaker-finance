<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'active' => $role->active,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                ];
            });

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissionsByModule' => Permission::getGroupedByModule()->map(function ($permissions) {
                return $permissions->map(function (Permission $permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'module' => $permission->module,
                        'action' => $permission->action,
                        'description' => $permission->description,
                        'active' => $permission->active,
                    ];
                })->values();
            }),
        ]);
    }

    public function store(): RedirectResponse
    {
        $validated = Request::validate([
            'name' => ['required', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')],
            'display_name' => ['required', 'max:100'],
            'description' => ['nullable', 'max:500'],
            'active' => ['boolean'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'active' => $validated['active'] ?? true,
        ]);

        $permissionIds = collect($validated['permission_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $role->permissions()->sync($permissionIds);

        return Redirect::route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role): Response
    {
        $role->load(['permissions', 'users']);

        return Inertia::render('Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'active' => $role->active,
                'permissions' => $role->permissions
                    ->sortBy(['module', 'name'])
                    ->values()
                    ->map(function (Permission $permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'display_name' => $permission->display_name,
                            'module' => $permission->module,
                            'action' => $permission->action,
                            'active' => $permission->active,
                        ];
                    }),
                'users_count' => $role->users()->count(),
            ],
        ]);
    }

    public function edit(Role $role): Response
    {
        $role->load(['permissions']);

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'active' => $role->active,
                'permission_ids' => $role->permissions->pluck('id')->toArray(),
            ],
            'permissionsByModule' => Permission::getGroupedByModule()->map(function ($permissions) {
                return $permissions->map(function (Permission $permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'module' => $permission->module,
                        'action' => $permission->action,
                        'description' => $permission->description,
                        'active' => $permission->active,
                    ];
                })->values();
            }),
        ]);
    }

    public function update(Role $role): RedirectResponse
    {
        $validated = Request::validate([
            'name' => ['required', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role->id)],
            'display_name' => ['required', 'max:100'],
            'description' => ['nullable', 'max:500'],
            'active' => ['boolean'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'active' => $validated['active'] ?? true,
        ]);

        $permissionIds = collect($validated['permission_ids'] ?? [])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $role->permissions()->sync($permissionIds);

        return Redirect::route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deleting the admin role (system safety).
        if ($role->name === 'admin') {
            return Redirect::back()->with('error', 'The admin role cannot be deleted.');
        }

        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        return Redirect::route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
