<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MountainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mountains')->insert([
            [
                'nama' => 'Gunung Merbabu',
                'deskripsi' => 'Gunung Merbabu adalah gunung berapi bertipe stratovolcano yang terletak di Jawa Tengah.',
                'gambar_gunung' => 'img_image_merbabu.png',
                'ketinggian' => 3142,
                'province_id' => 33,
                'regency_id' => 3328,
                'district_id' => 332815,
                'village_id' => 3328152007,
                'latitude' => -7.4553,
                'longitude' => 110.4394,
            ],
            [
                'nama' => 'Gunung Slamet',
                'deskripsi' => 'Gunung Slamet adalah gunung berapi tertinggi di Jawa Tengah.',
                'gambar_gunung' => 'img_image_slamet.png',
                'ketinggian' => 3428,
                'province_id' => 33,
                'regency_id' => 3328,
                'district_id' => 332815,
                'village_id' => 3328152007,
                'latitude' => -7.2426,
                'longitude' => 109.2083,
            ],
            [
                'nama' => 'Gunung Sumbing',
                'deskripsi' => 'Gunung Sumbing adalah gunung berapi yang terletak di Jawa Tengah.',
                'gambar_gunung' => 'img_image_sumbing.jpeg',
                'ketinggian' => 3371,
                'province_id' => 33,
                'regency_id' => 3328,
                'district_id' => 332815,
                'village_id' => 3328152007,
                'latitude' => -7.3833,
                'longitude' => 110.0708,
            ],
        ]);
    }
}
