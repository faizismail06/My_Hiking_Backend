@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Tata Tertib</h1>

        <form action="{{ route('tata_tertib.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="jalur_id">Jalur</label>
                <select name="jalur_id" id="jalur_id" class="form-control" required>
                    @foreach ($jalurs as $jalur)
                        <option value="{{ $jalur->id }}">{{ $jalur->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>x`
    </div>
@endsection