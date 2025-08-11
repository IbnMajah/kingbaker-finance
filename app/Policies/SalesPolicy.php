<?php

namespace App\Policies;

use App\Models\User;

class SalesPolicy
{
    /**
     * Check if user can access sales module
     */
    public function viewSales(User $user): bool
    {
        return $user->hasPermission('view_sales');
    }

    /**
     * Sales data permissions
     */
    public function createSales(User $user): bool
    {
        return $user->hasPermission('create_sales');
    }

    public function editSales(User $user): bool
    {
        return $user->hasPermission('edit_sales');
    }

    public function viewSalesSummary(User $user): bool
    {
        return $user->hasPermission('view_sales_summary');
    }

    /**
     * Check if user can view all sales (cross-branch)
     */
    public function viewAllSales(User $user): bool
    {
        return $user->hasPermission('view_all_sales');
    }

    /**
     * Check if user can only view their own sales
     */
    public function viewOwnSalesOnly(User $user): bool
    {
        return $user->hasPermission('view_own_sales') && !$user->hasPermission('view_all_sales');
    }

    /**
     * Check if user can view sales for a specific branch
     */
    public function viewBranchSales(User $user, $branchId = null): bool
    {
        // If user can view all sales, allow access to any branch
        if ($user->hasPermission('view_all_sales')) {
            return true;
        }

        // If user can only view own branch
        if ($user->hasPermission('view_own_branch_only')) {
            return $branchId === null || $user->branch_id === $branchId;
        }

        return false;
    }
}