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
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-label {
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            gap: 12px;
            margin: 0.25rem 0.75rem;
            border-radius: 10px;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.2);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
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

        .stat-card .icon.primary {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .stat-card .icon.success {
            background: #dcfce7;
            color: var(--success-color);
        }

        .stat-card .icon.warning {
            background: #fef3c7;
            color: var(--accent-color);
        }

        .stat-card .icon.info {
            background: #dbeafe;
            color: var(--info-color);
        }

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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
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
            white-space: nowrap;
        }

        .modern-table thead th.sortable {
            cursor: pointer;
            user-select: none;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .modern-table thead th.sortable:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .modern-table thead th .sort-icon {
            display: inline-block;
            margin-left: 6px;
            font-size: 0.75rem;
            color: #94a3b8;
            transition: color 0.2s ease;
            vertical-align: middle;
        }

        .modern-table thead th.sort-asc .sort-icon,
        .modern-table thead th.sort-desc .sort-icon {
            color: var(--primary-color);
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

        .badge-booking {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-hiking {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-done {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-verified {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

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
            box-shadow: 0 10px 30px rgba(17, 121, 88, 0.15);
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
            background: rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 0 0 3px rgba(17, 121, 88, 0.1);
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
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Global Chatbot */
        .global-chat-fab {
            position: fixed;
            right: 24px;
            bottom: 24px;
            width: 58px;
            height: 58px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff;
            font-size: 22px;
            box-shadow: 0 12px 24px rgba(17, 121, 88, 0.35);
            z-index: 1200;
        }

        .global-chat-window {
            position: fixed;
            right: 24px;
            bottom: 92px;
            width: min(420px, calc(100vw - 28px));
            height: min(620px, calc(100vh - 120px));
            display: none;
            flex-direction: column;
            background: #fff;
            border: 1px solid #d5e7de;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(17, 80, 60, 0.25);
            z-index: 1200;
        }

        .global-chat-window.show {
            display: flex;
            animation: chatIn 0.2s ease;
        }

        .global-chat-header {
            padding: 14px 16px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .global-chat-body {
            flex: 1;
            overflow-y: auto;
            background: #f6fbf8;
            padding: 14px;
        }

        .global-chat-footer {
            padding: 12px;
            border-top: 1px solid #e2efe9;
            background: #fff;
            display: flex;
            gap: 8px;
        }

        .global-chat-bubble-user {
            background: var(--accent-color);
            color: #1f2933;
        }

        .global-chat-bubble-bot {
            background: #fff;
            border: 1px solid #d9e8df;
            color: #24303a;
        }

        @keyframes chatIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .global-chat-fab {
                right: 14px;
                bottom: 14px;
            }

            .global-chat-window {
                right: 10px;
                left: 10px;
                width: auto;
                bottom: 80px;
                height: min(560px, calc(100vh - 96px));
            }
        }

        /* ===== SOS MODAL STYLES ===== */
        #sosModal .modal-dialog {
            max-width: 640px;
        }
        #sosModal .modal-content {
            background: #1a0000;
            border: 3px solid #ff0000;
            border-radius: 16px;
            overflow: hidden;
        }
        #sosModal .sos-header {
            background: linear-gradient(135deg, #c0392b, #8b0000);
            padding: 28px 32px 20px;
            text-align: center;
            position: relative;
        }
        #sosModal .sos-header .sos-icon-ring {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            animation: sosPulse 1s ease-in-out infinite;
        }
        #sosModal .sos-header h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 1px;
            margin: 0;
            text-shadow: 0 0 20px rgba(255,100,100,0.8);
        }
        #sosModal .sos-body {
            padding: 28px 32px;
        }
        #sosModal .sos-info-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 12px;
        }
        #sosModal .sos-info-row .sos-info-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: rgba(231,76,60,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff6b6b;
            flex-shrink: 0;
        }
        #sosModal .sos-info-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
            margin-bottom: 2px;
        }
        #sosModal .sos-info-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
        }
        #sosModal .sos-emergency-badge {
            display: inline-block;
            background: rgba(231,76,60,0.3);
            border: 1px solid #e74c3c;
            color: #ff8080;
            border-radius: 6px;
            padding: 2px 10px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 18px;
        }
        #btnTerimaCall {
            width: 100%;
            padding: 18px;
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: 2px;
            border-radius: 12px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            color: #fff;
            box-shadow: 0 0 30px rgba(231,76,60,0.7), 0 6px 20px rgba(0,0,0,0.4);
            transition: transform 0.15s, box-shadow 0.15s;
            animation: btnPulse 1.8s ease-in-out infinite;
        }
        #btnTerimaCall:hover {
            transform: scale(1.03);
            box-shadow: 0 0 50px rgba(231,76,60,0.9), 0 8px 25px rgba(0,0,0,0.5);
        }
        #btnTerimaCall:active { transform: scale(0.98); }

        @keyframes sosPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,80,80,0.7), 0 0 0 10px rgba(255,80,80,0.3); }
            50%       { box-shadow: 0 0 0 18px rgba(255,80,80,0), 0 0 0 30px rgba(255,80,80,0); }
        }
        @keyframes btnPulse {
            0%, 100% { box-shadow: 0 0 20px rgba(231,76,60,0.6), 0 6px 20px rgba(0,0,0,0.4); }
            50%       { box-shadow: 0 0 45px rgba(231,76,60,1),   0 6px 25px rgba(0,0,0,0.5); }
        }
        @keyframes shakeModal {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }
        .sos-shake { animation: shakeModal 0.5s ease-in-out; }

        /* Striped alert bar at top of modal */
        .sos-stripe-bar {
            height: 8px;
            background: repeating-linear-gradient(
                45deg,
                #e74c3c,
                #e74c3c 10px,
                #ff0 10px,
                #ff0 20px
            );
            animation: stripeScroll 1s linear infinite;
            background-size: 200% 100%;
        }
        @keyframes stripeScroll {
            0%   { background-position: 0 0; }
            100% { background-position: 40px 0; }
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

            <a href="{{ route('guards.dashboard') }}"
                class="menu-item {{ request()->routeIs('guards.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('guards.scanner') }}"
                class="menu-item {{ request()->routeIs('guards.scanner*') ? 'active' : '' }}">
                <i class="fas fa-qrcode"></i>
                <span>Scanner QR</span>
            </a>

            <a href="{{ route('guards.sar.index') }}"
                class="menu-item {{ request()->routeIs('guards.sar*') ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle"></i>
                <span>SAR Dashboard</span>
                <span class="badge bg-danger ms-auto d-none" id="sar-badge">0</span>
            </a>

            <a href="{{ route('guards.monitoring') }}"
                class="menu-item {{ request()->routeIs('guards.monitoring*') ? 'active' : '' }}">
                <i class="fas fa-map-marked-alt"></i>
                <span>Pemantauan Jalur</span>
            </a>

            <a href="{{ route('guards.history') }}"
                class="menu-item {{ request()->routeIs('guards.history') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Riwayat Pengunjung</span>
            </a>

            <div class="menu-label">Manajemen</div>

            <a href="{{ route('guards.trail') }}"
                class="menu-item {{ request()->routeIs('guards.trail') ? 'active' : '' }}">
                <i class="fas fa-route"></i>
                <span>Kelola Jalur</span>
            </a>

            <a href="{{ route('guards.revenue') }}"
                class="menu-item {{ request()->routeIs('guards.revenue') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Laporan Pendapatan</span>
            </a>

            <a href="{{ route('trail-guard.withdrawal.index') }}"
                class="menu-item {{ request()->routeIs('trail-guard.withdrawal.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Tarik Saldo</span>
            </a>

            <div class="menu-label">Akun</div>

            <a href="{{ route('guards.profile') }}"
                class="menu-item {{ request()->routeIs('guards.profile') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                <span>Profil Saya</span>
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mx-3 mt-2">
                @csrf
                <button type="submit" class="menu-item w-100 text-start border-0 bg-transparent"
                    style="color: rgba(255,255,255,0.8);">
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

    <button id="globalChatToggle" class="global-chat-fab" type="button" aria-label="Buka chatbot penjaga">
        <i class="fas fa-robot"></i>
    </button>

    <div id="globalChatWindow" class="global-chat-window" aria-hidden="true">
        <div class="global-chat-header">
            <div>
                <h6 class="mb-0">Trail Guard Assistant</h6>
                <small>{{ Auth::user()->name }}</small>
            </div>
            <button id="globalChatClose" class="btn-close btn-close-white" type="button" aria-label="Tutup"></button>
        </div>

        <div id="globalChatBox" class="global-chat-body"></div>

        <form id="globalChatForm" class="global-chat-footer">
            <input id="globalChatInput" type="text" class="form-control" placeholder="Ketik pertanyaan penjaga..."
                autocomplete="off" required>
            <button class="btn btn-success" type="submit">Kirim</button>
        </form>
    </div>

{{-- ===== SOS FORCED MODAL ===== --}}
<div class="modal fade" id="sosModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false"
     aria-labelledby="sosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            {{-- Stripe bar --}}
            <div class="sos-stripe-bar"></div>

            {{-- Header --}}
            <div class="sos-header">
                <div class="sos-info-row mb-3 justify-content-center" style="background:transparent;border:none;">
                    <div class="sos-icon-ring">
                        <i class="fas fa-exclamation-triangle fa-3x text-white"></i>
                    </div>
                </div>
                <h2 id="sosModalLabel">🚨 PERMINTAAN DARURAT MASUK!</h2>
                <p class="text-white-50 mt-2 mb-0" style="font-size:0.9rem;">
                    Segera tangani permintaan SOS ini
                </p>
            </div>

            {{-- Body --}}
            <div class="sos-body">
                <div class="text-center mb-3">
                    <span class="sos-emergency-badge" id="sosEmergencyType">—</span>
                </div>

                <div class="sos-info-row">
                    <div class="sos-info-icon"><i class="fas fa-user fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Nama Pendaki</div>
                        <div class="sos-info-value" id="sosHikerName">—</div>
                    </div>
                </div>

                <div class="sos-info-row">
                    <div class="sos-info-icon"><i class="fas fa-route fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Jalur / Gunung</div>
                        <div class="sos-info-value" id="sosTrailName">—</div>
                    </div>
                </div>

                <div class="sos-info-row">
                    <div class="sos-info-icon"><i class="fas fa-clock fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Waktu SOS</div>
                        <div class="sos-info-value" id="sosTime">—</div>
                    </div>
                </div>

                <div class="sos-info-row" id="sosDescRow" style="display:none;">
                    <div class="sos-info-icon"><i class="fas fa-notes-medical fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Keterangan</div>
                        <div class="sos-info-value" id="sosDesc" style="font-size:0.92rem;font-weight:500;">—</div>
                    </div>
                </div>

                <div class="mt-4">
                    <button id="btnTerimaCall" type="button">
                        <i class="fas fa-phone-alt me-2"></i>TERIMA PANGGILAN
                    </button>
                </div>

                <p class="text-center text-muted mt-3 mb-0" style="font-size:0.78rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    Anda akan diarahkan ke halaman detail setelah menerima panggilan
                </p>
            </div>

            <div class="sos-stripe-bar"></div>
        </div>
    </div>
</div>

    <!-- Modals from child views -->
    @stack('modals')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    (function () {
        'use strict';

        /* ------------------------------------------------------------------ */
        /*  CONFIG                                                              */
        /* ------------------------------------------------------------------ */
        const POLL_INTERVAL_MS  = 5000;          // 5 seconds
        const API_URL           = '{{ route("guards.sar.check-new-panics") }}';
        const ALARM_FREQ        = 850;           // Hz
        const ALARM_VOLUME      = 0.5;
        const ALARM_BEEP_ON_MS  = 800;           // beep on duration
        const ALARM_BEEP_OFF_MS = 400;           // silence between beeps
        const ALARM_CYCLE_MS    = 3000;          // full SOS pattern repeats every 3s

        /* ------------------------------------------------------------------ */
        /*  STATE                                                               */
        /* ------------------------------------------------------------------ */
        let lastSeenId      = 0;      // tracks highest panic id already alerted
        let currentPanicId  = null;   // id shown in the modal right now
        let currentDetailUrl = null;  // redirect URL for "TERIMA PANGGILAN"
        let currentRespondUrl = null; // POST action URL for "TERIMA PANGGILAN"
        let alarmCtx        = null;   // Web Audio API context
        let alarmNodes      = [];     // active oscillator/gain nodes
        let alarmTimer      = null;   // setInterval handle for looping
        let pollTimer       = null;   // setInterval handle for polling
        let modalInstance   = null;   // Bootstrap modal instance

        /* ------------------------------------------------------------------ */
        /*  ALARM — Web Audio API                                               */
        /* ------------------------------------------------------------------ */
        function startAlarm() {
            stopAlarm(); // never double-play

            try {
                alarmCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch (e) {
                console.warn('Web Audio API not supported:', e);
                return;
            }

            function playBeep(startTime, durationSec) {
                const osc  = alarmCtx.createOscillator();
                const gain = alarmCtx.createGain();

                osc.type      = 'square';
                osc.frequency.setValueAtTime(ALARM_FREQ, startTime);

                gain.gain.setValueAtTime(0, startTime);
                gain.gain.linearRampToValueAtTime(ALARM_VOLUME, startTime + 0.01);
                gain.gain.setValueAtTime(ALARM_VOLUME, startTime + durationSec - 0.02);
                gain.gain.linearRampToValueAtTime(0, startTime + durationSec);

                osc.connect(gain);
                gain.connect(alarmCtx.destination);

                osc.start(startTime);
                osc.stop(startTime + durationSec);

                alarmNodes.push({ osc, gain });
            }

            function scheduleSOSPattern() {
                if (!alarmCtx) return;
                const now = alarmCtx.currentTime;

                // SOS: · · · — — — · · ·  (simplified: 3 short + pause)
                const shortSec  = ALARM_BEEP_ON_MS  / 1000;
                const pauseSec  = ALARM_BEEP_OFF_MS / 1000;

                let t = now;
                for (let i = 0; i < 3; i++) {
                    playBeep(t, shortSec);
                    t += shortSec + pauseSec;
                }
            }

            scheduleSOSPattern();
            alarmTimer = setInterval(scheduleSOSPattern, ALARM_CYCLE_MS);
        }

        function stopAlarm() {
            clearInterval(alarmTimer);
            alarmTimer = null;

            alarmNodes.forEach(function (n) {
                try { n.osc.stop();  } catch (_) {}
                try { n.osc.disconnect();  } catch (_) {}
                try { n.gain.disconnect(); } catch (_) {}
            });
            alarmNodes = [];

            if (alarmCtx) {
                try { alarmCtx.close(); } catch (_) {}
                alarmCtx = null;
            }
        }

        /* ------------------------------------------------------------------ */
        /*  MODAL                                                               */
        /* ------------------------------------------------------------------ */
        function getModal() {
            if (!modalInstance) {
                const el = document.getElementById('sosModal');
                if (el) {
                    modalInstance = new bootstrap.Modal(el, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    el.addEventListener('hidden.bs.modal', function () {
                        stopAlarm();
                        currentPanicId = null;
                        currentDetailUrl = null;
                        currentRespondUrl = null;
                    });
                }
            }
            return modalInstance;
        }

        function populateModal(panic) {
            const typeEl = document.getElementById('sosEmergencyType');
            const hikerEl = document.getElementById('sosHikerName');
            const trailEl = document.getElementById('sosTrailName');
            const timeEl = document.getElementById('sosTime');
            const descEl = document.getElementById('sosDesc');
            const descRow = document.getElementById('sosDescRow');

            if (typeEl) typeEl.textContent = panic.emergency_type || 'DARURAT';
            if (hikerEl) hikerEl.textContent     = panic.hiker_name;
            if (trailEl) {
                trailEl.textContent     =
                    panic.mountain_name !== 'N/A'
                        ? panic.trail_name + ' — ' + panic.mountain_name
                        : panic.trail_name;
            }
            if (timeEl) timeEl.textContent          = panic.created_at;

            if (descEl && descRow) {
                if (panic.description) {
                    descEl.textContent = panic.description;
                    descRow.style.display = 'flex';
                } else {
                    descRow.style.display = 'none';
                }
            }

            currentDetailUrl  = panic.detail_url;
            currentRespondUrl = panic.respond_url;
            currentPanicId    = panic.id;
        }

        function showSOSModal(panic) {
            populateModal(panic);
            startAlarm();

            const modal = getModal();
            if (modal) {
                modal.show();
            }

            // Shake the modal dialog for extra drama after it's shown
            const dialogEl = document.querySelector('#sosModal .modal-dialog');
            if (dialogEl) {
                setTimeout(function () {
                    dialogEl.classList.add('sos-shake');
                    setTimeout(function () { dialogEl.classList.remove('sos-shake'); }, 600);
                }, 400);

                // Shake again every 8 seconds while modal is open
                const shakeLoop = setInterval(function () {
                    const m = document.getElementById('sosModal');
                    if (!m || !m.classList.contains('show')) {
                        clearInterval(shakeLoop);
                        return;
                    }
                    dialogEl.classList.add('sos-shake');
                    setTimeout(function () { dialogEl.classList.remove('sos-shake'); }, 600);
                }, 8000);
            }
        }

        function closeSOSModal() {
            stopAlarm();
            const modal = getModal();
            if (modal) {
                modal.hide();
            }
            currentPanicId    = null;
            currentDetailUrl  = null;
            currentRespondUrl = null;
        }

        /* ------------------------------------------------------------------ */
        /*  ACCEPT BUTTON                                                       */
        /* ------------------------------------------------------------------ */
        const acceptBtn = document.getElementById('btnTerimaCall');
        if (acceptBtn) {
            acceptBtn.addEventListener('click', function () {
                const respondUrl = currentRespondUrl;
                const detailUrl  = currentDetailUrl;

                closeSOSModal();

                if (respondUrl) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = respondUrl;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    document.body.appendChild(form);
                    form.submit();
                } else if (detailUrl) {
                    window.location.href = detailUrl;
                }
            });
        }

        /* ------------------------------------------------------------------ */
        /*  UPDATE SIDEBAR BADGE                                                */
        /* ------------------------------------------------------------------ */
        function updateSidebarBadge(totalPending) {
            const badgeEl = document.getElementById('sar-badge');
            if (badgeEl) {
                if (totalPending > 0) {
                    badgeEl.textContent = totalPending;
                    badgeEl.classList.remove('d-none');
                } else {
                    badgeEl.classList.add('d-none');
                }
            }
        }

        /* ------------------------------------------------------------------ */
        /*  POLLING                                                             */
        /* ------------------------------------------------------------------ */
        function checkNewPanics() {
            // If a modal is already open, don't overlap — just update badge and keep polling
            const modalEl = document.getElementById('sosModal');
            const isModalOpen = modalEl && modalEl.classList.contains('show');

            fetch(API_URL + '?last_seen_id=' + lastSeenId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                updateSidebarBadge(data.total_pending);

                // If modal is currently open for a panic, verify it is still active/pending
                if (isModalOpen && currentPanicId) {
                    const activeIds = data.active_pending_ids || [];
                    if (!activeIds.includes(currentPanicId)) {
                        // The panic request was accepted/handled elsewhere
                        closeSOSModal();
                        return;
                    }
                }

                if (!isModalOpen && data.panics && data.panics.length > 0) {
                    // Show alert for the first/latest unread panic
                    const latest = data.panics[data.panics.length - 1];

                    // Advance the cursor so we don't re-alert
                    lastSeenId = latest.id;

                    showSOSModal(latest);
                }
            })
            .catch(function (err) {
                console.warn('[SOS Polling] Error:', err);
            });
        }

        /* ------------------------------------------------------------------ */
        /*  INIT                                                                */
        /* ------------------------------------------------------------------ */
        // Initialise lastSeenId from the highest id already on-page
        // so a guard loading the dashboard doesn't get alerted for old panics
        (function seedLastSeenId() {
            fetch(API_URL + '?last_seen_id=0', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                updateSidebarBadge(d.total_pending);

                if (d.panics && d.panics.length > 0) {
                    const latest = d.panics[d.panics.length - 1];
                    lastSeenId = latest.id;

                    const modalEl = document.getElementById('sosModal');
                    const isModalOpen = modalEl && modalEl.classList.contains('show');
                    if (!isModalOpen) {
                        showSOSModal(latest);
                    }
                }
                // Start polling AFTER seeding
                pollTimer = setInterval(checkNewPanics, POLL_INTERVAL_MS);
            })
            .catch(function () {
                // Still start polling even if seed fails
                pollTimer = setInterval(checkNewPanics, POLL_INTERVAL_MS);
            });
        })();

        // Cleanup on page leave
        window.addEventListener('beforeunload', function () {
            clearInterval(pollTimer);
            stopAlarm();
        });

    })();
    </script>

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

        // Universal Interactive Table Column Sorting (ASC/DESC)
        document.addEventListener('DOMContentLoaded', function () {
            const initSortableTables = () => {
                document.querySelectorAll('.modern-table').forEach(table => {
                    const headers = table.querySelectorAll('thead th');
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;

                    headers.forEach((header, colIndex) => {
                        // Skip Aksi, Action, Gambar columns
                        const headerText = header.textContent.trim().toLowerCase();
                        if (headerText === 'aksi' || headerText === 'action' || headerText === 'gambar' || header.classList.contains('no-sort')) {
                            return;
                        }

                        header.classList.add('sortable');

                        if (!header.querySelector('.sort-icon')) {
                            const icon = document.createElement('i');
                            icon.className = 'fas fa-sort sort-icon';
                            header.appendChild(icon);
                        }

                        header.addEventListener('click', function () {
                            const currentDir = header.getAttribute('data-sort-dir');
                            const newDir = !currentDir || currentDir === 'asc' ? 'desc' : 'asc';

                            // Reset all headers in this table
                            headers.forEach(h => {
                                h.removeAttribute('data-sort-dir');
                                h.classList.remove('sort-asc', 'sort-desc');
                                const icon = h.querySelector('.sort-icon');
                                if (icon) icon.className = 'fas fa-sort sort-icon';
                            });

                            header.setAttribute('data-sort-dir', newDir);
                            header.classList.add(newDir === 'asc' ? 'sort-asc' : 'sort-desc');
                            const icon = header.querySelector('.sort-icon');
                            if (icon) icon.className = newDir === 'desc' ? 'fas fa-sort-up sort-icon' : 'fas fa-sort-down sort-icon';

                            const rows = Array.from(tbody.querySelectorAll('tr'));
                            const dataRows = rows.filter(r => !r.querySelector('td[colspan]'));
                            if (dataRows.length <= 1) return;

                            dataRows.sort((rowA, rowB) => {
                                const cellA = rowA.children[colIndex] ? rowA.children[colIndex].textContent.trim() : '';
                                const cellB = rowB.children[colIndex] ? rowB.children[colIndex].textContent.trim() : '';

                                const getNumericVal = (str) => {
                                    if (!str) return NaN;
                                    let s = str.trim();
                                    if (/Rp/i.test(s)) {
                                        let cleaned = s.replace(/Rp\s?/gi, '').replace(/\./g, '').replace(',', '.').trim();
                                        const p = parseFloat(cleaned);
                                        return isNaN(p) ? NaN : p;
                                    }
                                    const match = s.match(/-?\d+(?:[\.,]\d+)?/);
                                    if (match) {
                                        let rawNum = match[0];
                                        if (rawNum.includes('.') && rawNum.includes(',')) {
                                            rawNum = rawNum.replace(/\./g, '').replace(',', '.');
                                        } else if (rawNum.includes(',')) {
                                            rawNum = rawNum.replace(',', '.');
                                        }
                                        const p = parseFloat(rawNum);
                                        return isNaN(p) ? NaN : p;
                                    }
                                    return NaN;
                                };

                                const numA = getNumericVal(cellA);
                                const numB = getNumericVal(cellB);

                                let comparison = 0;
                                if (!isNaN(numA) && !isNaN(numB)) {
                                    comparison = numA - numB;
                                } else {
                                    comparison = cellA.localeCompare(cellB, 'id', { numeric: true, sensitivity: 'base' });
                                }

                                return newDir === 'asc' ? comparison : -comparison;
                            });

                            dataRows.forEach(row => tbody.appendChild(row));
                        });
                    });
                });
            };

            initSortableTables();
        });

        (() => {
            const chatToggle = document.getElementById('globalChatToggle');
            const chatClose = document.getElementById('globalChatClose');
            const chatWindow = document.getElementById('globalChatWindow');
            const chatBox = document.getElementById('globalChatBox');
            const chatForm = document.getElementById('globalChatForm');
            const chatInput = document.getElementById('globalChatInput');

            if (!chatToggle || !chatClose || !chatWindow || !chatBox || !chatForm || !chatInput) {
                return;
            }

            const role = 'penjaga';
            const userId = {{ Auth::id() }};
            const history = [];

            const isLocal = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');
            const chatbotBaseUrl = isLocal
                ? 'http://localhost:5000'
                : `${window.location.origin}/chatbot`;

            function formatMarkdown(text) {
                let temp = document.createElement('div');
                temp.textContent = text;
                let html = temp.innerHTML;

                html = html.replace(/^### (.*?)$/gm, '<h6 class="fw-bold my-1">$1</h6>');
                html = html.replace(/^## (.*?)$/gm, '<h5 class="fw-bold my-2">$1</h5>');
                html = html.replace(/^# (.*?)$/gm, '<h4 class="fw-bold my-2">$1</h4>');
                html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
                html = html.replace(/^- (.*?)$/gm, '• $1');
                html = html.replace(/\n/g, '<br>');

                return html;
            }

            function appendMessage(text, sender = 'bot') {
                const wrap = document.createElement('div');
                wrap.className = `d-flex ${sender === 'user' ? 'justify-content-end' : 'justify-content-start'} mb-2`;

                const bubble = document.createElement('div');
                bubble.className =
                    `px-3 py-2 rounded ${sender === 'user' ? 'global-chat-bubble-user' : 'global-chat-bubble-bot'}`;
                bubble.style.maxWidth = '82%';
                bubble.style.whiteSpace = 'pre-wrap';
                bubble.innerHTML = formatMarkdown(text);

                wrap.appendChild(bubble);
                chatBox.appendChild(wrap);
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            function appendDownloadButton(downloadPath) {
                const wrap = document.createElement('div');
                wrap.className = 'd-flex justify-content-start mb-2';

                const bubble = document.createElement('div');
                bubble.className = 'px-3 py-2 rounded global-chat-bubble-bot';
                bubble.style.maxWidth = '82%';

                const caption = document.createElement('div');
                caption.textContent = 'File laporan siap diunduh.';
                caption.style.marginBottom = '8px';

                const downloadBtn = document.createElement('a');
                downloadBtn.className = 'btn btn-primary btn-sm';
                downloadBtn.textContent = 'Download';
                downloadBtn.href = `${chatbotBaseUrl}${downloadPath}`;
                downloadBtn.setAttribute('download', '');
                downloadBtn.setAttribute('target', '_blank');
                downloadBtn.setAttribute('rel', 'noopener noreferrer');

                bubble.appendChild(caption);
                bubble.appendChild(downloadBtn);
                wrap.appendChild(bubble);
                chatBox.appendChild(wrap);
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            function openChat() {
                chatWindow.classList.add('show');
                chatWindow.setAttribute('aria-hidden', 'false');
                chatInput.focus();
            }

            function closeChat() {
                chatWindow.classList.remove('show');
                chatWindow.setAttribute('aria-hidden', 'true');
            }

            async function sendMessage(message) {
                appendMessage(message, 'user');
                history.push({
                    message,
                    isUser: true
                });

                try {
                    const resp = await fetch(`${chatbotBaseUrl}/api/chat`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message,
                            history,
                            role,
                            user_id: userId
                        })
                    });

                    const data = await resp.json();
                    const botMsg = data?.message || 'Tidak ada respons dari chatbot.';
                    appendMessage(botMsg, 'bot');
                    history.push({
                        message: botMsg,
                        isUser: false
                    });

                    if (data?.download_url) {
                        appendDownloadButton(data.download_url);
                    }
                } catch (err) {
                    appendMessage('Gagal terhubung ke server chatbot Python. Pastikan server berjalan.', 'bot');
                }
            }

            appendMessage('Halo Penjaga. Saya siap bantu pantau SAR, pendaki aktif, dan rekap laporan.');

            chatToggle.addEventListener('click', openChat);
            chatClose.addEventListener('click', closeChat);

            chatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const message = chatInput.value.trim();
                if (!message) return;
                chatInput.value = '';
                await sendMessage(message);
            });
        })();
    </script>

    @stack('scripts')
</body>

</html>
