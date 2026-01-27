<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User (Level 3)
        User::create([
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@myhiking.com',
            'password' => Hash::make('admin123'),
            'level' => 3,
            'address' => 'Jl. Admin No. 1',
            'nik' => 1234567890123456,
            'phone' => 628123456789,
            'emergency_phone' => 628987654321,
            'date_of_birth' => '1990-01-01',
            'email_verified_at' => now(),
        ]);

        // User Level 2
        User::create([
            'id' => 2,
            'name' => 'Manager',
            'email' => 'manager@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Manager No. 2',
            'nik' => 2234567890123456,
            'phone' => 628223456789,
            'emergency_phone' => 628887654321,
            'date_of_birth' => '1992-05-15',
            'email_verified_at' => now(),
        ]);

        // Regular User (Level 1)
        User::create([
            'id' => 3,
            'name' => 'Pendaki Satu',
            'email' => 'pendaki1@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 1,
            'address' => 'Jl. Pendaki No. 3',
            'nik' => 3234567890123456,
            'phone' => 628323456789,
            'emergency_phone' => 628787654321,
            'date_of_birth' => '1995-08-20',
            'email_verified_at' => now(),
        ]);

        // Regular User (Level 1)
        User::create([
            'id' => 4,
            'name' => 'Pendaki Dua',
            'email' => 'pendaki2@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 1,
            'address' => 'Jl. Pendaki No. 4',
            'nik' => 4234567890123456,
            'phone' => 628423456789,
            'emergency_phone' => 628687654321,
            'date_of_birth' => '1997-12-10',
            'email_verified_at' => now(),
        ]);

        // Regular User (Level 1)
        User::create([
            'id' => 5,
            'name' => 'Pendaki Tiga',
            'email' => 'pendaki3@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 1,
            'address' => 'Jl. Pendaki No. 5',
            'nik' => 5234567890123456,
            'phone' => 628523456789,
            'emergency_phone' => 628587654321,
            'date_of_birth' => '1998-03-25',
            'email_verified_at' => now(),
        ]);
    }
}
