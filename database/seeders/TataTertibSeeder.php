<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TataTertibSeeder extends Seeder
{
    public function run(): void
    {
        $tataTeribData = [
            [
                'jalur_id' => 1, // Jalur Selo
                'description' => '1. Wajib registrasi dan membayar tiket masuk di Pos
2. Wajib membawa minimal 2 liter air per pendaki
3. Dilarang membawa minuman beralkohol
4. Wajib membawa surat keterangan sehat
5. Dilarang membuat api unggun sembarangan
6. Wajib membawa kembali sampah masing-masing
7. Dilarang mengambil flora dan fauna
8. Wajib mengikuti briefing keselamatan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 2, // Jalur Cuntel
                'description' => '1. Pendaftaran wajib dilakukan H-1 pendakian
2. Wajib membawa peralatan standar pendakian
3. Dilarang mendaki saat hujan lebat
4. Wajib membawa perlengkapan P3K
5. Dilarang membuang sampah sembarangan
6. Wajib lapor ke petugas saat turun gunung
7. Dilarang membawa speaker/alat musik keras
8. Wajib berkemah di area yang ditentukan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 3, // Jalur Suwanting
                'description' => '1. Wajib mengisi buku pendakian
2. Minimal pendakian 2 orang
3. Wajib membawa lampu senter/headlamp
4. Dilarang memetik atau merusak tanaman
5. Wajib membawa jas hujan
6. Dilarang meninggalkan sampah di area kemah
7. Wajib mengikuti jalur yang sudah ada
8. Dilarang membawa hewan peliharaan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 4, // Jalur Thekelan
                'description' => '1. Wajib menggunakan sepatu gunung
2. Dilarang mendaki di atas jam 17.00
3. Wajib membawa tenda yang layak
4. Dilarang menyalakan api di dalam tenda
5. Wajib membawa pakaian hangat
6. Dilarang berteriak di malam hari
7. Wajib membawa logistik yang cukup
8. Dilarang memotong jalur pendakian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 5, // Jalur Wekas
                'description' => '1. Wajib membawa kartu identitas asli
2. Dilarang membawa senjata tajam
3. Wajib membawa cadangan baterai
4. Dilarang menggunakan drone tanpa izin
5. Wajib mematuhi marka jalur
6. Dilarang mengambil foto di area berbahaya
7. Wajib membawa masker
8. Dilarang membuat vandalisme',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 6, // Jalur Bambangan
                'description' => '1. Wajib menggunakan GPS/kompas
2. Dilarang membawa peralatan berat
3. Wajib membawa kantong sampah
4. Dilarang berkemah di jalur evakuasi
5. Wajib mematuhi rambu peringatan
6. Dilarang merokok di area hutan
7. Wajib membawa peluit darurat
8. Dilarang memindahkan tanda jalur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 7, // Jalur Kaliwadas
                'description' => '1. Pendakian maksimal 5 hari
2. Wajib membawa ponsel darurat
3. Dilarang membuat grafiti
4. Wajib mematuhi kuota pendaki
5. Dilarang membawa kendaraan bermotor
6. Wajib mengikuti arahan SAR
7. Dilarang membuat keributan
8. Wajib menghormati pendaki lain',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 8, // Jalur Guci
                'description' => '1. Wajib melapor di setiap pos
2. Dilarang membawa gerobak/trolley
3. Wajib membawa alat masak sendiri
4. Dilarang mengganggu satwa liar
5. Wajib mematikan api unggun setelah use
6. Dilarang mencuci di sumber air minum
7. Wajib mengikuti ritual adat setempat
8. Dilarang membawa kayu dari luar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 9, // Jalur Dipajaya
                'description' => '1. Wajib membawa rencana perjalanan
2. Dilarang mendaki saat upacara adat
3. Wajib membawa alat komunikasi
4. Dilarang membuat api di musim kemarau
5. Wajib menggunakan matras
6. Dilarang buang air sembarangan
7. Wajib membawa obat pribadi
8. Dilarang membawa minuman sachet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 10, // Jalur Baturraden
                'description' => '1. Wajib lapor kondisi kesehatan
2. Dilarang membawa peralatan berat
3. Wajib membawa peralatan masak gas
4. Dilarang bermain kartu/judi
5. Wajib menjaga jarak aman api
6. Dilarang membawa tumbuhan dari luar
7. Wajib membawa tracking pole
8. Dilarang menggunakan sabun di sungai',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 11, // Jalur Mangli
                'description' => '1. Wajib mengikuti briefing
2. Dilarang mendaki saat badai
3. Wajib membawa sleeping bag
4. Dilarang membawa mainan drone
5. Wajib menggunakan matras
6. Dilarang membuang puntung rokok
7. Wajib membawa peluit
8. Dilarang mendaki sendiri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 12, // Jalur Gajah Mungkur
                'description' => '1. Wajib membawa asuransi pendakian
2. Dilarang mendaki di atas kapasitas
3. Wajib membawa tali cadangan
4. Dilarang membawa radio besar
5. Wajib memakai pakaian terang
6. Dilarang membuat api di shelter
7. Wajib membawa peta jalur
8. Dilarang mengambil batu/pasir',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 13, // Jalur Cepit Parakan
                'description' => '1. Wajib registrasi online H-3
2. Dilarang membawa kamera drone
3. Wajib membawa jaket waterproof
4. Dilarang mendaki malam hari
5. Wajib membawa multi-tool
6. Dilarang membuang sampah organik
7. Wajib membawa raincover
8. Dilarang membawa peralatan mining',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 14, // Jalur Bowongso
                'description' => '1. Wajib membawa surat izin dokter
2. Dilarang mendaki saat ritual desa
3. Wajib membawa oxygen portable
4. Dilarang membawa generator listrik
5. Wajib menggunakan carrier standar
6. Dilarang membuat api di summit
7. Wajib membawa survival kit
8. Dilarang mengganggu peneliti',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jalur_id' => 15, // Jalur Garung
                'description' => '1. Wajib mengikuti SOP COVID-19
2. Dilarang membawa peralatan tambang
3. Wajib membawa masker cadangan
4. Dilarang mendaki di atas kuota
5. Wajib membawa kompas
6. Dilarang membuat jalan pintas
7. Wajib lapor turun gunung
8. Dilarang merusak marka jalur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tata_tertibs')->insert($tataTeribData);
    }
}