@extends('layouts.guards')

@section('page-title', 'Kelola Jalur')
@section('page-subtitle', 'Update informasi jalur pendakian ' . $trail->nama)

@section('content')
    <div class="row g-4">
        <!-- Form Update -->
        <div class="col-lg-8">
            <div class="modern-card animate-fade-in">
                <div class="card-header">
                    <h5><i class="fas fa-edit"></i> Update Informasi Jalur</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('guards.trail.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label-modern">Deskripsi Jalur</label>
                            <textarea name="deskripsi" class="form-control form-modern @error('deskripsi') is-invalid @enderror" 
                                rows="6" placeholder="Masukkan deskripsi jalur...">{{ old('deskripsi', $trail->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Jelaskan kondisi jalur, tingkat kesulitan, dan tips untuk pendaki
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern">Link Map Basecamp</label>
                            <div class="input-group">
                                <span class="input-group-text" style="border-radius: 10px 0 0 10px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                </span>
                                <input type="text" name="map_basecamp"
                                    class="form-control form-modern @error('map_basecamp') is-invalid @enderror"
                                    style="border-radius: 0 10px 10px 0;"
                                    value="{{ old('map_basecamp', $trail->map_basecamp) }}"
                                    placeholder="https://maps.google.com/...">
                                @error('map_basecamp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-link me-1"></i>
                                Link Google Maps lokasi basecamp
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern">Upload GPX Jalur</label>
                            <input type="file" id="gpx_file_input" name="gpx_file" class="form-control form-modern @error('gpx_file') is-invalid @enderror" accept=".gpx,.xml">
                            @error('gpx_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-route me-1"></i>
                                Upload GPX untuk memperbarui preview jalur di aplikasi pendaki.
                            </small>
                            @if(is_array($trail->route_points) && count($trail->route_points) > 0)
                                <small class="text-success mt-1 d-block">Titik aktif saat ini: {{ count($trail->route_points) }} titik</small>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label-modern">Sumber Jalur</label>
                            <select name="route_source" class="form-select form-modern @error('route_source') is-invalid @enderror">
                                <option value="manual" {{ old('route_source', $trail->route_source ?? 'manual') === 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="wikiloc" {{ old('route_source', $trail->route_source ?? 'manual') === 'wikiloc' ? 'selected' : '' }}>Wikiloc</option>
                                <option value="gps_survey" {{ old('route_source', $trail->route_source ?? 'manual') === 'gps_survey' ? 'selected' : '' }}>GPS Survey</option>
                            </select>
                            @error('route_source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4" id="map-editor">
                            <label class="form-label-modern">Editor Jalur dan Titik Pos</label>
                            <textarea name="route_points_json" id="route_points_json_input" class="d-none">{{ old('route_points_json', json_encode($trail->route_points ?? [])) }}</textarea>
                            <textarea name="trail_posts_json" id="trail_posts_json_input" class="d-none">{{ old('trail_posts_json', json_encode(($trail->posts ?? collect())->values()->all())) }}</textarea>

                            @error('route_points_json')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror
                            @error('trail_posts_json')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

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

                        <div class="mb-4">
                            <label class="form-label-modern">Upload Gambar Jalur</label>
                            <div class="upload-area p-4 border-2 border-dashed rounded-3 text-center" 
                                style="border-color: #e2e8f0; background: #f8fafc;">
                                <input type="file" name="gambar_jalur" id="gambar_jalur"
                                    class="d-none @error('gambar_jalur') is-invalid @enderror" accept="image/*"
                                    onchange="previewImage(this)">
                                <label for="gambar_jalur" class="mb-0 cursor-pointer" style="cursor: pointer;">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3 d-block"></i>
                                    <p class="mb-1 fw-medium">Klik untuk upload gambar baru</p>
                                    <small class="text-muted">JPG, PNG maksimal 2MB</small>
                                </label>
                            </div>
                            @error('gambar_jalur')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            
                            @if ($trail->gambar_jalur)
                                <div class="mt-3">
                                    <label class="form-label-modern">Gambar Saat Ini</label>
                                    <div class="position-relative d-inline-block">
                                        <img src="{{ asset('storage/images/' . $trail->gambar_jalur) }}" 
                                            alt="Gambar Jalur" id="preview-image"
                                            class="rounded-3" style="max-height: 200px; object-fit: cover;">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-modern btn-primary-modern">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('guards.dashboard') }}" class="btn btn-modern btn-outline-modern">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Detail Sidebar -->
        <div class="col-lg-4">
            <div class="modern-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Detail Jalur</h5>
                </div>
                <div class="card-body">
                    <div class="detail-item mb-3 pb-3 border-bottom">
                        <label class="text-muted small mb-1 d-block">Nama Jalur</label>
                        <p class="mb-0 fw-semibold">{{ $trail->nama }}</p>
                    </div>

                    <div class="detail-item mb-3 pb-3 border-bottom">
                        <label class="text-muted small mb-1 d-block">Gunung</label>
                        <p class="mb-0 fw-semibold">
                            <i class="fas fa-mountain text-primary me-1"></i>
                            {{ $trail->gunung->nama }}
                        </p>
                    </div>

                    <div class="detail-item mb-3 pb-3 border-bottom">
                        <label class="text-muted small mb-1 d-block">Lokasi</label>
                        <div class="small">
                            <p class="mb-1"><i class="fas fa-map me-1 text-muted"></i> {{ $trail->province->name }}</p>
                            <p class="mb-1 ms-3">{{ $trail->regency->name }}</p>
                            <p class="mb-1 ms-3">{{ $trail->district->name }}</p>
                            <p class="mb-0 ms-3">{{ $trail->village->name }}</p>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block">Jarak</label>
                            <p class="mb-0 fw-semibold">
                                <i class="fas fa-route text-info me-1"></i>
                                {{ $trail->jarak }} km
                            </p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block">Biaya</label>
                            <p class="mb-0 fw-bold" style="color: var(--primary-color);">
                                Rp {{ number_format($trail->biaya, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="alert alert-modern alert-info mb-0" style="background: #dbeafe; color: #1e40af;">
                        <i class="fas fa-info-circle"></i>
                        <small>Data di atas hanya dapat diubah oleh admin sistem</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
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
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('preview-image');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

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

            const serverRoutePoints = @json($trail->route_points ?? []);
            const serverPosts = @json(($trail->posts ?? collect())->values()->all());

            const parseJsonString = (value, fallback) => {
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

            const initialRoutePoints = parseJsonString(routeInput?.value, serverRoutePoints);
            const initialPosts = parseJsonString(postsInput?.value, serverPosts);

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
            if (routePoints.length === 0) {
                routePoints = normalizeRoutePoints(serverRoutePoints);
            }
            if (posts.length === 0) {
                posts = normalizePosts(serverPosts);
            }
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
                } else if (fitView && {{ $trail->latitude ?? 'null' }} && {{ $trail->longitude ?? 'null' }}) {
                    map.setView([{{ $trail->latitude ?? 0 }}, {{ $trail->longitude ?? 0 }}], 12);
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
    @endpush
@endsection

