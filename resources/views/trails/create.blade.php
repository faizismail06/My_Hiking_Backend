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

                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" id="mode-route-btn" class="btn btn-sm btn-primary-modern">Mode Tambah Titik Jalur</button>
                        <button type="button" id="mode-post-btn" class="btn btn-sm btn-outline-modern">Mode Tambah Pos</button>
                        <button type="button" id="mode-edit-route-btn" class="btn btn-sm btn-outline-modern">Mode Edit Titik Jalur</button>
                        <button type="button" id="mode-multi-select-route-btn" class="btn btn-sm btn-outline-modern">Mode Pilih Banyak Node</button>
                        <button type="button" id="delete-action-btn" class="btn btn-sm btn-warning-modern">Hapus</button>
                    </div>
                    <div id="trail-map-editor" style="height: 380px; border-radius: 12px; border: 1px solid #dbe3ec;"></div>
                    <small id="map-editor-stats" class="text-muted d-block mt-2"></small>
                    <small class="text-muted d-block mt-1">Gunakan tombol Hapus sesuai mode aktif: route (hapus titik terakhir), post (hapus pos terakhir), edit (hapus titik terpilih), pilih banyak node (hapus semua node terpilih).</small>
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
    .post-index-marker {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        background: #ea580c;
        border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
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
    const modeRouteBtn = document.getElementById('mode-route-btn');
    const modePostBtn = document.getElementById('mode-post-btn');
    const modeEditRouteBtn = document.getElementById('mode-edit-route-btn');
    const modeMultiSelectRouteBtn = document.getElementById('mode-multi-select-route-btn');
    const deleteActionBtn = document.getElementById('delete-action-btn');

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
            return 'Tambah Pos';
        }
        if (mode === 'multi_delete') {
            return `Pilih Banyak Node (${selectedRoutePointIndexes.size} dipilih)`;
        }
        if (mode === 'edit_route') {
            return selectedRoutePointIndex === null
                ? 'Edit Titik Jalur (pilih titik dulu)'
                : `Edit Titik Jalur (titik #${selectedRoutePointIndex + 1} terpilih)`;
        }
        return 'Tambah Titik Jalur';
    };

    const selectRoutePoint = (index) => {
        selectedRoutePointIndex = index;
        selectedRoutePointIndexes.clear();
        if (mode !== 'edit_route') {
            mode = 'edit_route';
            updateModeButtons();
        }
        drawLayers(false);
    };

    const toggleRoutePointSelection = (index) => {
        selectedRoutePointIndex = null;
        if (mode !== 'multi_delete') {
            mode = 'multi_delete';
            updateModeButtons();
        }

        if (selectedRoutePointIndexes.has(index)) {
            selectedRoutePointIndexes.delete(index);
        } else {
            selectedRoutePointIndexes.add(index);
        }
        drawLayers(false);
    };

    const updateModeButtons = () => {
        [modeRouteBtn, modePostBtn, modeEditRouteBtn, modeMultiSelectRouteBtn].forEach((button) => {
            button.classList.remove('btn-primary-modern');
            button.classList.add('btn-outline-modern');
        });

        const activeButton =
            mode === 'post'
                ? modePostBtn
                : (mode === 'edit_route'
                    ? modeEditRouteBtn
                    : (mode === 'multi_delete' ? modeMultiSelectRouteBtn : modeRouteBtn));
        activeButton.classList.remove('btn-outline-modern');
        activeButton.classList.add('btn-primary-modern');
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
            posts.pop();
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

            L.circleMarker([routePoints[0].lat, routePoints[0].lng], {
                radius: 6,
                color: '#065f46',
                fillColor: '#10b981',
                fillOpacity: 1,
                weight: 2,
            }).bindTooltip('Start').addTo(markerLayer);

            L.circleMarker([routePoints[routePoints.length - 1].lat, routePoints[routePoints.length - 1].lng], {
                radius: 6,
                color: '#7f1d1d',
                fillColor: '#ef4444',
                fillOpacity: 1,
                weight: 2,
            }).bindTooltip('Finish').addTo(markerLayer);
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
            const icon = L.divIcon({
                className: '',
                html: `<div class="post-index-marker">${index + 1}</div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });

            L.marker([post.lat, post.lng], { icon })
                .bindPopup(`<strong>${post.name}</strong>`)
                .addTo(markerLayer);
        });

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
                selectedRoutePointIndexes.clear();
                mode = 'route';
                updateModeButtons();
                drawLayers(true);
            } catch (error) {
                window.alert(error && error.message ? error.message : 'Gagal membaca file GPX.');
            }
        });
    }

    modeRouteBtn.addEventListener('click', () => {
        mode = 'route';
        selectedRoutePointIndex = null;
        selectedRoutePointIndexes.clear();
        updateModeButtons();
        syncHiddenFields();
    });

    modePostBtn.addEventListener('click', () => {
        mode = 'post';
        selectedRoutePointIndex = null;
        selectedRoutePointIndexes.clear();
        updateModeButtons();
        syncHiddenFields();
    });

    modeEditRouteBtn.addEventListener('click', () => {
        mode = 'edit_route';
        selectedRoutePointIndex = null;
        selectedRoutePointIndexes.clear();
        updateModeButtons();
        syncHiddenFields();
    });

    modeMultiSelectRouteBtn.addEventListener('click', () => {
        mode = 'multi_delete';
        selectedRoutePointIndex = null;
        selectedRoutePointIndexes.clear();
        updateModeButtons();
        syncHiddenFields();
    });

    deleteActionBtn.addEventListener('click', () => {
        performDeleteAction();
    });

    document.addEventListener('keydown', (event) => {
        if (mode !== 'edit_route' && mode !== 'multi_delete') {
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

    updateModeButtons();
    drawLayers();
    setTimeout(() => {
        map.invalidateSize();
        drawLayers();
    }, 50);
})();
</script>
@endsection

