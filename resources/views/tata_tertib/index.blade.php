@extends('layouts.admin')

@section('main-content')
<div class="container bg-white p-4 rounded mt-4">
    <h1 class="mb-4 text-center" style="font-weight: bold; color:rgb(0, 0, 0);">Daftar Tata Tertib</h1>

    <a href="{{ route('tata_tertib.create') }}" class="btn mb-3" style="background-color: #FFA500; color: white;">
        <i class="fas fa-plus"></i> Create Tata Tertib
    </a>

    @if ($tataTermibs->isEmpty())
        <div class="alert alert-info">
            Belum ada data Tata Tertib yang tersedia.
        </div>
    @else
        <table class="table table-bordered">
            <thead style="background-color: #d4edda;">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Jalur</th>
                    <th scope="col">Description</th>
                    <th scope="col" class="text-center" style="width: 20%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tataTermibs as $tataTertib)
                    <tr>
                        <td>{{ $tataTertib->id }}</td>
                        <td>{{ $tataTertib->jalur->nama ?? '-' }}</td>
                        <td>{{ $tataTertib->description }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <!-- Tombol Edit -->
                                <a href="{{ route('tata_tertib.edit', $tataTertib) }}" class="btn btn-sm"
                                    style="background-color: #28a745; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('tata_tertib.destroy', $tataTertib) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this Tata Tertib?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" style="margin-left: 5px;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection