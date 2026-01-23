<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gunung;
use App\Models\Jalur;

class JalurSeeder extends Seeder
{
    public function run()
    {
        // Mendapatkan data gunung berdasarkan ID atau kriteria lainnya
        $gunung1 = Gunung::find(1); // Misal Gunung Merbabu
        $gunung2 = Gunung::find(2); // Misal Gunung Slamet
        $gunung3 = Gunung::find(3); // Misal Gunung Sumbing
        // Menambahkan data jalur
        $jalur1 = Jalur::create([
            'nama' => 'Jalur Selo',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Selo',
            'map_basecamp' => 'https://maps.app.goo.gl/Mn3kiXcKEmtoqdqc6',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung1->id,  // Mengaitkan dengan Gunung Merbabu
        ]);

        $jalur2 = Jalur::create([
            'nama' => 'Jalur Cuntel',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152008',
            'jarak' => '6',
            'deskripsi' => 'Jalur pendakian melalui Cuntel',
            'map_basecamp' => 'https://maps.app.goo.gl/MTZNm1wKzJLRdrwb9',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 20000,
            'id_gunung' => $gunung1->id,  // Mengaitkan dengan Gunung Merbabu
        ]);
        $jalur3 = Jalur::create([
            'nama' => 'Jalur Suwanting',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Suwanting',
            'map_basecamp' => 'https://maps.app.goo.gl/WBQGj1Z8eWiLvQpC8',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung1->id,  // Mengaitkan dengan Gunung Merbabu
        ]);
        $jalur4 = Jalur::create([
            'nama' => 'Jalur Thekelan',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Thekelan',
            'map_basecamp' => 'https://maps.app.goo.gl/MZqsJix5HKBzqohFA',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung1->id,  // Mengaitkan dengan Gunung Merbabu
        ]);
        $jalur5 = Jalur::create([
            'nama' => 'Jalur Wekas',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '4',
            'deskripsi' => 'Jalur pendakian melalui Wekas',
            'map_basecamp' => 'https://maps.app.goo.gl/T8exLZEpHB5MJmYo9',
            'gambar_jalur' => 'img_image_merbabu_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung1->id,  // Mengaitkan dengan Gunung Merbabu
        ]);
        $jalur6 = Jalur::create([
            'nama' => 'Jalur Bambangan',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Bambangan',
            'map_basecamp' => 'https://maps.app.goo.gl/dXWsBUbJ7nscW1Ug7',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'biaya' => 15000,
            'id_gunung' => $gunung2->id,  // Mengaitkan dengan Gunung Slamet
        ]);

        $jalur7 = Jalur::create([
            'nama' => 'Jalur Kaliwadas',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152008',
            'jarak' => '6',
            'deskripsi' => 'Jalur pendakian melalui Kaliwadas',
            'map_basecamp' => 'https://maps.app.goo.gl/pC7NcEiTYTRPEPH19',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'biaya' => 20000,
            'id_gunung' => $gunung2->id,  // Mengaitkan dengan Gunung Slamet
        ]);
        $jalur8 = Jalur::create([
            'nama' => 'Jalur Guci',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Bambangan',
            'map_basecamp' => 'https://maps.app.goo.gl/qHCr9D4q1yq4fWNg8',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'biaya' => 15000,
            'id_gunung' => $gunung2->id,  // Mengaitkan dengan Gunung Slamet
        ]);
        $jalur9 = Jalur::create([
            'nama' => 'Jalur Dipajaya',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Bambangan',
            'map_basecamp' => 'https://maps.app.goo.gl/MmqKGYuSdzQ1Xyut8',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'biaya' => 15000,
            'id_gunung' => $gunung2->id,  // Mengaitkan dengan Gunung Slamet
        ]);
        $jalur10 = Jalur::create([
            'nama' => 'Jalur Baturraden',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '4',
            'deskripsi' => 'Jalur pendakian melalu Baturaden',
            'map_basecamp' => 'https://maps.app.goo.gl/95W2evfaFubNTX9N6',
            'gambar_jalur' => 'img_image_slamet_jalur.png',
            'biaya' => 15000,
            'id_gunung' => $gunung2->id,  // Mengaitkan dengan Gunung Slamet
        ]);
        $jalur11 = Jalur::create([
            'nama' => 'Jalur Mangli',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Mangli',
            'map_basecamp' => 'https://maps.app.goo.gl/PTxfDKtt8ArNvXBLA',
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung3->id,  // Mengaitkan dengan Gunung Sumbing
        ]);

        $jalur12 = Jalur::create([
            'nama' => 'Jalur Gajah Mungkur',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152008',
            'jarak' => '6',
            'deskripsi' => 'Jalur pendakian melalui Gajah Mungkur',
            'map_basecamp' => 'https://maps.app.goo.gl/G4VJ5u6R2GkfQBAD8',
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 20000,
            'id_gunung' => $gunung3->id,  // Mengaitkan dengan Gunung Sumbing
        ]);
        $jalur13 = Jalur::create([
            'nama' => 'Jalur Cepit Parakan',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Cepit Parakan',
            'map_basecamp' => 'https://maps.app.goo.gl/jg3MHesSX6tGzpsJ6',
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung3->id,  // Mengaitkan dengan Gunung Sumbing
        ]);
        $jalur14 = Jalur::create([
            'nama' => 'Jalur Bowongso',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '5',
            'deskripsi' => 'Jalur pendakian melalui Bowongso',
            'map_basecamp' => 'https://maps.app.goo.gl/WmQuVz7adzsxnmVv5',
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung3->id,  // Mengaitkan dengan Gunung Sumbing
        ]);
        $jalur15 = Jalur::create([
            'nama' => 'Jalur Garung',
            'province_id' => '33',
            'regency_id' => '3328',
            'district_id' => '332815',
            'village_id' => '3328152007',
            'jarak' => '4',
            'deskripsi' => 'Jalur pendakian melalui Garung',
            'map_basecamp' => 'https://maps.app.goo.gl/MZ6dt5YVxnbsQM5c7',
            'gambar_jalur' => 'img_image_sumbing_jalur.jpg',
            'biaya' => 15000,
            'id_gunung' => $gunung3->id,  // Mengaitkan dengan Gunung Sumbing
        ]);

        // // Mengaitkan jalur dengan gunung menggunakan relasi pivot

        // $gunung1->jalur()->attach([$jalur1->id, $jalur2->id, $jalur3->id, $jalur4->id, $jalur5->id]);
        // $gunung2->jalur()->attach([$jalur6->id, $jalur7->id, $jalur8->id, $jalur9->id, $jalur10->id]);
        // $gunung3->jalur()->attach([$jalur11->id, $jalur12->id, $jalur13->id, $jalur14->id, $jalur15->id]);
    }
}
