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

        // Trail Guards (Level 2) - Penjaga untuk setiap jalur
        // Merbabu Trails
        User::create([
            'name' => 'Penjaga Jalur Selo',
            'email' => 'penjaga.selo@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Selo No. 1',
            'nik' => 2111111111111111,
            'phone' => 628211111111,
            'emergency_phone' => 628911111111,
            'date_of_birth' => '1992-05-15',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Cuntel',
            'email' => 'penjaga.cuntel@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Cuntel No. 2',
            'nik' => 2122222222222222,
            'phone' => 628222222222,
            'emergency_phone' => 628922222222,
            'date_of_birth' => '1993-06-20',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Suwanting',
            'email' => 'penjaga.suwanting@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Suwanting No. 3',
            'nik' => 2133333333333333,
            'phone' => 628233333333,
            'emergency_phone' => 628933333333,
            'date_of_birth' => '1994-07-25',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Thekelan',
            'email' => 'penjaga.thekelan@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Thekelan No. 4',
            'nik' => 2144444444444444,
            'phone' => 628244444444,
            'emergency_phone' => 628944444444,
            'date_of_birth' => '1995-08-30',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Wekas',
            'email' => 'penjaga.wekas@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Wekas No. 5',
            'nik' => 2155555555555555,
            'phone' => 628255555555,
            'emergency_phone' => 628955555555,
            'date_of_birth' => '1996-09-10',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Bambangan',
            'email' => 'penjaga.bambangan@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Bambangan No. 6',
            'nik' => 2166666666666666,
            'phone' => 628266666666,
            'emergency_phone' => 628966666666,
            'date_of_birth' => '1997-10-15',
            'email_verified_at' => now(),
        ]);

        // Slamet Trails
        User::create([
            'name' => 'Penjaga Jalur Kaliwadas',
            'email' => 'penjaga.kaliwadas@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Kaliwadas No. 7',
            'nik' => 2177777777777777,
            'phone' => 628277777777,
            'emergency_phone' => 628977777777,
            'date_of_birth' => '1988-11-20',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Guci',
            'email' => 'penjaga.guci@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Guci No. 8',
            'nik' => 2188888888888888,
            'phone' => 628288888888,
            'emergency_phone' => 628988888888,
            'date_of_birth' => '1989-12-25',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Dipajaya',
            'email' => 'penjaga.dipajaya@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Dipajaya No. 9',
            'nik' => 2199999999999999,
            'phone' => 628299999999,
            'emergency_phone' => 628999999999,
            'date_of_birth' => '1991-01-30',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Baturraden',
            'email' => 'penjaga.baturraden@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Baturraden No. 10',
            'nik' => 2200000000000000,
            'phone' => 628200000000,
            'emergency_phone' => 628900000000,
            'date_of_birth' => '1993-02-05',
            'email_verified_at' => now(),
        ]);

        // Sumbing Trails
        User::create([
            'name' => 'Penjaga Jalur Mangli',
            'email' => 'penjaga.mangli@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Mangli No. 11',
            'nik' => 2211111111111111,
            'phone' => 628211111112,
            'emergency_phone' => 628911111112,
            'date_of_birth' => '1994-03-10',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Gajah Mungkur',
            'email' => 'penjaga.gajahmungkur@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Gajah Mungkur No. 12',
            'nik' => 2222222222222222,
            'phone' => 628222222223,
            'emergency_phone' => 628922222223,
            'date_of_birth' => '1995-04-15',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Cepit Parakan',
            'email' => 'penjaga.cepitparakan@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Cepit Parakan No. 13',
            'nik' => 2233333333333333,
            'phone' => 628233333334,
            'emergency_phone' => 628933333334,
            'date_of_birth' => '1996-05-20',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Bowongso',
            'email' => 'penjaga.bowongso@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Bowongso No. 14',
            'nik' => 2244444444444444,
            'phone' => 628244444445,
            'emergency_phone' => 628944444445,
            'date_of_birth' => '1997-06-25',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Penjaga Jalur Garung',
            'email' => 'penjaga.garung@myhiking.com',
            'password' => Hash::make('password123'),
            'level' => 2,
            'address' => 'Jl. Garung No. 15',
            'nik' => 2255555555555555,
            'phone' => 628255555556,
            'emergency_phone' => 628955555556,
            'date_of_birth' => '1998-07-30',
            'email_verified_at' => now(),
        ]);

        // Regular Users (Level 1)
        User::create([
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
