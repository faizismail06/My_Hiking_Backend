<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProvincesTableSeeder::class,
            RegenciesTableSeeder::class,
            DistrictsTableSeeder::class,
            VillagesTableSeeder::class,
            MountainSeeder::class,
            TrailSeeder::class,
            RuleSeeder::class,
            // PaymentsTableSeeder removed - no longer using payments table (Midtrans handles payment methods)
        ]);
    }
}
