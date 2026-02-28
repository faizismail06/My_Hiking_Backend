<div class="mb-3">
    <label for="nama" class="form-label">Nama Gunung</label>
    <input type="text" name="nama" class="form-control" id="nama" value="{{ old('nama', $mountain->nama ?? '') }}" required>
</div>
<div class="mb-3">
    <label for="ketinggian" class="form-label">Ketinggian (meter)</label>
    <input type="number" name="ketinggian" class="form-control" id="ketinggian" value="{{ old('ketinggian', $mountain->ketinggian ?? '') }}" required>
</div>
<div class="mb-3">
    <label for="deskripsi" class="form-label">Deskripsi</label>
    <textarea name="deskripsi" class="form-control" id="deskripsi">{{ old('deskripsi', $mountain->deskripsi ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label for="gambar" class="form-label">Gambar</label>
    <input type="file" name="gambar" class="form-control" id="gambar">
    @if (!empty($mountain->gambar))
        <img src="{{ asset('storage/' . $mountain->gambar) }}" alt="Gambar Gunung" class="img-thumbnail mt-2" width="200">
    @endif
</div>
