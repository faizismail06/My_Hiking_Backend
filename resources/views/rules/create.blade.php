@extends('layouts.admin-modern')

@section('page-title', 'Tambah Tata Tertib')
@section('page-subtitle', 'Tambahkan tata tertib baru untuk jalur pendakian')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-clipboard-list"></i> Form Tata Tertib</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('rules.store') }}" method="POST">
            @csrf
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="jalur_id" class="form-label">Jalur <span class="text-danger">*</span></label>
                    <select name="jalur_id" id="jalur_id" class="form-select @error('jalur_id') is-invalid @enderror" required>
                        <option value="" disabled selected>Pilih Jalur</option>
                        @foreach ($trails as $jalur)
                            <option value="{{ $jalur->id }}">{{ $jalur->nama }}</option>
                        @endforeach
                    </select>
                    @error('jalur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Deskripsi Tata Tertib <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="5" placeholder="Masukkan deskripsi tata tertib..." required>{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('rules.index') }}" class="btn btn-modern btn-outline-modern">Batal</a>
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
