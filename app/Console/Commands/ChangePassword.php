<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change-password {email : The email of the user} {password : The new password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change a user\'s password by email';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format.');
            return 1;
        }

        // Validate password length
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return 1;
        }

        // Find the user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Check if user is soft deleted
        if ($user->trashed()) {
            $this->error("User with email '{$email}' is deleted and cannot have their password changed.");
            return 1;
        }

        // Update the password
        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password successfully changed for user: {$user->name} ({$user->email})");

        return 0;
    }
}
