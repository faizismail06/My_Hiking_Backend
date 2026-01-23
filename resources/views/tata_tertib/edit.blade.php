@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Tata Tertib</h1>

        <form action="{{ route('tata_tertib.update', $tataTertib) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="jalur_id">Jalur</label>
                <select name="jalur_id" id="jalur_id" class="form-control" required>
                    @foreach ($jalurs as $jalur)
                        <option value="{{ $jalur->id }}" {{ $tataTertib->jalur_id == $jalur->id ? 'selected' : '' }}>
                            {{ $jalur->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" rows="5" style="width: 100%;" required>{{ $tataTertib->description }}</textarea>
        </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection