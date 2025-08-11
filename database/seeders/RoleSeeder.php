<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Full access to all modules and system administration',
                'active' => true,
            ],
            [
                'name' => 'finance_manager',
                'display_name' => 'Finance Manager',
                'description' => 'Full access to finance module with approval and reconciliation rights',
                'active' => true,
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Accountant',
                'description' => 'Limited access to finance module without approval rights',
                'active' => true,
            ],
            [
                'name' => 'sales_staff',
                'display_name' => 'Sales Staff',
                'description' => 'Access to sales module only for daily sales entry',
                'active' => true,
            ],
            [
                'name' => 'inventory_supervisor',
                'display_name' => 'Inventory Supervisor',
                'description' => 'Full access to production and inventory management',
                'active' => true,
            ],
            [
                'name' => 'procurement_staff',
                'display_name' => 'Procurement Staff',
                'description' => 'Access to inventory and vendor management',
                'active' => true,
            ],
            [
                'name' => 'auditor',
                'display_name' => 'Auditor',
                'description' => 'Read-only access to finance, inventory, and sales data',
                'active' => true,
            ],
            [
                'name' => 'branch_supervisor',
                'display_name' => 'Branch Supervisor',
                'description' => 'Limited access to branch-specific operations',
                'active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}