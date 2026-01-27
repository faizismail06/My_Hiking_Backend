@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Laporan Pendapatan</h1>
            <a href="{{ route('penjaga.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Filter Periode -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filter Periode</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('penjaga.pendapatan') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Pendapatan {{ \Carbon\Carbon::create()->month($bulan)->format('F') }}
                                    {{ $tahun }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                                </div>
                                <small class="text-muted">Dari {{ $transaksi->count() }} transaksi berhasil</small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Pendapatan Per Hari -->
        @if ($pendapatanPerHari->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Grafik Pendapatan Per Hari</h6>
                </div>
                <div class="card-body">
                    <canvas id="pendapatanChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        @endif

        <!-- Tabel Detail Transaksi -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Transaksi - {{ $jalur->nama }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode Transaksi</th>
                                <th>Nama Pendaki</th>
                                <th>Jumlah Anggota</th>
                                <th>Metode Pembayaran</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksi as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                    <td><code>{{ $item->id }}</code></td>
                                    <td>{{ $item->pesanan->user->name }}</td>
                                    <td class="text-center">{{ $item->pesanan->anggota->count() }} orang</td>
                                    <td>{{ $item->payment->nama ?? 'N/A' }}</td>
                                    <td class="text-right font-weight-bold text-success">
                                        Rp {{ number_format($item->total_bayar, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge badge-success">{{ $item->status_pesanan }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada transaksi pada periode ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($transaksi->count() > 0)
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="6" class="text-right">Total:</td>
                                    <td class="text-right text-success">Rp
                                        {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($pendapatanPerHari->count() > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('pendapatanChart');
            const labels = {!! json_encode(
                $pendapatanPerHari->pluck('tanggal')->map(function ($date) {
                    return \Carbon\Carbon::parse($date)->format('d M');
                }),
            ) !!};
            const data = {!! json_encode($pendapatanPerHari->pluck('total')) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: data,
                        borderColor: 'rgb(28, 200, 138)',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endsection
