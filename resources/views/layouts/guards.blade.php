<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Trail Guard' }} - MyHiking</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #117958;
            --primary-dark: #0d5c43;
            --primary-light: #e8f5f0;
            --secondary-color: #2c3e50;
            --accent-color: #f39c12;
            --danger-color: #e74c3c;
            --success-color: #27ae60;
            --info-color: #3498db;
            --warning-color: #f1c40f;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: white;
            padding: 5px;
        }

        .sidebar-brand h4 {
            color: white;
            font-weight: 700;
            margin: 0;
            font-size: 1.25rem;
        }

        .sidebar-brand small {
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-label {
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            gap: 12px;
            margin: 0.25rem 0.75rem;
            border-radius: 10px;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .menu-item.active {
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 500;
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .menu-item span {
            font-size: 0.9rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .main-header {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 0;
        }

        .header-title p {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: 600;
        }

        .user-info {
            text-align: right;
        }

        .user-info .name {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .user-info .role {
            color: #64748b;
            font-size: 0.75rem;
        }

        /* Content Area */
        .content-wrapper {
            padding: 2rem;
        }

        /* Cards */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        .stat-card .icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card .icon.primary { background: var(--primary-light); color: var(--primary-color); }
        .stat-card .icon.success { background: #dcfce7; color: var(--success-color); }
        .stat-card .icon.warning { background: #fef3c7; color: var(--accent-color); }
        .stat-card .icon.info { background: #dbeafe; color: var(--info-color); }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .stat-card .label {
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Modern Card */
        .modern-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .modern-card .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }

        .modern-card .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modern-card .card-header h5 i {
            color: var(--primary-color);
        }

        .modern-card .card-body {
            padding: 1.5rem;
        }

        /* Table */
        .modern-table {
            width: 100%;
        }

        .modern-table thead th {
            background: #f8fafc;
            padding: 1rem;
            font-weight: 600;
            color: #475569;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .modern-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Badges */
        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-booking { background: #fef3c7; color: #d97706; }
        .badge-hiking { background: #dbeafe; color: #2563eb; }
        .badge-done { background: #dcfce7; color: #16a34a; }
        .badge-pending { background: #fef3c7; color: #d97706; }
        .badge-verified { background: #dcfce7; color: #16a34a; }
        .badge-cancelled { background: #fee2e2; color: #dc2626; }

        /* Buttons */
        .btn-modern {
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-modern {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary-modern:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
        }

        .btn-success-modern {
            background: var(--success-color);
            color: white;
            border: none;
        }

        .btn-success-modern:hover {
            background: #219a52;
            color: white;
        }

        .btn-info-modern {
            background: var(--info-color);
            color: white;
            border: none;
        }

        .btn-warning-modern {
            background: var(--accent-color);
            color: white;
            border: none;
        }

        .btn-outline-modern {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-modern:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Quick Action Cards */
        .quick-action {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            display: block;
        }

        .quick-action:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(17,121,88,0.15);
        }

        .quick-action .icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .quick-action h6 {
            color: var(--secondary-color);
            font-weight: 600;
            margin: 0;
        }

        .quick-action p {
            color: #64748b;
            font-size: 0.8rem;
            margin: 0.5rem 0 0;
        }

        /* Trail Info Card */
        .trail-info-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 20px;
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .trail-info-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .trail-info-card .trail-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .trail-info-card .mountain-name {
            opacity: 0.9;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        .trail-info-card .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .trail-info-card .info-item {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 12px;
        }

        .trail-info-card .info-item label {
            font-size: 0.75rem;
            opacity: 0.8;
            display: block;
            margin-bottom: 0.25rem;
        }

        .trail-info-card .info-item value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Alerts */
        .alert-modern {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-modern i {
            font-size: 1.25rem;
        }

        /* Form Styling */
        .form-modern {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .form-modern:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(17,121,88,0.1);
            outline: none;
        }

        .form-label-modern {
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block !important;
            }
        }

        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--secondary-color);
            cursor: pointer;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('img/logo.png') }}" alt="Logo">
            <div>
                <h4>MyHiking</h4>
                <small>Trail Guard Portal</small>
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            
            <a href="{{ route('guards.dashboard') }}" class="menu-item {{ request()->routeIs('guards.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('guards.scanner') }}" class="menu-item {{ request()->routeIs('guards.scanner*') ? 'active' : '' }}">
                <i class="fas fa-qrcode"></i>
                <span>Scanner QR</span>
            </a>

            <a href="{{ route('guards.sar.index') }}" class="menu-item {{ request()->routeIs('guards.sar*') ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle"></i>
                <span>SAR Dashboard</span>
            </a>

            <a href="{{ route('guards.history') }}" class="menu-item {{ request()->routeIs('guards.history') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Riwayat Pengunjung</span>
            </a>

            <div class="menu-label">Manajemen</div>

            <a href="{{ route('guards.trail') }}" class="menu-item {{ request()->routeIs('guards.trail') ? 'active' : '' }}">
                <i class="fas fa-route"></i>
                <span>Kelola Jalur</span>
            </a>

            <a href="{{ route('guards.revenue') }}" class="menu-item {{ request()->routeIs('guards.revenue') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Laporan Pendapatan</span>
            </a>

            <div class="menu-label">Akun</div>

            <a href="{{ route('guards.profile') }}" class="menu-item {{ request()->routeIs('guards.profile') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                <span>Profil Saya</span>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mx-3 mt-2">
                @csrf
                <button type="submit" class="menu-item w-100 text-start border-0 bg-transparent" style="color: rgba(255,255,255,0.8);">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="main-header">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-sidebar" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-title">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <p>@yield('page-subtitle', 'Selamat datang di portal penjaga jalur')</p>
                </div>
            </div>

            <div class="header-user">
                <div class="user-info d-none d-md-block">
                    <div class="name">{{ Auth::user()->name }}</div>
                    <div class="role">Penjaga Jalur</div>
                </div>
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content-wrapper">
            @if (session('success'))
                <div class="alert alert-modern alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-modern alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.toggle-sidebar');
            
            if (window.innerWidth <= 992 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
