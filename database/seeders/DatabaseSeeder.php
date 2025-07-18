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
            UserSeeder::class,
            OrganizationSeeder::class,
            BranchSeeder::class,
            ShiftSeeder::class,
            BankAccountSeeder::class,
        ]);
    }
}
