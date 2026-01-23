<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GunungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('gunung')->insert([
            [
                'nama' => 'Gunung Merbabu',
                'deskripsi' => 'Gunung Merbabu adalah gunung berapi bertipe stratovolcano yang terletak di Jawa Tengah.',
                'gambar_gunung' => 'img_image_merbabu.png',
                'ketinggian' => 3142,
                'province_id' => 33, // Sesuaikan jika ada
                'regency_id' => 3328, // Sesuaikan jika ada
                'district_id' => 332815, // Sesuaikan jika ada
                'village_id' => 3328152007, // Sesuaikan jika ada
            ],
            [
                'nama' => 'Gunung Slamet',
                'deskripsi' => 'Gunung Slamet adalah gunung berapi tertinggi di Jawa Tengah.',
                'gambar_gunung' => 'img_image_slamet.png',
                'ketinggian' => 3428, // Update jika perlu
                'province_id' => 33,
                'regency_id' => 3328,
                'district_id' => 332815,
                'village_id' => 3328152007,
            ],
            [
                'nama' => 'Gunung Sumbing',
                'deskripsi' => 'Gunung Sumbing adalah gunung berapi yang terletak di Jawa Tengah.',
                'gambar_gunung' => 'img_image_sumbing.jpeg',
                'ketinggian' => 3371, // Update jika perlu
                'province_id' => 33,
                'regency_id' => 3328,
                'district_id' => 332815,
                'village_id' => 3328152007,
            ],
        ]);
    }
}
