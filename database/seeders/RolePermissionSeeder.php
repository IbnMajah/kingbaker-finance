<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->assignAdminPermissions();
        $this->assignFinanceManagerPermissions();
        $this->assignAccountantPermissions();
        $this->assignSalesStaffPermissions();
        $this->assignInventorySupervisorPermissions();
        $this->assignProcurementStaffPermissions();
        $this->assignAuditorPermissions();
        $this->assignBranchSupervisorPermissions();
    }

    private function assignAdminPermissions(): void
    {
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            // Admin gets ALL permissions
            $allPermissions = Permission::where('active', true)->pluck('id');
            $admin->permissions()->sync($allPermissions);
        }
    }

    private function assignFinanceManagerPermissions(): void
    {
        $role = Role::where('name', 'finance_manager')->first();
        if (!$role) return;

        $permissions = [
            // Full finance module access
            'view_finance',
            'create_bank_accounts',
            'edit_bank_accounts',
            'view_bank_accounts',
            'create_deposits',
            'edit_deposits',
            'view_deposits',
            'create_cheque_payments',
            'edit_cheque_payments',
            'view_cheque_payments',
            'create_bills',
            'edit_bills',
            'view_bills',
            'create_recurring_bills',
            'view_recurring_bills',
            'create_expenses',
            'edit_expenses',
            'view_expenses',
            'approve_expenses',
            'create_invoices',
            'edit_invoices',
            'view_invoices',
            'create_vendors',
            'edit_vendors',
            'view_vendors',
            'reconcile_transactions',
            'approve_transactions',
            'view_transactions',
            'print_receipts',
            'view_reports',
            'generate_reports',

            // System permissions
            'create_organizations',
            'edit_organizations',
            'view_organizations',
            'create_branches',
            'edit_branches',
            'view_branches',
            'view_all_branches',
            'export_data',
        ];

        $this->syncPermissions($role, $permissions);
    }

    private function assignAccountantPermissions(): void
    {
        $role = Role::where('name', 'accountant')->first();
        if (!$role) return;

        $permissions = [
            // Limited finance module access
            'view_finance',
            'view_bank_accounts',
            'create_deposits',
            'edit_deposits',
            'view_deposits',
            'create_cheque_payments',
            'edit_cheque_payments',
            'view_cheque_payments',
            'create_bills',
            'edit_bills',
            'view_bills', // non-recurring only
            'view_recurring_bills', // view only
            'create_expenses',
            'edit_expenses',
            'view_expenses', // cannot approve
            'create_invoices',
            'edit_invoices',
            'view_invoices',
            'view_vendors', // view vendor bills only
            'view_transactions', // cannot reconcile
            'print_receipts',
            'view_reports',

            // System permissions
            'view_organizations',
            'view_branches',
            'view_all_branches',
            'export_data',
        ];

        $this->syncPermissions($role, $permissions);
    }

    private function assignSalesStaffPermissions(): void
    {
        $role = Role::where('name', 'sales_staff')->first();
        if (!$role) return;

        $permissions = [
            // Sales module only
            'view_sales',
            'create_sales',
            'edit_sales',
            'view_sales_summary',
            'view_own_sales',
            'create_invoices',
            'view_invoices', // for bulk/partner customers

            // System permissions
            'view_own_branch_only',
        ];

        $this->syncPermissions($role, $permissions);
    }

    private function assignInventorySupervisorPermissions(): void
    {
        $role = Role::where('name', 'inventory_supervisor')->first();
        if (!$role) return;

        $permissions = [
            // Full inventory access
            'view_inventory',
            'create_products',
            'edit_products',
            'view_products',
            'create_categories',
            'manage_production_logs',
            'view_production_logs',
            'create_requisitions',
            'view_requisitions',
            'adjust_stock',
            'view_stock_levels',
            'view_inventory_reports',

            // System permissions
            'view_all_branches',
            'export_data',
        ];

        $this->syncPermissions($role, $permissions);
    }

    private function assignProcurementStaffPermissions(): void
    {
        $role = Role::where('name', 'procurement_staff')->first();
        if (!$role) return;

        $permissions = [
            // Inventory access
            'view_inventory',
            'create_requisitions',
            'view_requisitions',
            'view_products',

            // Finance access (limited to vendors and bills)
            'view_finance',
            'create_bills',
            'edit_bills',
            'view_bills',
            'create_vendors',
            'edit_vendors',
            'view_vendors',

            // System permissions
            'view_all_branches',
        ];

        $this->syncPermissions($role, $permissions);
    }

    private function assignAuditorPermissions(): void
    {
        $role = Role::where('name', 'auditor')->first();
        if (!$role) return;

        $permissions = [
            // Read-only access to all modules
            'view_finance',
            'view_bank_accounts',
            'view_deposits',
            'view_cheque_payments',
            'view_bills',
            'view_recurring_bills',
            'view_expenses',
            'view_invoices',
            'view_vendors',
            'view_transactions',
            'view_reports',

            'view_inventory',
            'view_products',
            'view_production_logs',
            'view_requisitions',
            'view_stock_levels',
            'view_inventory_reports',

            'view_sales',
            'view_sales_summary',
            'view_all_sales',

            // System permissions
            'view_organizations',
            'view_branches',
            'view_all_branches',
            'export_data',
        ];

        $this->syncPermissions($role, $permissions);
    }

    private function assignBranchSupervisorPermissions(): void
    {
        $role = Role::where('name', 'branch_supervisor')->first();
        if (!$role) return;

        $permissions = [
            // Limited sales access
            'view_sales',
            'create_sales',
            'edit_sales',
            'view_own_sales',

            // Limited inventory access
            'view_inventory',
            'view_products',
            'view_production_logs',
            'create_requisitions',
            'view_requisitions',

            // Limited finance access
            'view_finance',
            'create_deposits',
            'view_deposits',
            'create_expenses',
            'view_expenses',

            // System permissions
            'view_own_branch_only',
        ];

        $this->syncPermissions($role, $permissions);
    }

    private function syncPermissions(Role $role, array $permissionNames): void
    {
        $permissionIds = Permission::whereIn('name', $permissionNames)
            ->where('active', true)
            ->pluck('id')
            ->toArray();

        $role->permissions()->sync($permissionIds);
    }
}
