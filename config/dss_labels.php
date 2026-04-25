<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DSS Criteria Score Labels
    |--------------------------------------------------------------------------
    |
    | Descriptive labels for each integer score (1-5) per criterion.
    | Used in Blade forms to guide penjaga_jalur when selecting values.
    |
    */

    'panorama_score' => [
        1 => 'Sangat Buruk – hampir tidak ada pemandangan',
        2 => 'Buruk – pemandangan terbatas',
        3 => 'Cukup – pemandangan rata-rata',
        4 => 'Baik – pemandangan indah',
        5 => 'Sangat Indah – pemandangan luar biasa / summit view',
    ],

    'fasilitas_score' => [
        1 => 'Sangat Minim – tidak ada fasilitas',
        2 => 'Minim – hanya jalur dasar',
        3 => 'Cukup – toilet / warung tersedia',
        4 => 'Lengkap – toilet, shelter, warung, dll.',
        5 => 'Sangat Lengkap – fasilitas modern tersedia',
    ],

    'safety_score' => [
        1 => 'Sangat Berisiko – banyak bahaya / medan ekstrem',
        2 => 'Berisiko – ada titik berbahaya',
        3 => 'Cukup Aman – risiko normal',
        4 => 'Aman – jalur terkelola dengan baik',
        5 => 'Sangat Aman – jalur terpantau & berstandar',
    ],

    'crowd_level' => [
        1 => 'Sangat Sepi – hampir tidak ada pengunjung',
        2 => 'Sepi – jumlah pengunjung sedikit',
        3 => 'Sedang – cukup ramai di akhir pekan',
        4 => 'Ramai – selalu banyak pengunjung',
        5 => 'Sangat Padat – overloaded saat peak season',
    ],

];
