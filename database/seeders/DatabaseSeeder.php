<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AccountSeeder::class,
            BranchSeeder::class,
            ShiftSeeder::class,
            // BankAccountSeeder::class,
            OrganizationSeeder::class,

            // Role-based permissions system
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,

            UserSeeder::class, // Keep this last so users can be assigned roles
        ]);
    }
}
