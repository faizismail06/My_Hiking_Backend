<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tidak Ada Jalur - MyHiking</title>

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
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #117958 0%, #0d5c43 50%, #074d32 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .no-trail-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 80px rgba(0,0,0,0.2);
        }

        .icon-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .icon-container i {
            font-size: 3.5rem;
            color: var(--accent-color);
        }

        h2 {
            color: var(--secondary-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .description {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .description strong {
            color: var(--primary-color);
        }

        .info-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            color: #0369a1;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-box i {
            font-size: 1.25rem;
        }

        .btn-modern {
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
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
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(17,121,88,0.3);
        }

        .btn-outline-modern {
            background: transparent;
            border: 2px solid #e2e8f0;
            color: #64748b;
        }

        .btn-outline-modern:hover {
            border-color: #dc2626;
            background: #fee2e2;
            color: #dc2626;
        }

        .brand-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }

        .brand-footer img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
        }

        .brand-footer span {
            font-weight: 700;
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="no-trail-card">
        <div class="icon-container">
            <i class="fas fa-route"></i>
        </div>
        
        <h2>Belum Ada Jalur Ditugaskan</h2>
        
        <p class="description">
            Halo <strong>{{ $user->name }}</strong>, saat ini Anda belum ditugaskan untuk mengelola jalur pendakian manapun.
        </p>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <span>Silakan hubungi administrator untuk mendapatkan tugas pengelolaan jalur.</span>
        </div>
        
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <a href="{{ route('profile') }}" class="btn btn-modern btn-primary-modern">
                <i class="fas fa-user"></i> Lihat Profil
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-modern btn-outline-modern w-100">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>

        <div class="brand-footer d-flex align-items-center justify-content-center gap-2">
            <img src="{{ asset('img/logo.png') }}" alt="MyHiking">
            <span>MyHiking</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
