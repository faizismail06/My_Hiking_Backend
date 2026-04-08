@extends('layouts.admin-modern')

@section('page-title', 'Tambah Jalur')
@section('page-subtitle', 'Tambahkan jalur pendakian baru ke sistem')

@section('main-content')
<div class="modern-card animate-fade-in">
    <div class="card-header">
        <h5><i class="fas fa-route"></i> Form Tambah Jalur</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('trails.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Jalur</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="id_gunung" class="form-label">Gunung <span class="text-danger">*</span></label>
                    <select id="id_gunung" name="id_gunung" class="form-select @error('id_gunung') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Gunung</option>
                        @foreach ($mountains as $gunung)
                            <option value="{{ $gunung->id }}">{{ $gunung->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_gunung')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Jalur <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Masukkan nama jalur">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="province_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                    <select id="province_id" name="province_id" class="form-select @error('province_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Provinsi</option>
                        @foreach ($province_id as $province)
                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                    @error('province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="regency_id" class="form-label">Kabupaten <span class="text-danger">*</span></label>
                    <select id="regency_id" name="regency_id" class="form-select @error('regency_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Kabupaten</option>
                        @foreach ($regency_id as $regency)
                            <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                        @endforeach
                    </select>
                    @error('regency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="district_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                    <select id="district_id" name="district_id" class="form-select @error('district_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Kecamatan</option>
                        @foreach ($district_id as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                    @error('district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="village_id" class="form-label">Desa <span class="text-danger">*</span></label>
                    <select id="village_id" name="village_id" class="form-select @error('village_id') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Desa</option>
                        @foreach ($village_id as $village)
                            <option value="{{ $village->id }}">{{ $village->name }}</option>
                        @endforeach
                    </select>
                    @error('village_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Jarak (km) <span class="text-danger">*</span></label>
                    <input type="text" name="jarak" class="form-control @error('jarak') is-invalid @enderror" value="{{ old('jarak') }}" placeholder="5.5">
                    @error('jarak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Elevasi (m)</label>
                    <input type="text" name="elevasi" class="form-control @error('elevasi') is-invalid @enderror" value="{{ old('elevasi') }}" placeholder="1200">
                    @error('elevasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Durasi (jam)</label>
                    <input type="text" name="durasi" class="form-control @error('durasi') is-invalid @enderror" value="{{ old('durasi') }}" placeholder="6">
                    @error('durasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tingkat Kesulitan</label>
                    <select name="tingkat_kesulitan" class="form-select @error('tingkat_kesulitan') is-invalid @enderror">
                        <option value="" disabled {{ old('tingkat_kesulitan') ? '' : 'selected' }}>Pilih tingkat</option>
                        <option value="mudah" {{ old('tingkat_kesulitan') === 'mudah' ? 'selected' : '' }}>Mudah</option>
                        <option value="sedang" {{ old('tingkat_kesulitan') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="sulit" {{ old('tingkat_kesulitan') === 'sulit' ? 'selected' : '' }}>Sulit</option>
                        <option value="sangat_sulit" {{ old('tingkat_kesulitan') === 'sangat_sulit' ? 'selected' : '' }}>Sangat Sulit</option>
                    </select>
                    @error('tingkat_kesulitan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Biaya (Rp) <span class="text-danger">*</span></label>
                    <input type="text" name="biaya" class="form-control @error('biaya') is-invalid @enderror" value="{{ old('biaya') }}" placeholder="25000">
                    @error('biaya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gambar Jalur</label>
                    <input type="file" class="form-control" id="gambar_jalur" name="gambar_jalur" accept="image/*">
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Masukkan deskripsi jalur">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Link Map Basecamp</label>
                    <input type="text" name="map_basecamp" class="form-control @error('map_basecamp') is-invalid @enderror" value="{{ old('map_basecamp') }}" placeholder="https://maps.google.com/...">
                    @error('map_basecamp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Upload GPX Jalur</label>
                    <input type="file" id="gpx_file_input" class="form-control @error('gpx_file') is-invalid @enderror" name="gpx_file" accept=".gpx,.xml">
                    @error('gpx_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Format .gpx/.xml, maksimal 10MB</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sumber Jalur</label>
                    <select name="route_source" class="form-select @error('route_source') is-invalid @enderror">
                        <option value="manual" {{ old('route_source') === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="wikiloc" {{ old('route_source') === 'wikiloc' ? 'selected' : '' }}>Wikiloc</option>
                        <option value="gps_survey" {{ old('route_source') === 'gps_survey' ? 'selected' : '' }}>GPS Survey</option>
                    </select>
                    @error('route_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12" id="map-editor">
                    <label class="form-label">Editor Jalur dan Titik Pos</label>
                    <textarea name="route_points_json" id="route_points_json_input" class="d-none">{{ old('route_points_json', '[]') }}</textarea>
                    <textarea name="trail_posts_json" id="trail_posts_json_input" class="d-none">{{ old('trail_posts_json', '[]') }}</textarea>

                    @error('route_points_json')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    @error('trail_posts_json')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                    <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
                        <select id="mode-selector" class="form-select form-select-sm" style="max-width: 260px;">
                            <option value="route">Mode Tambah Titik Jalur</option>
                            <option value="post">Mode Tambah Pos</option>
                            <option value="edit_route">Mode Edit Titik Jalur</option>
                            <option value="multi_delete">Mode Pilih Banyak Node</option>
                        </select>
                        <button type="button" id="set-start-btn" class="btn btn-sm btn-outline-modern">Jadikan Start</button>
                        <button type="button" id="set-finish-btn" class="btn btn-sm btn-outline-modern">Jadikan Finish</button>
                        <button type="button" id="swap-start-finish-btn" class="btn btn-sm btn-outline-modern">Tukar Start/Finish</button>
                        <button type="button" id="delete-action-btn" class="btn btn-sm btn-warning-modern">Hapus</button>
                    </div>
                    <div id="trail-map-editor" style="height: 380px; border-radius: 12px; border: 1px solid #dbe3ec;"></div>
                    <small id="map-editor-stats" class="text-muted d-block mt-2"></small>
                    <small class="text-muted d-block mt-1">Gunakan tombol Hapus sesuai mode aktif: route (hapus titik terakhir), post (hapus pos terakhir), edit (hapus titik terpilih), pilih banyak node (hapus semua node terpilih). Pilih node di mode Edit lalu gunakan Jadikan Start/Jadikan Finish jika urutan tertukar.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" placeholder="-7.2575">
                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" placeholder="112.7521">
                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <h6 class="text-muted mb-3"><i class="fas fa-user-shield me-2"></i>Data Penjaga Jalur</h6>
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Sistem akan otomatis membuat akun untuk penjaga jalur. Password default: <strong>password123</strong>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Penjaga <span class="text-danger">*</span></label>
                    <input type="text" name="penjaga_name" class="form-control @error('penjaga_name') is-invalid @enderror" value="{{ old('penjaga_name') }}" placeholder="Masukkan nama penjaga">
                    @error('penjaga_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Penjaga <span class="text-danger">*</span></label>
                    <input type="email" name="penjaga_email" class="form-control @error('penjaga_email') is-invalid @enderror" value="{{ old('penjaga_email') }}" placeholder="email@example.com">
                    @error('penjaga_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="penjaga_phone" class="form-control @error('penjaga_phone') is-invalid @enderror" value="{{ old('penjaga_phone') }}" placeholder="08xxxxxxxxxx">
                    @error('penjaga_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat (Opsional)</label>
                    <textarea name="penjaga_address" class="form-control" rows="2" placeholder="Masukkan alamat penjaga">{{ old('penjaga_address') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('trails.index') }}" class="btn btn-modern btn-outline-modern">Batal</a>
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .route-special-marker {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        position: relative;
    }

    .route-start-marker {
        background: #16a34a;
    }

    .route-finish-marker {
        background: #dc2626;
    }

    .route-camp-marker {
        background: #ea580c;
    }

    .route-camp-marker .camp-seq {
        position: absolute;
        right: -6px;
        bottom: -6px;
        min-width: 14px;
        height: 14px;
        border-radius: 999px;
        background: #111827;
        color: #fff;
        font-size: 9px;
        line-height: 14px;
        text-align: center;
        border: 1px solid #fff;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    $('#province_id').change(function() {
        let provinceId = $(this).val();
        $('#regency_id').empty().append('<option value="" disabled selected>Loading...</option>');
        $('#district_id').empty().append('<option value="" disabled selected>Pilih Kecamatan</option>');
        $('#village_id').empty().append('<option value="" disabled selected>Pilih Desa</option>');
        $.get(`/get-regencies/${provinceId}`, function(data) {
            $('#regency_id').empty().append('<option value="" disabled selected>Pilih Kabupaten</option>');
            $.each(data, function(index, regency) {
                $('#regency_id').append(`<option value="${regency.id}">${regency.name}</option>`);
            });
        });
    });

    $('#regency_id').change(function() {
        let regencyId = $(this).val();
        $('#district_id').empty().append('<option value="" disabled selected>Loading...</option>');
        $('#village_id').empty().append('<option value="" disabled selected>Pilih Desa</option>');
        $.get(`/get-districts/${regencyId}`, function(data) {
            $('#district_id').empty().append('<option value="" disabled selected>Pilih Kecamatan</option>');
            $.each(data, function(index, district) {
                $('#district_id').append(`<option value="${district.id}">${district.name}</option>`);
            });
        });
    });

    $('#district_id').change(function() {
        let districtId = $(this).val();
        $('#village_id').empty().append('<option value="" disabled selected>Loading...</option>');
        $.get(`/get-villages/${districtId}`, function(data) {
            $('#village_id').empty().append('<option value="" disabled selected>Pilih Desa</option>');
            $.each(data, function(index, village) {
                $('#village_id').append(`<option value="${village.id}">${village.name}</option>`);
            });
        });
    });
});

(function () {
    const mapContainer = document.getElementById('trail-map-editor');
    if (!mapContainer || typeof L === 'undefined') {
        return;
    }

    const routeInput = document.getElementById('route_points_json_input');
    const postsInput = document.getElementById('trail_posts_json_input');
    const gpxFileInput = document.getElementById('gpx_file_input');
    const statsLabel = document.getElementById('map-editor-stats');
    const modeSelector = document.getElementById('mode-selector');
    const setStartBtn = document.getElementById('set-start-btn');
    const setFinishBtn = document.getElementById('set-finish-btn');
    const swapStartFinishBtn = document.getElementById('swap-start-finish-btn');
    const deleteActionBtn = document.getElementById('delete-action-btn');
    const latitudeInput = document.querySelector('input[name="latitude"]');
    const longitudeInput = document.querySelector('input[name="longitude"]');

    const parseJsonString = (value, fallback = []) => {
        if (typeof value !== 'string') {
            return fallback;
        }

        const trimmed = value.trim();
        if (trimmed === '') {
            return fallback;
        }

        try {
            return JSON.parse(trimmed);
        } catch (_) {
            return fallback;
        }
    };

    const initialRoutePoints = parseJsonString(routeInput?.value, []);
    const initialPosts = parseJsonString(postsInput?.value, []);

    const toRows = (rows) => {
        if (Array.isArray(rows)) {
            return rows;
        }

        if (rows && typeof rows === 'object') {
            return Object.values(rows);
        }

        if (typeof rows === 'string' && rows.trim() !== '') {
            try {
                return toRows(JSON.parse(rows));
            } catch (_) {
                return [];
            }
        }

        return [];
    };

    const normalizeRoutePoints = (rows) => toRows(rows)
        .map((item) => {
            const lat = Number(item?.lat ?? item?.latitude);
            const lng = Number(item?.lng ?? item?.lon ?? item?.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return null;
            }
            return { lat, lng };
        })
        .filter(Boolean);

    const normalizePosts = (rows) => toRows(rows)
        .map((item, index) => {
            const lat = Number(item?.lat ?? item?.latitude);
            const lng = Number(item?.lng ?? item?.lon ?? item?.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return null;
            }
            return {
                name: (item?.name || '').toString().trim() || `Pos ${index + 1}`,
                lat,
                lng,
                icon_type: (item?.icon_type || 'signpost').toString(),
                elevation: item?.elevation ?? null,
                description: item?.description ?? null,
            };
        })
        .filter(Boolean);

    let routePoints = normalizeRoutePoints(initialRoutePoints);
    let posts = normalizePosts(initialPosts);
    let mode = 'route';
    let selectedRoutePointIndex = null;
    let selectedPostIndex = null;
    const selectedRoutePointIndexes = new Set();
    let suppressMapClickUntil = 0;
    let polylineLayer = null;
    let polylineOutlineLayer = null;
    const markerLayer = L.layerGroup();

    const map = L.map('trail-map-editor', {
        zoomControl: true,
        center: [-7.4, 110.4],
        zoom: 8,
    });

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    markerLayer.addTo(map);

    const modeLabel = () => {
        if (mode === 'post') {
            return selectedPostIndex === null
                ? 'Tambah Pos'
                : `Tambah Pos (pos #${selectedPostIndex + 1} terpilih)`;
        }
        if (mode === 'multi_delete') {
            return `Pilih Banyak Node (${selectedRoutePointIndexes.size} dipilih)`;
        }
        if (mode === 'edit_route') {
            const selectedRole =
                selectedRoutePointIndex === 0
                    ? ' (Start)'
                    : (selectedRoutePointIndex === routePoints.length - 1 ? ' (Finish)' : '');
            return selectedRoutePointIndex === null
                ? 'Edit Titik Jalur (pilih titik dulu)'
                : `Edit Titik Jalur (titik #${selectedRoutePointIndex + 1}${selectedRole} terpilih)`;
        }
        return 'Tambah Titik Jalur';
    };

    const selectRoutePoint = (index) => {
        selectedRoutePointIndex = index;
        selectedPostIndex = null;
        selectedRoutePointIndexes.clear();
        if (mode !== 'edit_route') {
            mode = 'edit_route';
            syncModeSelector();
        }
        drawLayers(false);
    };

    const selectPost = (index) => {
        selectedPostIndex = index;
        selectedRoutePointIndex = null;
        selectedRoutePointIndexes.clear();
        if (mode !== 'post') {
            mode = 'post';
            syncModeSelector();
        }
        drawLayers(false);
    };

    const toggleRoutePointSelection = (index) => {
        selectedRoutePointIndex = null;
        selectedPostIndex = null;
        if (mode !== 'multi_delete') {
            mode = 'multi_delete';
            syncModeSelector();
        }

        if (selectedRoutePointIndexes.has(index)) {
            selectedRoutePointIndexes.delete(index);
        } else {
            selectedRoutePointIndexes.add(index);
        }
        drawLayers(false);
    };

    const syncModeSelector = () => {
        if (modeSelector) {
            modeSelector.value = mode;
        }
    };

    const updateBasecampInputs = (lat, lng) => {
        if (latitudeInput) {
            latitudeInput.value = Number(lat).toFixed(6);
        }
        if (longitudeInput) {
            longitudeInput.value = Number(lng).toFixed(6);
        }
    };

    const getBasecampLatLng = () => {
        if (!latitudeInput || !longitudeInput) {
            return null;
        }

        const lat = Number(latitudeInput.value);
        const lng = Number(longitudeInput.value);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return null;
        }

        return { lat, lng };
    };

    const findNearestRoutePointIndex = (lat, lng, maxDistanceMeters = 150) => {
        if (routePoints.length === 0) {
            return null;
        }

        let nearestIndex = null;
        let nearestDistance = Number.POSITIVE_INFINITY;
        routePoints.forEach((point, index) => {
            const distance = map.distance([lat, lng], [point.lat, point.lng]);
            if (distance < nearestDistance) {
                nearestDistance = distance;
                nearestIndex = index;
            }
        });

        return nearestDistance <= maxDistanceMeters ? nearestIndex : null;
    };

    const removeSelectedRoutePoint = () => {
        if (selectedRoutePointIndex === null) {
            window.alert('Pilih titik jalur terlebih dahulu (klik titik hijau).');
            return;
        }

        if (routePoints.length <= 2) {
            window.alert('Jalur minimal 2 titik. Gunakan Reset Jalur jika ingin menghapus semua titik.');
            return;
        }

        routePoints.splice(selectedRoutePointIndex, 1);
        selectedRoutePointIndex = null;
        drawLayers(false);
    };

    const removeSelectedRoutePoints = () => {
        if (selectedRoutePointIndexes.size === 0) {
            window.alert('Belum ada node yang dipilih. Gunakan Mode Pilih Banyak Node lalu klik beberapa node.');
            return;
        }

        if ((routePoints.length - selectedRoutePointIndexes.size) < 2) {
            window.alert('Penghapusan dibatalkan. Jalur minimal harus menyisakan 2 titik.');
            return;
        }

        routePoints = routePoints.filter((_, index) => !selectedRoutePointIndexes.has(index));
        selectedRoutePointIndexes.clear();
        selectedRoutePointIndex = null;
        drawLayers(false);
    };

    const moveSelectedPointToStart = () => {
        if (selectedRoutePointIndex === null) {
            window.alert('Pilih titik dulu di Mode Edit, lalu klik Jadikan Start.');
            return;
        }

        if (selectedRoutePointIndex === 0) {
            window.alert('Titik terpilih sudah menjadi Start.');
            return;
        }

        const selectedPoint = routePoints.splice(selectedRoutePointIndex, 1)[0];
        routePoints.unshift(selectedPoint);
        selectedRoutePointIndex = 0;
        selectedPostIndex = null;
        selectedRoutePointIndexes.clear();
        mode = 'edit_route';
        syncModeSelector();
        drawLayers(false);
    };

    const moveSelectedPointToFinish = () => {
        if (selectedRoutePointIndex === null) {
            window.alert('Pilih titik dulu di Mode Edit, lalu klik Jadikan Finish.');
            return;
        }

        const finishIndex = routePoints.length - 1;
        if (selectedRoutePointIndex === finishIndex) {
            window.alert('Titik terpilih sudah menjadi Finish.');
            return;
        }

        const selectedPoint = routePoints.splice(selectedRoutePointIndex, 1)[0];
        routePoints.push(selectedPoint);
        selectedRoutePointIndex = routePoints.length - 1;
        selectedPostIndex = null;
        selectedRoutePointIndexes.clear();
        mode = 'edit_route';
        syncModeSelector();
        drawLayers(false);
    };

    const swapStartFinish = () => {
        if (routePoints.length < 2) {
            window.alert('Minimal perlu 2 titik jalur untuk menukar Start/Finish.');
            return;
        }

        routePoints.reverse();
        selectedRoutePointIndex = null;
        selectedPostIndex = null;
        selectedRoutePointIndexes.clear();
        drawLayers(false);
    };

    const performDeleteAction = () => {
        if (mode === 'multi_delete') {
            removeSelectedRoutePoints();
            return;
        }

        if (mode === 'edit_route') {
            removeSelectedRoutePoint();
            return;
        }

        if (mode === 'post') {
            if (posts.length === 0) {
                window.alert('Tidak ada pos untuk dihapus.');
                return;
            }

            if (selectedPostIndex !== null) {
                posts.splice(selectedPostIndex, 1);
                selectedPostIndex = null;
            } else {
                posts.pop();
            }

            drawLayers(false);
            return;
        }

        if (mode === 'route') {
            if (routePoints.length === 0) {
                window.alert('Tidak ada titik jalur untuk dihapus.');
                return;
            }
            routePoints.pop();
            selectedRoutePointIndex = null;
            selectedPostIndex = null;
            selectedRoutePointIndexes.clear();
            drawLayers();
        }
    };

    const syncHiddenFields = () => {
        routeInput.value = JSON.stringify(routePoints);
        postsInput.value = JSON.stringify(
            posts.map((item, index) => ({
                ...item,
                sequence: index + 1,
            }))
        );

        statsLabel.textContent = `Titik jalur: ${routePoints.length} | Pos: ${posts.length} | Mode aktif: ${modeLabel()}`;
    };

    const drawLayers = (fitView = true) => {
        if (polylineLayer) {
            map.removeLayer(polylineLayer);
        }
        if (polylineOutlineLayer) {
            map.removeLayer(polylineOutlineLayer);
        }
        markerLayer.clearLayers();

        if (routePoints.length > 0) {
            polylineOutlineLayer = L.polyline(
                routePoints.map((point) => [point.lat, point.lng]),
                { color: '#ffffff', weight: 9, opacity: 0.92 }
            ).addTo(map);

            polylineLayer = L.polyline(
                routePoints.map((point) => [point.lat, point.lng]),
                { color: '#dc2626', weight: 5, opacity: 0.98 }
            ).addTo(map);

            const startIcon = L.divIcon({
                className: '',
                html: '<div class="route-special-marker route-start-marker"><i class="fas fa-play"></i></div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15],
            });

            L.marker([routePoints[0].lat, routePoints[0].lng], { icon: startIcon })
                .bindTooltip('Start')
                .on('click', () => selectRoutePoint(0))
                .addTo(markerLayer);

            if (routePoints.length > 1) {
                const finishIcon = L.divIcon({
                    className: '',
                    html: '<div class="route-special-marker route-finish-marker"><i class="fas fa-flag-checkered"></i></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15],
                });

                L.marker([routePoints[routePoints.length - 1].lat, routePoints[routePoints.length - 1].lng], { icon: finishIcon })
                    .bindTooltip('Finish')
                    .on('click', () => selectRoutePoint(routePoints.length - 1))
                    .addTo(markerLayer);
            }
        }

        routePoints.forEach((point, index) => {
            const isSelected =
                (mode === 'edit_route' && selectedRoutePointIndex === index) ||
                (mode === 'multi_delete' && selectedRoutePointIndexes.has(index));

            if (isSelected) {
                L.circleMarker([point.lat, point.lng], {
                    radius: 11,
                    color: '#1d4ed8',
                    fillColor: '#60a5fa',
                    fillOpacity: 0.28,
                    weight: 2,
                }).addTo(markerLayer);
            }

            const handleRoutePointClick = (event) => {
                const domEvent = event?.originalEvent || event;
                if (domEvent && typeof domEvent.preventDefault === 'function') {
                    domEvent.preventDefault();
                }
                if (domEvent && typeof domEvent.stopPropagation === 'function') {
                    domEvent.stopPropagation();
                    L.DomEvent.stop(domEvent);
                }

                suppressMapClickUntil = Date.now() + 300;
                if (mode === 'multi_delete') {
                    toggleRoutePointSelection(index);
                    return;
                }

                selectRoutePoint(index);
            };

            L.circleMarker([point.lat, point.lng], {
                radius: 10,
                color: 'transparent',
                fillColor: 'transparent',
                fillOpacity: 0,
                opacity: 0,
                weight: 0,
            })
                .on('click', handleRoutePointClick)
                .addTo(markerLayer);

            L.circleMarker([point.lat, point.lng], {
                radius: isSelected ? 6 : 5,
                color: isSelected ? '#1e3a8a' : '#117958',
                fillColor: isSelected ? '#2563eb' : '#117958',
                fillOpacity: 1,
                weight: isSelected ? 2 : 1,
            })
                .on('click', handleRoutePointClick)
                .addTo(markerLayer);
        });

        posts.forEach((post, index) => {
            const isPostSelected = mode === 'post' && selectedPostIndex === index;

            if (isPostSelected) {
                L.circleMarker([post.lat, post.lng], {
                    radius: 13,
                    color: '#1d4ed8',
                    fillColor: '#60a5fa',
                    fillOpacity: 0.28,
                    weight: 2,
                }).addTo(markerLayer);
            }

            const handlePostClick = (event) => {
                const domEvent = event?.originalEvent || event;
                if (domEvent && typeof domEvent.preventDefault === 'function') {
                    domEvent.preventDefault();
                }
                if (domEvent && typeof domEvent.stopPropagation === 'function') {
                    domEvent.stopPropagation();
                    L.DomEvent.stop(domEvent);
                }

                suppressMapClickUntil = Date.now() + 300;
                selectPost(index);
            };

            const icon = L.divIcon({
                className: '',
                html: `<div class="route-special-marker route-camp-marker"><i class="fas fa-campground"></i><span class="camp-seq">${index + 1}</span></div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15],
            });

            L.marker([post.lat, post.lng], { icon })
                .bindPopup(`<strong>${post.name}</strong>`)
                .on('click', handlePostClick)
                .addTo(markerLayer);
        });

        const basecamp = getBasecampLatLng();
        if (basecamp) {
            const basecampIcon = L.divIcon({
                className: '',
                html: '<div class="route-special-marker" style="background:#2563eb;"><i class="fas fa-campground"></i></div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15],
            });

            const basecampMarker = L.marker([basecamp.lat, basecamp.lng], {
                icon: basecampIcon,
                draggable: true,
            })
                .bindTooltip('Basecamp (drag untuk geser posisi)')
                .addTo(markerLayer);

            basecampMarker.on('dragstart', () => {
                suppressMapClickUntil = Date.now() + 300;
            });

            basecampMarker.on('dragend', (event) => {
                const nextPos = event.target.getLatLng();
                updateBasecampInputs(nextPos.lat, nextPos.lng);
                suppressMapClickUntil = Date.now() + 300;
                drawLayers(false);
            });
        }

        if (fitView && routePoints.length > 1) {
            map.fitBounds(routePoints.map((point) => [point.lat, point.lng]), {
                padding: [20, 20],
                maxZoom: 15,
            });
        } else if (fitView && posts.length > 0) {
            map.setView([posts[0].lat, posts[0].lng], 13);
        } else if (fitView) {
            map.setView([-7.4, 110.4], 8);
        }

        syncHiddenFields();
    };

    map.on('click', (event) => {
        if (Date.now() < suppressMapClickUntil) {
            return;
        }

        if (mode === 'route') {
            selectedRoutePointIndex = null;
            selectedPostIndex = null;
            selectedRoutePointIndexes.clear();
            routePoints.push({ lat: event.latlng.lat, lng: event.latlng.lng });
            drawLayers(false);
            return;
        }

        if (mode === 'edit_route') {
            if (routePoints.length === 0) {
                window.alert('Belum ada titik jalur untuk diedit.');
                return;
            }

            if (selectedRoutePointIndex === null) {
                window.alert('Pilih node jalur terlebih dahulu (klik titik hijau sampai muncul lingkaran biru).');
                return;
            }

            routePoints[selectedRoutePointIndex] = { lat: event.latlng.lat, lng: event.latlng.lng };
            selectedRoutePointIndex = null;
            selectedPostIndex = null;
            drawLayers(false);
            return;
        }

        if (mode === 'multi_delete') {
            return;
        }

        const defaultLabel = `Pos ${posts.length + 1}`;
        const label = window.prompt('Nama pos:', defaultLabel);
        if (label === null) {
            return;
        }

        posts.push({
            name: label.trim() || defaultLabel,
            lat: event.latlng.lat,
            lng: event.latlng.lng,
            icon_type: 'signpost',
            elevation: null,
            description: null,
        });
        selectedPostIndex = posts.length - 1;
        drawLayers(false);
    });

    const parseGpxPreviewPoints = (xmlText) => {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlText, 'application/xml');
        const parserErrors = xmlDoc.getElementsByTagName('parsererror');
        if (parserErrors && parserErrors.length > 0) {
            throw new Error('Format GPX tidak valid.');
        }

        let pointNodes = Array.from(xmlDoc.getElementsByTagName('trkpt'));
        if (pointNodes.length === 0) {
            pointNodes = Array.from(xmlDoc.getElementsByTagName('rtept'));
        }

        const parsed = pointNodes
            .map((node) => {
                const lat = Number(node.getAttribute('lat'));
                const lng = Number(node.getAttribute('lon'));
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return null;
                }
                return { lat, lng };
            })
            .filter(Boolean);

        if (parsed.length < 2) {
            throw new Error('GPX minimal harus memiliki 2 titik jalur.');
        }

        return parsed;
    };

    if (gpxFileInput) {
        gpxFileInput.addEventListener('change', async (event) => {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            try {
                const text = await file.text();
                const parsed = parseGpxPreviewPoints(text);
                routePoints = parsed;
                selectedRoutePointIndex = null;
                selectedPostIndex = null;
                selectedRoutePointIndexes.clear();
                mode = 'route';
                syncModeSelector();
                drawLayers(true);
            } catch (error) {
                window.alert(error && error.message ? error.message : 'Gagal membaca file GPX.');
            }
        });
    }

    if (modeSelector) {
        modeSelector.addEventListener('change', () => {
            mode = modeSelector.value || 'route';
            selectedRoutePointIndex = null;
            selectedPostIndex = null;
            selectedRoutePointIndexes.clear();
            syncModeSelector();
            syncHiddenFields();
        });
    }

    deleteActionBtn.addEventListener('click', () => {
        performDeleteAction();
    });

    if (setStartBtn) {
        setStartBtn.addEventListener('click', () => {
            moveSelectedPointToStart();
        });
    }

    if (setFinishBtn) {
        setFinishBtn.addEventListener('click', () => {
            moveSelectedPointToFinish();
        });
    }

    if (swapStartFinishBtn) {
        swapStartFinishBtn.addEventListener('click', () => {
            swapStartFinish();
        });
    }

    if (latitudeInput) {
        latitudeInput.addEventListener('change', () => {
            drawLayers(false);
        });
    }

    if (longitudeInput) {
        longitudeInput.addEventListener('change', () => {
            drawLayers(false);
        });
    }

    document.addEventListener('keydown', (event) => {
        if (mode !== 'edit_route' && mode !== 'multi_delete' && mode !== 'post') {
            return;
        }

        if (event.key === 'Delete' || event.key === 'Backspace') {
            const activeTag = document.activeElement?.tagName || '';
            if (activeTag === 'INPUT' || activeTag === 'TEXTAREA' || document.activeElement?.isContentEditable) {
                return;
            }

            event.preventDefault();
            performDeleteAction();
        }
    });

    syncModeSelector();
    drawLayers();
    setTimeout(() => {
        map.invalidateSize();
        drawLayers();
    }, 50);
})();
</script>
@endsection

