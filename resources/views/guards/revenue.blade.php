@extends('layouts.guards')

@section('page-title', 'Laporan Pendapatan')
@section('page-subtitle', 'Analisis pendapatan jalur ' . $trail->nama)

@section('content')
    <!-- Summary Card -->
    <div class="stat-card mb-4 animate-fade-in"
        style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="mb-1 opacity-75">Total Pendapatan {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                    {{ $year }}</p>
                <h2 class="mb-1 fw-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                <small class="opacity-75">
                    <i class="fas fa-receipt me-1"></i>
                    Dari {{ $totalPaidVisitors }} pengunjung yang sudah lunas
                </small>
            </div>
            <div style="font-size: 4rem; opacity: 0.3;">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="modern-card mb-4 animate-fade-in" style="animation-delay: 0.1s">
        <div class="card-header">
            <h5><i class="fas fa-filter"></i> Filter Periode</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('guards.revenue') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label-modern">Bulan</label>
                        <select name="bulan" class="form-control form-modern">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->locale('id')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-modern">Tahun</label>
                        <select name="tahun" class="form-control form-modern">
                            @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-modern btn-primary-modern w-100">
                            <i class="fas fa-search"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart Section -->
    @if ($dailyRevenue->count() > 0)
        <div class="modern-card mb-4 animate-fade-in" style="animation-delay: 0.2s">
            <div class="card-header">
                <h5><i class="fas fa-chart-area"></i> Grafik Pendapatan Harian</h5>
            </div>
            <div class="card-body">
                <canvas id="pendapatanChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    @endif

    <!-- Transaction Table -->
    <div class="modern-card animate-fade-in" style="animation-delay: 0.3s">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Detail Transaksi</h5>
            <span class="badge-modern badge-done">{{ $transactions->count() }} Transaksi</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Tanggal</th>
                            <th>Kode</th>
                            <th>Pendaki</th>
                            <th class="text-center">Pengunjung</th>
                            <th>Pembayaran</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $item->created_at->format('d M Y') }}</span>
                                        <small class="d-block text-muted">{{ $item->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded">{{ $item->id }}</code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($item->order->user->name, 0, 1)) }}
                                        </div>
                                        <span>{{ $item->order->user->name }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-secondary rounded-pill">{{ $item->order->orderMembers->count() + 1 }}</span>
                                </td>
                                <td>
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="fas fa-credit-card text-muted"></i>
                                        {{ $item->payment_method_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold" style="color: var(--primary-color);">
                                        Rp {{ number_format($item->total_bayar, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge-modern badge-done">{{ $item->status_pesanan }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-receipt fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                        <h6>Belum Ada Transaksi</h6>
                                        <p class="mb-0 small">Tidak ada transaksi pada periode yang dipilih</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($transactions->count() > 0)
                        <tfoot>
                            <tr style="background: #f8fafc;">
                                <td colspan="6" class="text-end fw-bold">Total Pendapatan:</td>
                                <td class="text-end fw-bold" style="color: var(--primary-color); font-size: 1.1rem;">
                                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($dailyRevenue->count() > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('pendapatanChart');
            const labels = {!! json_encode(
                $dailyRevenue->pluck('date')->map(function ($date) {
                    return \Carbon\Carbon::parse($date)->format('d M');
                }),
            ) !!};
            const data = {!! json_encode($dailyRevenue->pluck('total')) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: data,
                        borderColor: '#117958',
                        backgroundColor: 'rgba(17, 121, 88, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#117958',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'K';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endpush
