<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mountain;
use App\Models\Trail;
use App\Models\User;

class TrailSeeder extends Seeder
{
    public function run()
    {
        // Get mountain data
        $mountain1 = Mountain::find(1); // Mount Merbabu
        $mountain2 = Mountain::find(2); // Mount Slamet
        $mountain3 = Mountain::find(3); // Mount Sumbing

        // Map penjaga jalur by email to avoid mismatch when user order changes.
        $guardsByEmail = User::where('level', 2)->pluck('id', 'email');

        // Add trail data
        Trail::create([
            'nama' => 'Jalur Selo',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5.60',
            'deskripsi' => 'Jalur pendakian melalui Selo',
            'map_basecamp' => 'https://maps.app.goo.gl/Mn3kiXcKEmtoqdqc6',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 20000,
            'id_gunung' => $mountain1->id,
            'user_id' => $guardsByEmail->get('penjaga.selo@myhiking.com'),
            'latitude' => -7.4553,
            'longitude' => 110.4394,
        ]);

        Trail::create([
            'nama' => 'Jalur Cuntel',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152008',
            'jarak' => '6.43',
            'deskripsi' => 'Jalur pendakian melalui Cuntel',
            'map_basecamp' => 'https://maps.app.goo.gl/MTZNm1wKzJLRdrwb9',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 20000,
            'id_gunung' => $mountain1->id,
            'user_id' => $guardsByEmail->get('penjaga.cuntel@myhiking.com'),
            'latitude' => -7.4563,
            'longitude' => 110.4404,
        ]);

        Trail::create([
            'nama' => 'Jalur Suwanting',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '6.56',
            'deskripsi' => 'Jalur pendakian melalui Suwanting',
            'map_basecamp' => 'https://maps.app.goo.gl/WBQGj1Z8eWiLvQpC8',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 20000,
            'id_gunung' => $mountain1->id,
            'user_id' => $guardsByEmail->get('penjaga.suwanting@myhiking.com'),
            'latitude' => -7.4543,
            'longitude' => 110.4384,
        ]);

        Trail::create([
            'nama' => 'Jalur Thekelan',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '6.90',
            'deskripsi' => 'Jalur pendakian melalui Thekelan',
            'map_basecamp' => 'https://maps.app.goo.gl/MZqsJix5HKBzqohFA',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 20000,
            'user_id' => $guardsByEmail->get('penjaga.thekelan@myhiking.com'),
            'id_gunung' => $mountain1->id,
            'latitude' => -7.4533,
            'longitude' => 110.4414,
        ]);

        Trail::create([
            'nama' => 'Jalur Wekas',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5.60',
            'deskripsi' => 'Jalur pendakian melalui Wekas',
            'map_basecamp' => 'https://maps.app.goo.gl/T8exLZEpHB5MJmYo9',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 20000,
            'user_id' => $guardsByEmail->get('penjaga.wekas@myhiking.com'),
            'id_gunung' => $mountain1->id,
            'latitude' => -7.4573,
            'longitude' => 110.4374,
        ]);

        Trail::create([
            'nama' => 'Jalur Bambangan',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '6.20',
            'deskripsi' => 'Jalur pendakian melalui Bambangan',
            'map_basecamp' => 'https://maps.app.goo.gl/dXWsBUbJ7nscW1Ug7',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'biaya' => 25000,
            'user_id' => $guardsByEmail->get('penjaga.bambangan@myhiking.com'),
            'id_gunung' => $mountain2->id,
            'latitude' => -7.2426,
            'longitude' => 109.2083,
        ]);

        Trail::create([
            'nama' => 'Jalur Kaliwadas',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152008',
            'jarak' => '11.00',
            'deskripsi' => 'Jalur pendakian melalui Kaliwadas',
            'map_basecamp' => 'https://maps.app.goo.gl/pC7NcEiTYTRPEPH19',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'biaya' => 35000,
            'user_id' => $guardsByEmail->get('penjaga.kaliwadas@myhiking.com'),
            'id_gunung' => $mountain2->id,
            'latitude' => -7.2436,
            'longitude' => 109.2093,
        ]);

        Trail::create([
            'nama' => 'Jalur Guci',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '9.95',
            'deskripsi' => 'Jalur pendakian melalui Guci',
            'map_basecamp' => 'https://maps.app.goo.gl/qHCr9D4q1yq4fWNg8',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'user_id' => $guardsByEmail->get('penjaga.guci@myhiking.com'),
            'biaya' => 35000,
            'id_gunung' => $mountain2->id,
            'latitude' => -7.2416,
            'longitude' => 109.2073,
        ]);

        Trail::create([
            'nama' => 'Jalur Dipajaya',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '6.28',
            'deskripsi' => 'Jalur pendakian melalui Dipajaya',
            'map_basecamp' => 'https://maps.app.goo.gl/MmqKGYuSdzQ1Xyut8',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'user_id' => $guardsByEmail->get('penjaga.dipajaya@myhiking.com'),
            'biaya' => 30000,
            'id_gunung' => $mountain2->id,
            'latitude' => -7.2406,
            'longitude' => 109.2103,
        ]);

        Trail::create([
            'nama' => 'Jalur Baturraden',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '7.61',
            'deskripsi' => 'Jalur pendakian melalui Baturraden',
            'map_basecamp' => 'https://maps.app.goo.gl/95W2evfaFubNTX9N6',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'user_id' => $guardsByEmail->get('penjaga.baturraden@myhiking.com'),
            'biaya' => 50000,
            'id_gunung' => $mountain2->id,
            'latitude' => -7.2446,
            'longitude' => 109.2063,
        ]);

        Trail::create([
            'nama' => 'Jalur Mangli',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '7.00',
            'deskripsi' => 'Jalur pendakian melalui Mangli',
            'map_basecamp' => 'https://maps.app.goo.gl/PTxfDKtt8ArNvXBLA',
            'user_id' => $guardsByEmail->get('penjaga.mangli@myhiking.com'),
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 35000,
            'id_gunung' => $mountain3->id,
            'latitude' => -7.3833,
            'longitude' => 110.0708,
        ]);

        Trail::create([
            'nama' => 'Jalur Gajah Mungkur',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152008',
            'jarak' => '8.28',
            'deskripsi' => 'Jalur pendakian melalui Gajah Mungkur',
            'map_basecamp' => 'https://maps.app.goo.gl/G4VJ5u6R2GkfQBAD8',
            'user_id' => $guardsByEmail->get('penjaga.gajahmungkur@myhiking.com'),
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 35000,
            'id_gunung' => $mountain3->id,
            'latitude' => -7.3843,
            'longitude' => 110.0718,
        ]);

        Trail::create([
            'nama' => 'Jalur Cepit Parakan',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '8.50',
            'deskripsi' => 'Jalur pendakian melalui Cepit Parakan',
            'map_basecamp' => 'https://maps.app.goo.gl/jg3MHesSX6tGzpsJ6',
            'user_id' => $guardsByEmail->get('penjaga.cepitparakan@myhiking.com'),
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 35000,
            'id_gunung' => $mountain3->id,
            'latitude' => -7.3823,
            'longitude' => 110.0698,
        ]);

        Trail::create([
            'nama' => 'Jalur Bowongso',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '7.77',
            'deskripsi' => 'Jalur pendakian melalui Bowongso',
            'user_id' => $guardsByEmail->get('penjaga.bowongso@myhiking.com'),
            'map_basecamp' => 'https://maps.app.goo.gl/WmQuVz7adzsxnmVv5',
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 35000,
            'id_gunung' => $mountain3->id,
            'latitude' => -7.3813,
            'longitude' => 110.0728,
        ]);

        Trail::create([
            'nama' => 'Jalur Garung',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '4.23',
            'deskripsi' => 'Jalur pendakian melalui Garung',
            'user_id' => $guardsByEmail->get('penjaga.garung@myhiking.com'),
            'map_basecamp' => 'https://maps.app.goo.gl/MZ6dt5YVxnbsQM5c7',
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 35000,
            'id_gunung' => $mountain3->id,
            'latitude' => -7.3853,
            'longitude' => 110.0688,
        ]);

        // Pastikan metrik rute konsisten untuk DSS (sesuai data acuan).
        $routeMetrics = [
            1 => [
                'jarak' => 5.60, 'elevasi' => 1300, 'durasi' => 8, 'tingkat_kesulitan' => 'sedang',
                'panorama_score' => 5, 'fasilitas_score' => 4, 'popularity_score' => 850, 'safety_score' => 4, 'crowd_level' => 3
            ],
            2 => [
                'jarak' => 6.43, 'elevasi' => 1400, 'durasi' => 10, 'tingkat_kesulitan' => 'sedang',
                'panorama_score' => 4, 'fasilitas_score' => 3, 'popularity_score' => 420, 'safety_score' => 4, 'crowd_level' => 2
            ],
            3 => [
                'jarak' => 6.56, 'elevasi' => 1500, 'durasi' => 10, 'tingkat_kesulitan' => 'sulit',
                'panorama_score' => 5, 'fasilitas_score' => 2, 'popularity_score' => 310, 'safety_score' => 3, 'crowd_level' => 2
            ],
            4 => [
                'jarak' => 6.90, 'elevasi' => 1400, 'durasi' => 9, 'tingkat_kesulitan' => 'sedang',
                'panorama_score' => 4, 'fasilitas_score' => 3, 'popularity_score' => 250, 'safety_score' => 4, 'crowd_level' => 1
            ],
            5 => [
                'jarak' => 5.60, 'elevasi' => 1200, 'durasi' => 8, 'tingkat_kesulitan' => 'sedang',
                'panorama_score' => 3, 'fasilitas_score' => 3, 'popularity_score' => 180, 'safety_score' => 5, 'crowd_level' => 1
            ],
            6 => [
                'jarak' => 6.20, 'elevasi' => 1500, 'durasi' => 9, 'tingkat_kesulitan' => 'sulit',
                'panorama_score' => 5, 'fasilitas_score' => 5, 'popularity_score' => 950, 'safety_score' => 3, 'crowd_level' => 5
            ],
            7 => [
                'jarak' => 11.00, 'elevasi' => 1600, 'durasi' => 10, 'tingkat_kesulitan' => 'sulit',
                'panorama_score' => 4, 'fasilitas_score' => 3, 'popularity_score' => 380, 'safety_score' => 3, 'crowd_level' => 2
            ],
            8 => [
                'jarak' => 9.95, 'elevasi' => 1400, 'durasi' => 8, 'tingkat_kesulitan' => 'sulit',
                'panorama_score' => 4, 'fasilitas_score' => 4, 'popularity_score' => 560, 'safety_score' => 4, 'crowd_level' => 3
            ],
            9 => [
                'jarak' => 6.28, 'elevasi' => 1300, 'durasi' => 7, 'tingkat_kesulitan' => 'sedang',
                'panorama_score' => 3, 'fasilitas_score' => 3, 'popularity_score' => 290, 'safety_score' => 4, 'crowd_level' => 2
            ],
            10 => [
                'jarak' => 7.61, 'elevasi' => 1700, 'durasi' => 11, 'tingkat_kesulitan' => 'sangat_sulit',
                'panorama_score' => 5, 'fasilitas_score' => 4, 'popularity_score' => 410, 'safety_score' => 2, 'crowd_level' => 4
            ],
            11 => [
                'jarak' => 7.00, 'elevasi' => 1300, 'durasi' => 7, 'tingkat_kesulitan' => 'sedang',
                'panorama_score' => 4, 'fasilitas_score' => 4, 'popularity_score' => 620, 'safety_score' => 5, 'crowd_level' => 3
            ],
            12 => [
                'jarak' => 8.28, 'elevasi' => 1500, 'durasi' => 9, 'tingkat_kesulitan' => 'sulit',
                'panorama_score' => 5, 'fasilitas_score' => 2, 'popularity_score' => 150, 'safety_score' => 3, 'crowd_level' => 1
            ],
            13 => [
                'jarak' => 8.50, 'elevasi' => 1400, 'durasi' => 8, 'tingkat_kesulitan' => 'sulit',
                'panorama_score' => 4, 'fasilitas_score' => 2, 'popularity_score' => 120, 'safety_score' => 4, 'crowd_level' => 1
            ],
            14 => [
                'jarak' => 7.77, 'elevasi' => 1400, 'durasi' => 8, 'tingkat_kesulitan' => 'sulit',
                'panorama_score' => 4, 'fasilitas_score' => 3, 'popularity_score' => 200, 'safety_score' => 4, 'crowd_level' => 2
            ],
            15 => [
                'jarak' => 4.23, 'elevasi' => 1200, 'durasi' => 7, 'tingkat_kesulitan' => 'sedang',
                'panorama_score' => 3, 'fasilitas_score' => 5, 'popularity_score' => 780, 'safety_score' => 5, 'crowd_level' => 4
            ],
        ];

        foreach ($routeMetrics as $routeId => $metrics) {
            Trail::where('id', $routeId)->update($metrics);
        }
    }
}
