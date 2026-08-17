<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_two_factor_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('name', 100);
            $table->text('secret');
            $table->timestamp('confirmed_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'name']);
        });

        DB::table('users')
            ->whereNotNull('two_factor_secret')
            ->whereNotNull('two_factor_confirmed_at')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                $now = now();
                $devices = $users->map(fn ($user) => [
                    'user_id' => $user->id,
                    'name' => 'Primary authenticator',
                    'secret' => $user->two_factor_secret,
                    'confirmed_at' => $user->two_factor_confirmed_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('user_two_factor_devices')->insert($devices);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_two_factor_devices');
    }
};
