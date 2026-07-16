<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyHiking - Layanan Booking Tiket Pendakian Online Resmi</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS v2 (Compiled CSS Stylesheet, offline-safe and Brave Shields compatible) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #ffffff;
            color: #374151;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }
        
        /* Custom color definitions corresponding to MyHiking (primary green: #127857) */
        .bg-primary-50 { background-color: #e7f4f0 !important; }
        .bg-primary-100 { background-color: #cbeade !important; }
        .bg-primary-600 { background-color: #127857 !important; } 
        .bg-primary-700 { background-color: #0e6045 !important; }
        .bg-primary-900 { background-color: #0b4935 !important; }
        .bg-primary-950 { background-color: #062b1f !important; }
        .bg-primary-600\/20 { background-color: rgba(18, 120, 87, 0.2) !important; }
        
        .hover\:bg-primary-50:hover { background-color: #e7f4f0 !important; }
        .hover\:bg-primary-600:hover { background-color: #127857 !important; }
        .hover\:bg-primary-700:hover { background-color: #0e6045 !important; }
        
        .text-primary-600 { color: #127857 !important; }
        .text-primary-700 { color: #0e6045 !important; }
        .text-primary-800 { color: #0b4935 !important; }
        
        .border-primary-500\/20 { border-color: rgba(18, 120, 87, 0.2) !important; }
        .border-primary-500\/30 { border-color: rgba(18, 120, 87, 0.3) !important; }
        .hover\:border-primary-500\/30:hover { border-color: rgba(18, 120, 87, 0.3) !important; }

        .shadow-primary-500\/10 { box-shadow: 0 4px 6px -1px rgba(18, 120, 87, 0.1), 0 2px 4px -1px rgba(18, 120, 87, 0.06) !important; }
        .shadow-primary-700\/20 { box-shadow: 0 10px 15px -3px rgba(18, 120, 87, 0.15), 0 4px 6px -4px rgba(18, 120, 87, 0.15) !important; }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(18, 120, 87, 0.08);
            box-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.05);
        }
        
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f3f4f6;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #127857;
        }
    </style>
</head>
<body class="antialiased">

    <!-- Header Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 glass border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#" class="flex items-center gap-3">
                <!-- App Logo Image from Flutter assets -->
                <img src="{{ asset('img/myhikinglogo.png') }}" alt="MyHiking Logo" class="w-10 h-10 object-contain rounded-xl shadow-sm border border-gray-200 bg-white p-1">
                <span class="text-xl font-extrabold tracking-tight text-gray-900">MyHiking</span>
            </a>
            
            <nav class="hidden md:flex items-center gap-8">
                <a href="#home" class="text-sm font-semibold text-gray-600 hover:text-primary-600 transition-colors">Beranda</a>
                <a href="#features" class="text-sm font-semibold text-gray-600 hover:text-primary-600 transition-colors">Fitur Aplikasi</a>
                <a href="#katalog" class="text-sm font-semibold text-gray-600 hover:text-primary-600 transition-colors">Katalog Gunung</a>
                <a href="#payment-methods" class="text-sm font-semibold text-gray-600 hover:text-primary-600 transition-colors">Pembayaran</a>
                <a href="#terms-modal-btn" onclick="openModal('terms-modal')" class="text-sm font-semibold text-gray-600 hover:text-primary-600 transition-colors cursor-pointer">Kebijakan Layanan</a>
            </nav>
            
            <!-- Link to Android download -->
            <div class="flex items-center gap-4">
                <a href="{{ asset('storage/app/myhiking.apk') }}" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm transition-all duration-300 shadow-md shadow-primary-500/10 flex items-center gap-2">
                    <i class="fa-brands fa-android text-base"></i> Download App
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="relative pt-32 pb-24 md:pt-48 md:pb-36 flex items-center justify-center overflow-hidden bg-gradient-to-b from-green-50/50 via-white to-white">
        <!-- Soft background glows -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-primary-600/5 rounded-full blur-[120px] pointer-events-none z-0"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-50 border border-primary-100 text-primary-700 text-xs font-semibold uppercase tracking-wider mb-6">
                <i class="fa-solid fa-circle-check text-[10px] text-primary-600"></i> Partner Resmi Pembayaran via Midtrans
            </div>
            
            <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold tracking-tight text-gray-900 mb-6 leading-tight">
                Mendaki Lebih Aman, <br>
                <span class="text-primary-600">Booking Online Instan</span>
            </h1>
            
            <p class="max-w-2xl mx-auto text-base sm:text-xl text-gray-500 mb-10 font-medium leading-relaxed">
                Pendaftaran pendaki, penentuan jalur rekomendasi DSS, e-tiket resmi, dan pembayaran otomatis via Midtrans Payment Gateway. Aman, cepat, tanpa antre manual di basecamp.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#katalog" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold transition-all duration-300 shadow-lg shadow-primary-700/20 text-center flex items-center justify-center gap-2">
                    <i class="fa-solid fa-ticket"></i> Cari Tiket Gunung
                </a>
                <a href="{{ asset('storage/app/myhiking.apk') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold transition-all duration-300 text-center flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-brands fa-android text-lg text-primary-600"></i> Download Aplikasi Android
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50/50 border-y border-gray-100 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Fitur Utama MyHiking</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Kami menyediakan ekosistem terpadu untuk memastikan pendakian Anda direncanakan dengan matang dan terpantau dengan aman.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Card 1: Midtrans Payment -->
                <div class="p-8 rounded-2xl bg-white border border-gray-100 hover:border-primary-100 transition-all duration-300 shadow-sm hover:shadow-md group">
                    <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 text-xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Pembayaran Instan</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Bayar tiket pendakian resmi secara instan menggunakan QRIS, E-Wallet (GoPay, ShopeePay), dan Virtual Account Bank terkemuka via Midtrans.</p>
                </div>
                
                <!-- Card 2: DSS Recommendation -->
                <div class="p-8 rounded-2xl bg-white border border-gray-100 hover:border-primary-100 transition-all duration-300 shadow-sm hover:shadow-md group">
                    <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 text-xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Rekomendasi Jalur (DSS)</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Sistem pendukung keputusan cerdas menganalisis kondisi fisik dan pengalaman Anda untuk merekomendasikan jalur gunung paling sesuai.</p>
                </div>
                
                <!-- Card 3: Safety Tracking -->
                <div class="p-8 rounded-2xl bg-white border border-gray-100 hover:border-primary-100 transition-all duration-300 shadow-sm hover:shadow-md group">
                    <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 text-xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-location-crosshairs"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Monitoring Pendaki</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Penjaga jalur (trail guard) dapat memantau status check-in, check-out, dan estimasi posisi pendaki demi keselamatan selama di gunung.</p>
                </div>
                
                <!-- Card 4: Panic Button -->
                <div class="p-8 rounded-2xl bg-white border border-gray-100 hover:border-red-100 transition-all duration-300 shadow-sm hover:shadow-md group">
                    <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600 text-xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3 text-red-600">Panic Button (SAR)</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Kirim sinyal darurat beserta titik koordinat GPS ke pos penjaga terdekat secara instan apabila terjadi cedera atau bahaya di jalur.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Katalog Gunung & Jalur -->
    <section id="katalog" class="py-20 relative bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-sm font-bold text-primary-600 tracking-widest uppercase">PRODUK & LAYANAN JASA</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-gray-900 mt-2 mb-4">Destinasi Gunung Terdaftar</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Pilih gunung tujuan Anda, lihat detail estimasi biaya registrasi (SIMAKSI) per jalur, dan pesan tiketnya langsung dari aplikasi mobile.</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                @forelse($mountains as $m)
                    <!-- Dynamic Card -->
                    <div class="rounded-2xl overflow-hidden bg-white border border-gray-100 hover:border-primary-100 transition-all duration-300 flex flex-col shadow-sm hover:shadow-lg">
                        <div class="relative h-64 w-full bg-gray-100">
                            <!-- Image with fallback handling -->
                            <img src="{{ asset('/storage/images/' . $m->gambar_gunung) }}" 
                                 alt="{{ $m->nama }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80';">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                                <span class="px-3 py-1.5 rounded-lg bg-black/60 text-white text-xs font-bold backdrop-blur-sm">
                                    <i class="fa-solid fa-mountain mr-1 text-primary-300"></i> {{ number_format($m->ketinggian, 0, ',', '.') }} MDPL
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $m->nama }}</h3>
                                <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-3">{{ $m->deskripsi }}</p>
                                
                                <div class="border-t border-gray-100 pt-4 mb-6">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Jalur & Tarif Booking Resmi:</h4>
                                    <div class="space-y-2.5">
                                        @forelse($m->trails as $t)
                                            <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50 last:border-0">
                                                <span class="text-gray-700 font-medium">
                                                    <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> {{ $t->nama }} 
                                                    <span class="text-xs text-gray-400">({{ $t->jarak }} km)</span>
                                                </span>
                                                <span class="text-primary-600 font-bold">
                                                    Rp {{ number_format($t->biaya, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="text-xs text-gray-400 italic">Belum ada informasi jalur pendakian.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ asset('storage/app/myhiking.apk') }}" class="w-full py-3 rounded-xl bg-primary-50 hover:bg-primary-600 hover:text-white border border-primary-500/20 text-primary-700 font-bold transition-all text-center block text-sm">
                                <i class="fa-brands fa-android mr-2"></i> Pilih & Booking via App
                            </a>
                        </div>
                    </div>
                @empty
                    <!-- Static / Mock Fallbacks (Akan tampil jika database kosong) -->
                    <!-- Card 1: Merbabu -->
                    <div class="rounded-2xl overflow-hidden bg-white border border-gray-100 hover:border-primary-100 transition-all duration-300 flex flex-col shadow-sm hover:shadow-lg">
                        <div class="relative h-64 w-full bg-gray-100">
                            <img src="https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?auto=format&fit=crop&w=800&q=80" 
                                 alt="Gunung Merbabu" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                                <span class="px-3 py-1.5 rounded-lg bg-black/60 text-white text-xs font-bold backdrop-blur-sm">
                                    <i class="fa-solid fa-mountain mr-1 text-primary-300"></i> 3.142 MDPL
                                </span>
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Gunung Merbabu</h3>
                                <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-3">Gunung Merbabu adalah gunung berapi bertipe stratovolcano yang terletak di Jawa Tengah, terkenal dengan sabana rumputnya yang sangat luas dan indah.</p>
                                
                                <div class="border-t border-gray-100 pt-4 mb-6">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Jalur & Tarif Booking Resmi:</h4>
                                    <div class="space-y-2.5">
                                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Selo <span class="text-xs text-gray-400">(5.6 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 20.000</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Cuntel <span class="text-xs text-gray-400">(6.4 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 20.000</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm py-1.5">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Suwanting <span class="text-xs text-gray-400">(6.5 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 20.000</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ asset('storage/app/myhiking.apk') }}" class="w-full py-3 rounded-xl bg-primary-50 hover:bg-primary-600 hover:text-white border border-primary-500/20 text-primary-700 font-bold transition-all text-center block text-sm">
                                <i class="fa-brands fa-android mr-2"></i> Pilih & Booking via App
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Slamet -->
                    <div class="rounded-2xl overflow-hidden bg-white border border-gray-100 hover:border-primary-100 transition-all duration-300 flex flex-col shadow-sm hover:shadow-lg">
                        <div class="relative h-64 w-full bg-gray-100">
                            <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80" 
                                 alt="Gunung Slamet" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                                <span class="px-3 py-1.5 rounded-lg bg-black/60 text-white text-xs font-bold backdrop-blur-sm">
                                    <i class="fa-solid fa-mountain mr-1 text-primary-300"></i> 3.428 MDPL
                                </span>
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Gunung Slamet</h3>
                                <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-3">Gunung Slamet adalah gunung berapi tertinggi di Jawa Tengah dan merupakan gunung tunggal terbesar di Pulau Jawa, menawarkan rute pendakian yang menantang.</p>
                                
                                <div class="border-t border-gray-100 pt-4 mb-6">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Jalur & Tarif Booking Resmi:</h4>
                                    <div class="space-y-2.5">
                                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Bambangan <span class="text-xs text-gray-400">(6.2 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 25.000</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Kaliwadas <span class="text-xs text-gray-400">(11.0 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 35.000</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm py-1.5">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Guci <span class="text-xs text-gray-400">(9.9 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 35.000</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ asset('storage/app/myhiking.apk') }}" class="w-full py-3 rounded-xl bg-primary-50 hover:bg-primary-600 hover:text-white border border-primary-500/20 text-primary-700 font-bold transition-all text-center block text-sm">
                                <i class="fa-brands fa-android mr-2"></i> Pilih & Booking via App
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: Sumbing -->
                    <div class="rounded-2xl overflow-hidden bg-white border border-gray-100 hover:border-primary-100 transition-all duration-300 flex flex-col shadow-sm hover:shadow-lg">
                        <div class="relative h-64 w-full bg-gray-100">
                            <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80" 
                                 alt="Gunung Sumbing" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                                <span class="px-3 py-1.5 rounded-lg bg-black/60 text-white text-xs font-bold backdrop-blur-sm">
                                    <i class="fa-solid fa-mountain mr-1 text-primary-300"></i> 3.371 MDPL
                                </span>
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Gunung Sumbing</h3>
                                <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-3">Gunung Sumbing menyajikan pemandangan kawah aktif yang indah dan lanskap alam pegunungan vulkanik yang menawan hati para pencinta alam.</p>
                                
                                <div class="border-t border-gray-100 pt-4 mb-6">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Jalur & Tarif Booking Resmi:</h4>
                                    <div class="space-y-2.5">
                                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Mangli <span class="text-xs text-gray-400">(7.0 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 35.000</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-50">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Garung <span class="text-xs text-gray-400">(4.2 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 35.000</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm py-1.5">
                                            <span class="text-gray-700 font-medium">
                                                <i class="fa-solid fa-route mr-1.5 text-primary-600"></i> Jalur Bowongso <span class="text-xs text-gray-400">(7.7 km)</span>
                                            </span>
                                            <span class="text-primary-600 font-bold">Rp 35.000</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ asset('storage/app/myhiking.apk') }}" class="w-full py-3 rounded-xl bg-primary-50 hover:bg-primary-600 hover:text-white border border-primary-500/20 text-primary-700 font-bold transition-all text-center block text-sm">
                                <i class="fa-brands fa-android mr-2"></i> Pilih & Booking via App
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Download App Section -->
    <section id="download" class="py-20 bg-gray-50 border-t border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-primary-50 rounded-3xl p-8 sm:p-12 border border-primary-100 shadow-sm relative overflow-hidden text-center">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-600/5 rounded-full blur-2xl"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-primary-600/5 rounded-full blur-2xl"></div>
                
                <span class="px-3 py-1.5 rounded-lg bg-primary-100 text-primary-800 text-xs font-bold tracking-wide uppercase inline-block mb-4">Aplikasi Mobile Pendaki</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-6">Nikmati Kemudahan dengan Aplikasi MyHiking</h2>
                <p class="text-gray-600 mb-8 leading-relaxed max-w-2xl mx-auto">
                    Dapatkan pengalaman registrasi terlengkap langsung di smartphone Anda. Fitur checkout instan Midtrans Snap terintegrasi mempermudah pengisian tiket. Dilengkapi dengan navigasi offline, fitur chat pendaki berbasis kecerdasan buatan, dan jaminan keselamatan lewat Panic Button SAR.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10">
                    <a href="{{ asset('storage/app/myhiking.apk') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold transition flex items-center justify-center gap-2 shadow-md">
                        <i class="fa-brands fa-android text-xl"></i> Download APK Langsung
                    </a>
                    <span class="text-xs text-gray-500 font-medium">Versi Android 8.0+ didukung</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Payment Gateways Section -->
    <section id="payment-methods" class="py-16 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Metode Pembayaran Didukung via Midtrans</h3>
            <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12 opacity-65">
                <span class="text-gray-700 font-extrabold text-xl tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-qrcode text-primary-600"></i> QRIS
                </span>
                <span class="text-gray-700 font-extrabold text-xl tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-primary-600"></i> GOPAY
                </span>
                <span class="text-gray-700 font-extrabold text-xl tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-primary-600"></i> VIRTUAL ACCOUNT
                </span>
                <span class="text-gray-700 font-extrabold text-xl tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-primary-600"></i> VISA / MASTERCARD
                </span>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 py-12 text-sm text-gray-400 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('img/myhikinglogo.png') }}" alt="MyHiking Logo" class="w-8 h-8 object-contain rounded-lg">
                        <span class="text-lg font-bold text-white">MyHiking</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Sistem Informasi terpadu pendaftaran pendaki online dan reservasi tiket resmi terintegrasi dengan Payment Gateway Midtrans.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Alamat & Kontak Bisnis</h4>
                    <p class="text-xs leading-relaxed space-y-2 text-gray-400">
                        <strong>Pemilik Usaha:</strong> Faiz Ismail M<br>
                        <strong>Alamat:</strong> Tembalang, Semarang, Jawa Tengah, 50275<br>
                        <strong>Email Pengelola:</strong> faizismail1706@gmail.com<br>
                        <strong>No. Telepon:</strong> +62 822-4217-1725
                    </p>
                </div>
                
                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Kebijakan Bisnis</h4>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <button onclick="openModal('refund-modal')" class="hover:text-primary-600 text-left transition-colors cursor-pointer text-gray-400">
                                <i class="fa-solid fa-circle-info mr-1 text-primary-600"></i> Kebijakan Pengembalian Dana (Refund)
                            </button>
                        </li>
                        <li>
                            <button onclick="openModal('terms-modal')" class="hover:text-primary-600 text-left transition-colors cursor-pointer text-gray-400">
                                <i class="fa-solid fa-circle-info mr-1 text-primary-600"></i> Syarat & Ketentuan Layanan (TOS)
                            </button>
                        </li>
                        <li>
                            <button onclick="openModal('privacy-modal')" class="hover:text-primary-600 text-left transition-colors cursor-pointer text-gray-400">
                                <i class="fa-solid fa-circle-info mr-1 text-primary-600"></i> Kebijakan Privasi
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-gray-500">
                <p>&copy; 2026 MyHiking. Hak Cipta Dilindungi Undang-Undang. Layanan ini tunduk pada aturan operasional pariwisata nasional.</p>
                <div class="flex items-center gap-4 mt-4 md:mt-0">
                    <span class="text-gray-400">Terverifikasi oleh Midtrans</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- MODAL 1: Terms & Conditions -->
    <div id="terms-modal" class="fixed inset-0 z-50 hidden bg-black/55 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white max-w-2xl w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh] border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-shield text-primary-600"></i> Syarat & Ketentuan Layanan (TOS)
                </h3>
                <button onclick="closeModal('terms-modal')" class="text-gray-400 hover:text-gray-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="p-6 overflow-y-auto space-y-4 text-xs text-gray-600 leading-relaxed">
                <h4 class="font-bold text-gray-900">1. Ketentuan Umum Pendakian</h4>
                <p>Setiap pendaki wajib melakukan registrasi online melalui aplikasi MyHiking dan membayar biaya pendaftaran resmi (SIMAKSI) sesuai jalur pilihan sebelum memulai pendakian.</p>
                
                <h4 class="font-bold text-gray-900">2. Keabsahan E-Tiket</h4>
                <p>E-Tiket resmi yang diterbitkan setelah konfirmasi pembayaran sukses oleh Midtrans merupakan bukti pemesanan yang sah dan harus ditunjukkan kepada petugas basecamp saat check-in.</p>
                
                <h4 class="font-bold text-gray-900">3. Kewajiban & Keselamatan Pendaki</h4>
                <p>Pendaki wajib membawa perlengkapan standar kelayakan daki gunung yang telah diatur oleh pengelola. MyHiking menyediakan fitur monitoring GPS dan Panic Button untuk mempermudah evakuasi oleh tim SAR terdekat jika terjadi kondisi darurat.</p>
                
                <h4 class="font-bold text-gray-900">4. Kepatuhan Hukum</h4>
                <p>Semua transaksi yang diproses melalui sistem kami adalah sah dan diatur di bawah yurisdiksi hukum Republik Indonesia serta kepatuhan terhadap merchant regulator Bank Indonesia melalui Midtrans.</p>
            </div>
            <div class="p-6 border-t border-gray-100 bg-gray-50 text-right">
                <button onclick="closeModal('terms-modal')" class="px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition cursor-pointer">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Refund Policy -->
    <div id="refund-modal" class="fixed inset-0 z-50 hidden bg-black/55 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white max-w-2xl w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh] border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-rotate-left text-primary-600"></i> Kebijakan Pengembalian Dana (Refund)
                </h3>
                <button onclick="closeModal('refund-modal')" class="text-gray-400 hover:text-gray-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="p-6 overflow-y-auto space-y-4 text-xs text-gray-600 leading-relaxed">
                <h4 class="font-bold text-gray-900">1. Kondisi Pengajuan Refund</h4>
                <p>Refund tiket pendakian hanya dapat diajukan jika terjadi pembatalan kegiatan dari pihak pengelola taman nasional / pos gunung akibat kondisi cuaca buruk ekstrim, bencana alam gunung meletus, atau alasan operasional darurat lainnya.</p>
                
                <h4 class="font-bold text-gray-900">2. Prosedur Pembatalan oleh Pengguna</h4>
                <p>Jika pembatalan diajukan secara mandiri oleh pendaki karena alasan pribadi, pengajuan wajib dilakukan maksimal 2x24 jam sebelum tanggal keberangkatan pendakian terdaftar melalui menu pengembalian (refund) di aplikasi mobile.</p>
                
                <h4 class="font-bold text-gray-900">3. Pemotongan Biaya & Administrasi</h4>
                <p>Setiap refund atas keinginan pendaki sendiri akan dikenakan biaya administrasi transaksi bank/payment gateway. Pengembalian sisa dana akan ditransfer kembali ke rekening pemohon maksimal dalam waktu 7-14 hari kerja setelah disetujui admin.</p>
            </div>
            <div class="p-6 border-t border-gray-100 bg-gray-50 text-right">
                <button onclick="closeModal('refund-modal')" class="px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Privacy Policy -->
    <div id="privacy-modal" class="fixed inset-0 z-50 hidden bg-black/55 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white max-w-2xl w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh] border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-lock text-primary-600"></i> Kebijakan Privasi Pengguna
                </h3>
                <button onclick="closeModal('privacy-modal')" class="text-gray-400 hover:text-gray-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="p-6 overflow-y-auto space-y-4 text-xs text-gray-600 leading-relaxed">
                <h4 class="font-bold text-gray-900">1. Pengumpulan Informasi</h4>
                <p>Kami mengumpulkan data pendaki berupa nama lengkap, nomor KTP/identitas, kontak darurat, serta data koordinat lokasi GPS pendakian demi keselamatan dan pengurusan e-tiket pariwisata.</p>
                
                <h4 class="font-bold text-gray-900">2. Keamanan Pembayaran</h4>
                <p>Semua informasi keuangan pribadi (seperti data e-wallet dan akun bank) tidak disimpan di server kami, melainkan diproses secara aman menggunakan sistem enkripsi standar industri milik Midtrans Payment Gateway.</p>
                
                <h4 class="font-bold text-gray-900">3. Penggunaan Data Koordinat</h4>
                <p>Data lokasi GPS saat mendaki hanya digunakan oleh sistem monitoring pos pendakian dan tim rescue SAR ketika Anda menekan tombol Panic Button. Data lokasi akan berhenti direkam setelah Anda melakukan proses check-out resmi.</p>
            </div>
            <div class="p-6 border-t border-gray-100 bg-gray-50 text-right">
                <button onclick="closeModal('privacy-modal')" class="px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- JS Helper to manage Modals -->
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        // Close modal if click outside the content box
        window.onclick = function(event) {
            const modals = ['terms-modal', 'refund-modal', 'privacy-modal'];
            modals.forEach(id => {
                const modal = document.getElementById(id);
                if (event.target == modal) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        }
    </script>
</body>
</html>
