@extends('layouts.app')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detail Gunung</title>
</head>
<body style="background: #117958">
<div class="container bg-white p-4 rounded">
                        <h1 class="text-center my-4" style="font-weight: bold; color: black;">Detail Gunung</h1>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm rounded">
                                    <div class="card-body">
                                        @if ($gunung->gambar_gunung)
                                        <img src="{{ asset('/storage/images/' . $gunung->gambar_gunung) }}" class="rounded" style="width: 100%">
                                        @else
                                        <p><strong>Tidak ada gambar</strong></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card border-1 shadow-sm rounded">
                                    <div class="card-body">
                                        <h3>{{ $gunung->nama }}</h3>
                                        <hr/>
                                        <p><strong>Provinsi:</strong> {{ $gunung->province->name ?? 'Tidak Diketahui' }}</p>
                                        <p><strong>Kabupaten/Kota:</strong> {{ $gunung->regency->name ?? 'Tidak Diketahui' }}</p>
                                        <p><strong>Kecamatan:</strong> {{ $gunung->district->name ?? 'Tidak Diketahui' }}</p>
                                        <p><strong>Desa:</strong> {{ $gunung->village->name ?? 'Tidak Diketahui' }}</p>
                                        <p><strong>Ketinggian:</strong> {{ $gunung->ketinggian }} meter</p>
                                        <hr/>
                                        <p><strong>Deskripsi:</strong></p>
                                        <code>
                                            <p>{{ $gunung->deskripsi }}</p>
                                        </code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('gunung.index') }}" class="btn" style="background-color: #117958; color: white; border: none;">Kembali ke Daftar Gunung</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
@endsection
