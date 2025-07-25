<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
		'account_id' => 1,
                'first_name' => 'Isatou',
                'last_name' => 'Demba',
                'email' => 'isatou@kingbaker.com',
                'password' => Hash::make('password'),
                'owner' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
