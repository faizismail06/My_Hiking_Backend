<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('payments')->insert([
            [
                'nama_pembayaran' => 'GoPay',
                'nomor_pembayaran' => '0857' . rand(10000000, 99999999),
                'gambar_pembayaran' => 'payments/img_logo.png',
            ],
            [
                'nama_pembayaran' => 'OVO',
                'nomor_pembayaran' => '0821' . rand(10000000, 99999999),
                'gambar_pembayaran' => 'payments/img_ovo.jpg',
            ],
            [
                'nama_pembayaran' => 'Bank BRI',
                'nomor_pembayaran' => '014' . rand(1000000000, 9999999999),
                'gambar_pembayaran' => 'payments/img_logo_bank_bri.png',
            ],
            [
                'nama_pembayaran' => 'Bank Mandiri',
                'nomor_pembayaran' => '008' . rand(1000000000, 9999999999),
                'gambar_pembayaran' => 'payments/img_mandiri.jpg',
            ],
        ]);
    }
}
