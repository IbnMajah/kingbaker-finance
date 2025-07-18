<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            [
                'account_id' => 1,
                'name' => 'King Bakers',
                'email' => 'info@kingbakers.com',
                'phone' => '+1234567890',
                'address' => '123 Main Street',
                'city' => 'Cebu City',
                'region' => 'Cebu',
                'country' => 'PH',
                'postal_code' => '6000',
            ],
        ];

        foreach ($organizations as $organization) {
            Organization::create($organization);
        }
    }
}