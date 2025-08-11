<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'permissions',
        'branch_id',
        'phone',
        'photo_path',
        'active',
        'owner',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'array',
        'active' => 'boolean',
        'owner' => 'boolean',
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::needsRehash($password) ? Hash::make($password) : $password;
    }

    public function isDemoUser()
    {
        return $this->email === 'admin@kingbaker.com';
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasPermission(string $permission): bool
    {
        // Admin and owner always have all permissions
        if ($this->role === 'admin' || $this->owner) {
            return true;
        }

        // Check role-based permissions
        if ($this->hasRolePermission($permission)) {
            return true;
        }

        // Fallback to old permission system for backward compatibility
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if user has permission through their roles
     */
    public function hasRolePermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission)->where('active', true);
            })
            ->where('active', true)
            ->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->role === 'admin' || $this->owner) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->role === 'admin' || $this->owner) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRoleName(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->where('active', true)->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->where('active', true)->exists();
    }

    /**
     * Get all permissions for this user through their roles
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        if ($this->role === 'admin' || $this->owner) {
            return \App\Models\Permission::where('active', true)->get();
        }

        return \App\Models\Permission::whereHas('roles', function ($query) {
            $query->whereIn('role_id', $this->roles()->pluck('roles.id'));
        })->where('active', true)->get();
    }

    /**
     * Get permissions by module
     */
    public function getPermissionsByModule(string $module): \Illuminate\Support\Collection
    {
        return $this->getAllPermissions()->where('module', $module);
    }

    public function canAccessBranch($branchId)
    {
        if ($this->owner || $this->role === 'admin') {
            return true;
        }

        return $this->branch_id === $branchId;
    }

    public function getActiveAttribute(): bool
    {
        return $this->deleted_at === null;
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('first_name')->orderBy('last_name');
    }

    public function scopeWhereRole($query, $role)
    {
        $query->where('role', $role);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        })->when($filters['role'] ?? null, function ($query, $role) {
            $query->where('role', $role);
        })->when($filters['branch'] ?? null, function ($query, $branchId) {
            $query->where('branch_id', $branchId);
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
