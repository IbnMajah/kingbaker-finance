<?php

namespace App\Policies;

use App\Models\User;

class InventoryPolicy
{
    /**
     * Check if user can access inventory module
     */
    public function viewInventory(User $user): bool
    {
        return $user->hasPermission('view_inventory');
    }

    /**
     * Product permissions
     */
    public function createProducts(User $user): bool
    {
        return $user->hasPermission('create_products');
    }

    public function editProducts(User $user): bool
    {
        return $user->hasPermission('edit_products');
    }

    public function viewProducts(User $user): bool
    {
        return $user->hasPermission('view_products');
    }

    /**
     * Category permissions
     */
    public function createCategories(User $user): bool
    {
        return $user->hasPermission('create_categories');
    }

    /**
     * Production log permissions
     */
    public function manageProductionLogs(User $user): bool
    {
        return $user->hasPermission('manage_production_logs');
    }

    public function viewProductionLogs(User $user): bool
    {
        return $user->hasPermission('view_production_logs');
    }

    /**
     * Requisition permissions
     */
    public function createRequisitions(User $user): bool
    {
        return $user->hasPermission('create_requisitions');
    }

    public function viewRequisitions(User $user): bool
    {
        return $user->hasPermission('view_requisitions');
    }

    /**
     * Stock management permissions
     */
    public function adjustStock(User $user): bool
    {
        return $user->hasPermission('adjust_stock');
    }

    public function viewStockLevels(User $user): bool
    {
        return $user->hasPermission('view_stock_levels');
    }

    /**
     * Report permissions
     */
    public function viewInventoryReports(User $user): bool
    {
        return $user->hasPermission('view_inventory_reports');
    }

    /**
     * Check if user can view inventory for a specific branch
     */
    public function viewBranchInventory(User $user, $branchId = null): bool
    {
        // If user can view all branches, allow access to any branch
        if ($user->hasPermission('view_all_branches')) {
            return true;
        }

        // If user can only view own branch
        if ($user->hasPermission('view_own_branch_only')) {
            return $branchId === null || $user->branch_id === $branchId;
        }

        return false;
    }
}