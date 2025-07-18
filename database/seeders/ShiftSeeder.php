<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'description' => 'Morning production and early sales shift',
                'active' => true,
            ],
            [
                'name' => 'Afternoon Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'description' => 'Afternoon and evening sales shift',
                'active' => true,
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'description' => 'Night shift for 24/7 operations',
                'active' => true,
            ],
            [
                'name' => 'Weekend Shift',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
                'description' => 'Extended weekend shift for busy periods',
                'active' => true,
            ],
            [
                'name' => 'Holiday Shift',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'description' => 'Reduced hours for public holidays',
                'active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }

        $this->command->info('Shifts seeded successfully!');
    }
}