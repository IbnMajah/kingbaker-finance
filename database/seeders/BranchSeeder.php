<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Turntable Branch',
                'location' => 'Turntable Industrial Area',
                'description' => 'Main production facility and headquarters',
                'active' => true,
            ],
            [
                'name' => 'Senegambia Branch',
                'location' => 'Senegambia Area',
                'description' => 'Retail outlet and distribution center',
                'active' => true,
            ],
            [
                'name' => 'Traffic Light Branch',
                'location' => 'Traffic Light Area',
                'description' => 'Capital city retail outlet',
                'active' => true,
            ],
            [
                'name' => 'Jeshwang Branch',
                'location' => 'Jeshwang Area',
                'description' => 'Strategic location for western area coverage',
                'active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $this->command->info('Branches seeded successfully!');
    }
}
