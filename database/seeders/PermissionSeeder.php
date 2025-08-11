<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->createSystemPermissions();
        $this->createFinancePermissions();
        $this->createSalesPermissions();
        $this->createInventoryPermissions();
        $this->createHRPermissions();
    }

    private function createSystemPermissions(): void
    {
        $permissions = [
            ['name' => 'create_users', 'display_name' => 'Create Users', 'module' => 'system', 'action' => 'create'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users', 'module' => 'system', 'action' => 'update'],
            ['name' => 'delete_users', 'display_name' => 'Delete Users', 'module' => 'system', 'action' => 'delete'],
            ['name' => 'view_users', 'display_name' => 'View Users', 'module' => 'system', 'action' => 'read'],
            ['name' => 'assign_roles', 'display_name' => 'Assign Roles', 'module' => 'system', 'action' => 'update'],
            ['name' => 'view_audit_logs', 'display_name' => 'View Audit Logs', 'module' => 'system', 'action' => 'read'],
            ['name' => 'access_settings', 'display_name' => 'Access Settings', 'module' => 'system', 'action' => 'read'],
            ['name' => 'view_own_branch_only', 'display_name' => 'View Own Branch Only', 'module' => 'system', 'action' => 'read'],
            ['name' => 'view_all_branches', 'display_name' => 'View All Branches', 'module' => 'system', 'action' => 'read'],
            ['name' => 'export_data', 'display_name' => 'Export Data', 'module' => 'system', 'action' => 'export'],
            ['name' => 'create_organizations', 'display_name' => 'Create Organizations', 'module' => 'system', 'action' => 'create'],
            ['name' => 'edit_organizations', 'display_name' => 'Edit Organizations', 'module' => 'system', 'action' => 'update'],
            ['name' => 'view_organizations', 'display_name' => 'View Organizations', 'module' => 'system', 'action' => 'read'],
            ['name' => 'create_branches', 'display_name' => 'Create Branches', 'module' => 'system', 'action' => 'create'],
            ['name' => 'edit_branches', 'display_name' => 'Edit Branches', 'module' => 'system', 'action' => 'update'],
            ['name' => 'view_branches', 'display_name' => 'View Branches', 'module' => 'system', 'action' => 'read'],
        ];

        $this->insertPermissions($permissions);
    }

    private function createFinancePermissions(): void
    {
        $permissions = [
            ['name' => 'view_finance', 'display_name' => 'View Finance Module', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'create_bank_accounts', 'display_name' => 'Create Bank Accounts', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'edit_bank_accounts', 'display_name' => 'Edit Bank Accounts', 'module' => 'finance', 'action' => 'update'],
            ['name' => 'view_bank_accounts', 'display_name' => 'View Bank Accounts', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'create_deposits', 'display_name' => 'Create Deposits', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'edit_deposits', 'display_name' => 'Edit Deposits', 'module' => 'finance', 'action' => 'update'],
            ['name' => 'view_deposits', 'display_name' => 'View Deposits', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'create_cheque_payments', 'display_name' => 'Create Cheque Payments', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'edit_cheque_payments', 'display_name' => 'Edit Cheque Payments', 'module' => 'finance', 'action' => 'update'],
            ['name' => 'view_cheque_payments', 'display_name' => 'View Cheque Payments', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'create_bills', 'display_name' => 'Create Bills', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'edit_bills', 'display_name' => 'Edit Bills', 'module' => 'finance', 'action' => 'update'],
            ['name' => 'view_bills', 'display_name' => 'View Bills', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'create_recurring_bills', 'display_name' => 'Create Recurring Bills', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'view_recurring_bills', 'display_name' => 'View Recurring Bills', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'create_expenses', 'display_name' => 'Create Expenses', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'edit_expenses', 'display_name' => 'Edit Expenses', 'module' => 'finance', 'action' => 'update'],
            ['name' => 'view_expenses', 'display_name' => 'View Expenses', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'approve_expenses', 'display_name' => 'Approve Expenses', 'module' => 'finance', 'action' => 'approve'],
            ['name' => 'create_invoices', 'display_name' => 'Create Invoices', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'edit_invoices', 'display_name' => 'Edit Invoices', 'module' => 'finance', 'action' => 'update'],
            ['name' => 'view_invoices', 'display_name' => 'View Invoices', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'create_vendors', 'display_name' => 'Create Vendors', 'module' => 'finance', 'action' => 'create'],
            ['name' => 'edit_vendors', 'display_name' => 'Edit Vendors', 'module' => 'finance', 'action' => 'update'],
            ['name' => 'view_vendors', 'display_name' => 'View Vendors', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'reconcile_transactions', 'display_name' => 'Reconcile Transactions', 'module' => 'finance', 'action' => 'reconcile'],
            ['name' => 'approve_transactions', 'display_name' => 'Approve Transactions', 'module' => 'finance', 'action' => 'approve'],
            ['name' => 'view_transactions', 'display_name' => 'View Transactions', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'print_receipts', 'display_name' => 'Print Receipts', 'module' => 'finance', 'action' => 'print'],
            ['name' => 'view_reports', 'display_name' => 'View Financial Reports', 'module' => 'finance', 'action' => 'read'],
            ['name' => 'generate_reports', 'display_name' => 'Generate Financial Reports', 'module' => 'finance', 'action' => 'create'],
        ];

        $this->insertPermissions($permissions);
    }

    private function createSalesPermissions(): void
    {
        $permissions = [
            ['name' => 'view_sales', 'display_name' => 'View Sales Module', 'module' => 'sales', 'action' => 'read'],
            ['name' => 'create_sales', 'display_name' => 'Create Sales', 'module' => 'sales', 'action' => 'create'],
            ['name' => 'edit_sales', 'display_name' => 'Edit Sales', 'module' => 'sales', 'action' => 'update'],
            ['name' => 'view_sales_summary', 'display_name' => 'View Sales Summary', 'module' => 'sales', 'action' => 'read'],
            ['name' => 'view_all_sales', 'display_name' => 'View All Sales', 'module' => 'sales', 'action' => 'read'],
            ['name' => 'view_own_sales', 'display_name' => 'View Own Sales', 'module' => 'sales', 'action' => 'read'],
        ];

        $this->insertPermissions($permissions);
    }

    private function createInventoryPermissions(): void
    {
        $permissions = [
            ['name' => 'view_inventory', 'display_name' => 'View Inventory Module', 'module' => 'inventory', 'action' => 'read'],
            ['name' => 'create_products', 'display_name' => 'Create Products', 'module' => 'inventory', 'action' => 'create'],
            ['name' => 'edit_products', 'display_name' => 'Edit Products', 'module' => 'inventory', 'action' => 'update'],
            ['name' => 'view_products', 'display_name' => 'View Products', 'module' => 'inventory', 'action' => 'read'],
            ['name' => 'create_categories', 'display_name' => 'Create Categories', 'module' => 'inventory', 'action' => 'create'],
            ['name' => 'manage_production_logs', 'display_name' => 'Manage Production Logs', 'module' => 'inventory', 'action' => 'create'],
            ['name' => 'view_production_logs', 'display_name' => 'View Production Logs', 'module' => 'inventory', 'action' => 'read'],
            ['name' => 'create_requisitions', 'display_name' => 'Create Requisitions', 'module' => 'inventory', 'action' => 'create'],
            ['name' => 'view_requisitions', 'display_name' => 'View Requisitions', 'module' => 'inventory', 'action' => 'read'],
            ['name' => 'adjust_stock', 'display_name' => 'Adjust Stock', 'module' => 'inventory', 'action' => 'update'],
            ['name' => 'view_stock_levels', 'display_name' => 'View Stock Levels', 'module' => 'inventory', 'action' => 'read'],
            ['name' => 'view_inventory_reports', 'display_name' => 'View Inventory Reports', 'module' => 'inventory', 'action' => 'read'],
        ];

        $this->insertPermissions($permissions);
    }

    private function createHRPermissions(): void
    {
        $permissions = [
            ['name' => 'view_hr', 'display_name' => 'View HR Module', 'module' => 'hr', 'action' => 'read'],
            ['name' => 'manage_employees', 'display_name' => 'Manage Employees', 'module' => 'hr', 'action' => 'create'],
            ['name' => 'view_employees', 'display_name' => 'View Employees', 'module' => 'hr', 'action' => 'read'],
        ];

        $this->insertPermissions($permissions);
    }

    private function insertPermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                array_merge($permission, ['active' => true])
            );
        }
    }
}
