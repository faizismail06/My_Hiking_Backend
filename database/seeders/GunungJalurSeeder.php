<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GunungJalurSeeder extends Seeder
{
    public function run()
    {
        DB::table('gunung_jalur')->insert([
            ['id_gunung' => 1, 'jalur_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 1, 'jalur_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 1, 'jalur_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 1, 'jalur_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 1, 'jalur_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 2, 'jalur_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 2, 'jalur_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 2, 'jalur_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 2, 'jalur_id' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 2, 'jalur_id' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 3, 'jalur_id' => 11, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 3, 'jalur_id' => 12, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 3, 'jalur_id' => 13, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 3, 'jalur_id' => 14, 'created_at' => now(), 'updated_at' => now()],
            ['id_gunung' => 3, 'jalur_id' => 15, 'created_at' => now(), 'updated_at' => now()],
            
        ]);
    }
}
