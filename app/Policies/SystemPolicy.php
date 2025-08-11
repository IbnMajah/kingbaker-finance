<?php

namespace App\Policies;

use App\Models\User;

class SystemPolicy
{
    /**
     * User management permissions
     */
    public function createUsers(User $user): bool
    {
        return $user->hasPermission('create_users');
    }

    public function editUsers(User $user): bool
    {
        return $user->hasPermission('edit_users');
    }

    public function deleteUsers(User $user): bool
    {
        return $user->hasPermission('delete_users');
    }

    public function viewUsers(User $user): bool
    {
        return $user->hasPermission('view_users');
    }

    /**
     * Role and permission management
     */
    public function assignRoles(User $user): bool
    {
        return $user->hasPermission('assign_roles');
    }

    /**
     * System access permissions
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->hasPermission('view_audit_logs');
    }

    public function accessSettings(User $user): bool
    {
        return $user->hasPermission('access_settings');
    }

    /**
     * Data export permissions
     */
    public function exportData(User $user): bool
    {
        return $user->hasPermission('export_data');
    }

    /**
     * Branch access permissions
     */
    public function viewAllBranches(User $user): bool
    {
        return $user->hasPermission('view_all_branches');
    }

    public function viewOwnBranchOnly(User $user): bool
    {
        return $user->hasPermission('view_own_branch_only');
    }

    /**
     * Check if user can access data for a specific branch
     */
    public function canAccessBranch(User $user, $branchId = null): bool
    {
        // Admin and owners can access all branches
        if ($user->role === 'admin' || $user->owner) {
            return true;
        }

        // If user can view all branches, allow access
        if ($user->hasPermission('view_all_branches')) {
            return true;
        }

        // If user can only view own branch, check branch match
        if ($user->hasPermission('view_own_branch_only')) {
            return $branchId === null || $user->branch_id === $branchId;
        }

        return false;
    }

    /**
     * Check if user has admin-level access
     */
    public function isAdmin(User $user): bool
    {
        return $user->role === 'admin' || $user->owner;
    }

    /**
     * Check if user can perform read-only operations (auditor role)
     */
    public function isReadOnlyUser(User $user): bool
    {
        return $user->hasRoleName('auditor');
    }
}