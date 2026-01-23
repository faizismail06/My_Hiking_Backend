<!-- @extends('layouts.admin')

@section('main-content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('Gunung') }}</h1>

    @if (session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-left-danger" role="alert">
            <ul class="pl-4 my-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Gunung</h6>
                </div>

                <div class="card-body">
                    <a href="{{ route('gunung.create') }}" class="btn btn-primary mb-3">Tambah Gunung</a>
                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Gunung</th>
                                <th>Provinsi</th>
                                <th>Kabupaten</th>
                                <th>Kecamatan</th>
                                <th>Desa</th>
                                <th>Ketinggian</th>
                                <th>Gambar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($gunungs as $gunung)
                                <tr>
                                    <td>{{ $gunung->id }}</td>
                                    <td>{{ $gunung->nama }}</td>
                                    <td>{{ $gunung->province_id }}</td>
                                    <td>{{ $gunung->regency_id }}</td>
                                    <td>{{ $gunung->district_id }}</td>
                                    <td>{{ $gunung->village_id }}</td>
                                    <td>{{ $gunung->ketinggian }} mdpl</td>
                                    <td>
                                        <img src="{{ asset('storage/' . $gunung->gambar_gunung) }}" alt="{{ $gunung->nama }}" width="80">
                                    </td>
                                    <td>
                                        <a href="{{ route('gunung.edit', $gunung) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('gunung.destroy', $gunung) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data gunung</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection -->
