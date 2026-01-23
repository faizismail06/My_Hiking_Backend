<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faker\Factory as Faker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateRandomUser extends Command
{
    protected $signature = 'user:create {count=1}';
    protected $description = 'Create random users';

    public function handle()
    {
        $count = $this->argument('count');
        $faker = Faker::create('id_ID');

        for ($i = 0; $i < $count; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password123'), // atau random password
                // tambahan field lain sesuai kebutuhan
            ]);

            $this->info("User created: {$user->name} ({$user->email})");
        }

        return 0;
    }
}