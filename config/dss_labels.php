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
        1 => 'Hampir tidak ada pemandangan',
        2 => 'Pemandangan terbatas',
        3 => 'Pemandangan rata-rata',
        4 => 'Pemandangan indah',
        5 => 'Pemandangan luar biasa / summit view',
    ],

    'fasilitas_score' => [
        1 => 'Tidak ada fasilitas',
        2 => 'Hanya jalur dasar',
        3 => 'Toilet / warung tersedia',
        4 => 'Toilet, shelter, warung, dll.',
        5 => 'Fasilitas modern tersedia',
    ],

    'safety_score' => [
        1 => 'Banyak bahaya / medan ekstrem',
        2 => 'Ada titik berbahaya',
        3 => 'Risiko normal',
        4 => 'Jalur terkelola dengan baik',
        5 => 'Jalur terpantau & berstandar',
    ],

    'crowd_level' => [
        1 => 'Hampir tidak ada pengunjung',
        2 => 'Jumlah pengunjung sedikit',
        3 => 'Cukup ramai di akhir pekan',
        4 => 'Selalu banyak pengunjung',
        5 => 'Overloaded saat peak season',
    ],

];
