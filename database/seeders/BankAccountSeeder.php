<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankAccounts = [
            [
                'name' => 'King Bakers Main Account',
                'account_number' => '1001234567890',
                'bank_name' => 'Ecobank Gambia',
                'branch' => 'Kanifing Branch',
                'description' => 'Primary business account for daily operations',
                'opening_balance' => 50000.00,
                'current_balance' => 75500.00,
                'active' => true,
            ],
            [
                'name' => 'King Bakers Savings',
                'account_number' => '2001234567891',
                'bank_name' => 'Standard Chartered Bank Gambia',
                'branch' => 'Banjul Branch',
                'description' => 'Business savings account for reserve funds',
                'opening_balance' => 100000.00,
                'current_balance' => 125000.00,
                'active' => true,
            ],
            [
                'name' => 'Petty Cash Account',
                'account_number' => '3001234567892',
                'bank_name' => 'Access Bank Gambia',
                'branch' => 'Westfield Branch',
                'description' => 'Account for small daily expenses and petty cash',
                'opening_balance' => 5000.00,
                'current_balance' => 3250.00,
                'active' => true,
            ],
            [
                'name' => 'King Bakers USD Account',
                'account_number' => '4001234567893',
                'bank_name' => 'Guaranty Trust Bank Gambia',
                'branch' => 'Serrekunda Branch',
                'description' => 'Foreign currency account for international transactions',
                'opening_balance' => 25000.00,
                'current_balance' => 28750.00,
                'active' => true,
            ],
            [
                'name' => 'Equipment Fund',
                'account_number' => '5001234567894',
                'bank_name' => 'First International Bank',
                'branch' => 'Kairaba Avenue Branch',
                'description' => 'Dedicated account for equipment purchases and upgrades',
                'opening_balance' => 15000.00,
                'current_balance' => 18500.00,
                'active' => true,
            ],
            [
                'name' => 'Branch Expansion Fund',
                'account_number' => '6001234567895',
                'bank_name' => 'Trust Bank Gambia',
                'branch' => 'Bakau Branch',
                'description' => 'Account for funding new branch openings',
                'opening_balance' => 75000.00,
                'current_balance' => 82000.00,
                'active' => true,
            ],
            [
                'name' => 'Old Account - Closed',
                'account_number' => '7001234567896',
                'bank_name' => 'Arab Gambian Islamic Bank',
                'branch' => 'Brusubi Branch',
                'description' => 'Previously used account - now inactive',
                'opening_balance' => 10000.00,
                'current_balance' => 0.00,
                'active' => false,
            ],
            [
                'name' => 'Staff Salary Account',
                'account_number' => '8001234567897',
                'bank_name' => 'Zenith Bank Gambia',
                'branch' => 'Bundung Branch',
                'description' => 'Dedicated account for staff salary payments',
                'opening_balance' => 30000.00,
                'current_balance' => 25000.00,
                'active' => true,
            ],
            [
                'name' => 'Supplier Payments',
                'account_number' => '9001234567898',
                'bank_name' => 'Ecobank Gambia',
                'branch' => 'Pipeline Branch',
                'description' => 'Account used for paying suppliers and vendors',
                'opening_balance' => 20000.00,
                'current_balance' => 15750.00,
                'active' => true,
            ],
            [
                'name' => 'Emergency Fund',
                'account_number' => '1101234567899',
                'bank_name' => 'Standard Chartered Bank Gambia',
                'branch' => 'Kololi Branch',
                'description' => 'Emergency reserve fund for unexpected expenses',
                'opening_balance' => 40000.00,
                'current_balance' => 42500.00,
                'active' => true,
            ],
        ];

        foreach ($bankAccounts as $account) {
            BankAccount::create($account);
        }

        $this->command->info('Bank accounts seeded successfully!');
    }
}
