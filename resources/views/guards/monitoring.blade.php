@extends('layouts.guards')

@section('page-title', 'Pemantauan Jalur')
@section('page-subtitle', 'Monitoring pendaki di jalur ' . $trail->nama)

@section('content')
<div class="row g-4">
    <!-- Map Area (Main Panel) -->
    <div class="col-lg-9 animate-fade-in">
        <div class="modern-card" style="height: calc(100vh - 180px); min-height: 500px; display: flex; flex-direction: column; overflow: hidden;">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fas fa-map-marked-alt text-primary me-2"></i>Peta Monitor</h5>
                <div class="d-flex align-items-center gap-2">
                    <span id="sync-loader" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                    <span id="last-sync-time-label" class="text-muted small">Mengambil data...</span>
                    <button id="refresh-data-btn" class="btn btn-sm btn-outline-modern" title="Refresh Sekarang">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0 flex-grow-1 position-relative" style="height: 100%;">
                <div id="monitoring-map" style="height: 100%; width: 100%; z-index: 1;"></div>
                
                <!-- Floating Legend -->
                <div class="map-legend">
                    <h6>Legenda Status</h6>
                    <div class="legend-item">
                        <span class="legend-color bg-success"></span>
                        <span>Aktif (Sinyal Normal)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color bg-secondary"></span>
                        <span>Sinyal Hilang (> 5 mnt)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color bg-danger"></span>
                        <span>Overdue (Telat Turun)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color legend-sos-pulse"></span>
                        <span>SOS (Panic Button)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Hiker Info Panel -->
    <div class="col-lg-3 animate-fade-in" style="animation-delay: 0.1s">
        <div class="modern-card" style="height: calc(100vh - 180px); min-height: 500px; display: flex; flex-direction: column;">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Daftar Pendaki Aktif</h5>
            </div>
            
            <!-- Default Info / Hiker Detail View -->
            <div id="info-side-container" class="card-body flex-grow-1 overflow-y-auto" style="padding: 1.25rem;">
                <!-- Hiker List Summary -->
                <div id="hiker-summary-section">
                    <p class="text-muted small mb-3">Klik pendaki di peta atau daftar di bawah ini untuk melihat detail rute perjalanan mereka.</p>
                    <div id="climbers-list-group" class="list-group list-group-flush gap-2">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-user-friends fa-2x mb-2"></i>
                            <p class="small mb-0">Sedang memuat daftar pendaki...</p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Hiker Profile (Hidden by default, shown when marker is clicked) -->
                <div id="hiker-detail-section" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="mb-0 text-primary font-weight-bold" id="detail-title">Detail Pendaki</h6>
                        <button type="button" class="btn-close" id="close-detail-btn" style="font-size: 0.8rem;"></button>
                    </div>
                    
                    <div class="detail-card mb-3 text-center p-3 rounded-3" id="detail-status-bg" style="background-color: #f8fafc;">
                        <div class="user-avatar mx-auto mb-2" id="detail-avatar" style="width: 50px; height: 50px; font-size: 1.2rem; font-weight: 700;">
                            A
                        </div>
                        <h5 class="mb-1" id="detail-hiker-name">Nama Pendaki</h5>
                        <p class="text-muted small mb-0" id="detail-email">email@domain.com</p>
                        <span class="badge mt-2 px-3 py-2" id="detail-badge">Status</span>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small mb-1 d-block"><i class="fas fa-qrcode me-1"></i> ID Registrasi / Order</label>
                        <p class="mb-0 fw-semibold" id="detail-order-id">#1029</p>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block"><i class="fas fa-users me-1"></i> Rombongan</label>
                            <p class="mb-0 fw-semibold" id="detail-members">1 orang</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block"><i class="fas fa-route me-1"></i> Jarak Tempuh</label>
                            <p class="mb-0 fw-semibold" id="detail-distance">0.00 km</p>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block"><i class="fas fa-calendar-alt me-1"></i> Tanggal Naik</label>
                            <p class="mb-0 fw-semibold" id="detail-date-up">10/06/2026</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small mb-1 d-block"><i class="fas fa-calendar-check me-1"></i> Target Turun</label>
                            <p class="mb-0 fw-semibold text-danger" id="detail-date-down">11/06/2026</p>
                        </div>
                    </div>

                    <div class="mb-3 p-2 rounded-2" style="background-color: #f1f5f9; border-left: 4px solid #3b82f6;">
                        <label class="text-muted small mb-0 d-block"><i class="fas fa-clock me-1"></i> Sinkronisasi Lokasi Terakhir</label>
                        <p class="mb-0 fw-semibold text-dark small" id="detail-last-sync">10/06/2026 12:00:00</p>
                    </div>

                    <button type="button" class="btn btn-modern btn-outline-modern w-100 btn-sm justify-content-center" id="btn-fit-hiker-path">
                        <i class="fas fa-eye-slash"></i> Sembunyikan Jalur Perjalanan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Pulsing SOS Animation */
    @keyframes pulse-sos {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8);
        }
        70% {
            box-shadow: 0 0 0 12px rgba(239, 68, 68, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }

    /* Map Styles */
    .map-legend {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: white;
        padding: 10px 14px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        border: 1px solid #e2e8f0;
        max-width: 200px;
    }
    .map-legend h6 {
        margin-bottom: 8px;
        font-weight: 700;
        font-size: 0.8rem;
        color: #1e293b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        font-size: 0.75rem;
        color: #475569;
        font-weight: 500;
    }
    .legend-item:last-child {
        margin-bottom: 0;
    }
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .legend-sos-pulse {
        background: #f97316;
        animation: pulse-sos 1.5s infinite;
        border: 1px solid #fff;
    }

    /* Premium Custom Markers */
    .custom-leaflet-marker {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 13px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        border: 2px solid white;
        transition: transform 0.2s ease;
    }
    .custom-leaflet-marker:hover {
        transform: scale(1.15);
    }
    
    .marker-active {
        background-color: #22c55e; /* Green */
    }
    .marker-lost-signal {
        background-color: #64748b; /* Slate Gray */
    }
    .marker-overdue {
        background-color: #ef4444; /* Red */
    }
    
    .marker-sos-request {
        background-color: #f97316; /* Orange */
        animation: pulse-sos 1.4s infinite;
        border: 2px solid white;
    }

    /* List Group Custom */
    .hiker-list-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px !important;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .hiker-list-item:hover {
        background-color: #f8fafc;
        border-color: var(--primary-color);
        transform: translateY(-1px);
    }
    .hiker-list-item.selected {
        background-color: #e8f5f0;
        border-color: var(--primary-color);
        box-shadow: 0 4px 10px rgba(17, 121, 88, 0.1);
    }
    
    /* SOS Marker Pulse class in Leaflet map */
    .sos-beacon {
        width: 14px;
        height: 14px;
        background: #f97316;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.8);
        animation: pulse-sos 1.5s infinite;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('monitoring-map');
    if (!mapContainer || typeof L === 'undefined') return;

    // 1. Inisialisasi Peta
    const map = L.map('monitoring-map', {
        zoomControl: true,
        center: [-7.4, 110.4],
        zoom: 11
    });

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Group Layers
    const basecampLayerGroup = L.layerGroup().addTo(map);
    const trailLineLayerGroup = L.layerGroup().addTo(map);
    const hikerMarkersLayerGroup = L.layerGroup().addTo(map);
    const sosMarkersLayerGroup = L.layerGroup().addTo(map);
    const pathLayerGroup = L.layerGroup().addTo(map);

    // State Variables
    let activePathPolyline = null;
    let pathCoordinatesLoadedOrderId = null;
    let selectedHikerOrderId = null;

    // Cache Marker & Data to prevent lag (lag-free rendering)
    const hikerMarkersCache = {}; // order_id -> Leaflet Marker
    const sosMarkersCache = {}; // sos_id -> Leaflet Marker
    let activeTrailData = null;

    // Custom DivIcons
    function createHikerIcon(status) {
        let iconClass = 'marker-active';
        let iconHtml = '<i class="fas fa-hiking"></i>';
        
        if (status === 'lost_signal') {
            iconClass = 'marker-lost-signal';
            iconHtml = '<i class="fas fa-signal-mesh"></i>';
        } else if (status === 'overdue') {
            iconClass = 'marker-overdue';
            iconHtml = '<i class="fas fa-clock"></i>';
        }

        return L.divIcon({
            className: '',
            html: `<div class="custom-leaflet-marker ${iconClass}">${iconHtml}</div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -16]
        });
    }

    const sosIcon = L.divIcon({
        className: '',
        html: '<div class="custom-leaflet-marker marker-sos-request"><i class="fas fa-exclamation-triangle"></i></div>',
        iconSize: [36, 36],
        iconAnchor: [18, 18],
        popupAnchor: [0, -18]
    });

    const basecampIcon = L.divIcon({
        className: '',
        html: '<div class="custom-leaflet-marker" style="background:#2563eb; border: 2px solid white;"><i class="fas fa-campground"></i></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    // 2. Load Real-Time Data (AJAX)
    async function loadMonitoringData() {
        const syncLoader = document.getElementById('sync-loader');
        const refreshBtn = document.getElementById('refresh-data-btn');
        const lastSyncLabel = document.getElementById('last-sync-time-label');

        syncLoader.classList.remove('d-none');
        refreshBtn.disabled = true;

        try {
            const response = await fetch("{{ route('guards.monitoring.data') }}");
            const res = await response.json();
            
            if (res.success) {
                // Render Trail Route (Sekali saja saat load pertama)
                if (!activeTrailData && res.trail) {
                    activeTrailData = res.trail;
                    
                    // Plot Basecamp
                    if (res.trail.latitude && res.trail.longitude) {
                        L.marker([res.trail.latitude, res.trail.longitude], { icon: basecampIcon })
                            .bindTooltip(`Basecamp: ${res.trail.name}`, { permanent: false, direction: 'top' })
                            .addTo(basecampLayerGroup);
                        
                        map.setView([res.trail.latitude, res.trail.longitude], 12);
                    }

                    // Plot Trail Line
                    if (res.trail.route_points && res.trail.route_points.length > 0) {
                        const routeCoords = res.trail.route_points.map(pt => [pt.lat, pt.lng]);
                        
                        // Outline putih
                        L.polyline(routeCoords, { color: '#ffffff', weight: 8, opacity: 0.9 }).addTo(trailLineLayerGroup);
                        // Garis merah
                        L.polyline(routeCoords, { color: '#10b981', weight: 4, opacity: 0.95 }).addTo(trailLineLayerGroup);
                        
                        // Fit camera ke lintasan jalur
                        const bounds = L.polyline(routeCoords).getBounds();
                        map.fitBounds(bounds, { padding: [50, 50] });
                    }
                }

                // Render Hikers & SOS
                renderHikerMarkers(res.climbers);
                renderSOSMarkers(res.sos_requests);
                renderHikerList(res.climbers, res.sos_requests);

                // Auto reload active hiker's path if one is currently selected
                if (selectedHikerOrderId) {
                    loadHikerTraversedPath(selectedHikerOrderId);
                }

                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                lastSyncLabel.textContent = `Update terakhir: ${timeString}`;
            } else {
                console.error(res.message);
                lastSyncLabel.textContent = "Gagal memuat data";
            }
        } catch (error) {
            console.error("Kesalahan fetch monitoring data:", error);
            lastSyncLabel.textContent = "Koneksi bermasalah";
        } finally {
            syncLoader.classList.add('d-none');
            refreshBtn.disabled = false;
        }
    }

    // 3. Render Markers Tanpa Lag (Update Koordinat / Tambah / Hapus Marker)
    function renderHikerMarkers(climbers) {
        const activeIds = {};

        climbers.forEach(climber => {
            const orderId = climber.order_id;
            activeIds[orderId] = true;
            const latLng = [climber.latitude, climber.longitude];

            // Jika marker sudah ada di cache, perbarui koordinat dan ikonnya
            if (hikerMarkersCache[orderId]) {
                const marker = hikerMarkersCache[orderId];
                marker.setLatLng(latLng);
                marker.setIcon(createHikerIcon(climber.status));
                
                // Update popup konten
                marker.getPopup().setContent(createHikerPopupHtml(climber));
            } 
            // Jika marker belum ada, buat baru
            else {
                const marker = L.marker(latLng, { icon: createHikerIcon(climber.status) })
                    .bindPopup(createHikerPopupHtml(climber))
                    .addTo(hikerMarkersLayerGroup);
                
                marker.on('click', function() {
                    showHikerDetail(climber);
                });

                hikerMarkersCache[orderId] = marker;
            }
        });

        // Hapus marker yang sudah tidak aktif (pendaki sudah check-out / hilang dari list)
        Object.keys(hikerMarkersCache).forEach(orderId => {
            if (!activeIds[orderId]) {
                map.removeLayer(hikerMarkersCache[orderId]);
                delete hikerMarkersCache[orderId];
                
                // Jika marker yang dihapus adalah yang sedang dipilih, bersihkan path
                if (selectedHikerOrderId == orderId) {
                    closeHikerDetail();
                }
            }
        });
    }

    // Render SOS Requests Markers
    function renderSOSMarkers(sosRequests) {
        const activeSosIds = {};

        sosRequests.forEach(sos => {
            const sosId = sos.id;
            activeSosIds[sosId] = true;
            const latLng = [sos.latitude, sos.longitude];

            if (sosMarkersCache[sosId]) {
                const marker = sosMarkersCache[sosId];
                marker.setLatLng(latLng);
                marker.getPopup().setContent(createSOSPopupHtml(sos));
            } else {
                const marker = L.marker(latLng, { icon: sosIcon })
                    .bindPopup(createSOSPopupHtml(sos))
                    .addTo(sosMarkersLayerGroup);

                sosMarkersCache[sosId] = marker;
            }
        });

        // Hapus SOS marker yang sudah beres ditangani (resolved)
        Object.keys(sosMarkersCache).forEach(sosId => {
            if (!activeSosIds[sosId]) {
                map.removeLayer(sosMarkersCache[sosId]);
                delete sosMarkersCache[sosId];
            }
        });
    }

    // Popup HTML Creators
    function createHikerPopupHtml(climber) {
        let statusText = 'Aktif / Online';
        let badgeColor = 'bg-success';
        if (climber.status === 'lost_signal') {
            statusText = 'Kehilangan Sinyal';
            badgeColor = 'bg-secondary';
        } else if (climber.status === 'overdue') {
            statusText = 'Overdue (Belum Turun)';
            badgeColor = 'bg-danger';
        }

        return `
            <div style="font-family: 'Inter', sans-serif; min-width: 180px;">
                <h6 style="margin: 0 0 4px; font-weight:700; color:#1e293b;">${climber.user_name}</h6>
                <p style="margin: 0 0 6px; font-size: 11px; color:#64748b;">Order ID: #${climber.order_id}</p>
                <div class="mb-2"><span class="badge ${badgeColor}" style="font-size:10px; padding:3px 8px;">${statusText}</span></div>
                <div style="font-size: 11px; color:#475569; border-top:1px solid #f1f5f9; padding-top:6px; margin-top:6px;">
                    <div><strong>Jarak:</strong> ${(climber.distance_meters / 1000).toFixed(2)} km</div>
                    <div><strong>Durasi:</strong> ${formatTime(climber.duration_seconds)}</div>
                    <div style="margin-top:4px; font-size:10px; color:#64748b;"><strong>Terakhir:</strong> ${climber.synced_at}</div>
                </div>
            </div>
        `;
    }

    function createSOSPopupHtml(sos) {
        return `
            <div style="font-family: 'Inter', sans-serif; min-width: 200px; padding: 2px;">
                <h6 style="margin: 0 0 4px; font-weight:700; color:#dc2626;"><i class="fas fa-exclamation-triangle me-1"></i>PANGGILAN DARURAT (SOS)</h6>
                <h5 style="margin: 0 0 4px; font-size:13px; font-weight:700; color:#1e293b;">${sos.user_name}</h5>
                <div style="font-size:11px; margin-bottom: 6px;"><strong>Jenis:</strong> <span class="text-danger fw-bold">${sos.emergency_type}</span></div>
                <div style="font-size:11px; background:#fef2f2; border:1px solid #fecaca; padding:6px; border-radius:6px; margin-bottom:6px; color:#991b1b;">
                    "${sos.description || 'Tidak ada deskripsi'}"
                </div>
                <span class="badge bg-warning text-dark" style="font-size:9px; text-transform:uppercase;">${sos.status}</span>
                <div style="font-size:9px; color:#64748b; margin-top:6px;"><strong>Waktu kejadian:</strong> ${sos.created_at}</div>
            </div>
        `;
    }

    // Format Duration Helper
    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        
        let out = '';
        if (hrs > 0) out += `${hrs} jam `;
        out += `${mins} menit`;
        return out;
    }

    // 4. Render Hiker List on Sidebar
    function renderHikerList(climbers, sosRequests) {
        const group = document.getElementById('climbers-list-group');
        group.innerHTML = '';

        if (climbers.length === 0 && sosRequests.length === 0) {
            group.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                    <p class="small mb-0">Tidak ada pendaki aktif saat ini</p>
                </div>
            `;
            return;
        }

        // Render SOS items first at the top
        sosRequests.forEach(sos => {
            const item = document.createElement('div');
            item.className = 'list-group-item hiker-list-item d-flex align-items-center justify-content-between p-2 mb-2';
            item.style.backgroundColor = '#fff1f2';
            item.style.borderColor = '#fca5a5';
            
            item.innerHTML = `
                <div style="flex:1; min-width:0;">
                    <h6 class="mb-1 text-danger font-weight-bold text-truncate" style="font-size: 0.85rem;"><i class="fas fa-exclamation-triangle me-1"></i>SOS: ${sos.user_name}</h6>
                    <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">${sos.emergency_type} - ${sos.status}</small>
                </div>
                <span class="badge bg-danger p-1 animate-pulse"><i class="fas fa-bell"></i></span>
            `;

            item.addEventListener('click', () => {
                map.setView([sos.latitude, sos.longitude], 15);
                if (sosMarkersCache[sos.id]) {
                    sosMarkersCache[sos.id].openPopup();
                }
            });

            group.appendChild(item);
        });

        // Render standard hikers
        climbers.forEach(climber => {
            let statusBadge = '<span class="badge bg-success" style="font-size: 0.7rem;">Aktif</span>';
            if (climber.status === 'lost_signal') {
                statusBadge = '<span class="badge bg-secondary" style="font-size: 0.7rem;">Offline</span>';
            } else if (climber.status === 'overdue') {
                statusBadge = '<span class="badge bg-danger" style="font-size: 0.7rem;">Overdue</span>';
            }

            const isSelected = selectedHikerOrderId == climber.order_id;

            const item = document.createElement('div');
            item.className = `list-group-item hiker-list-item d-flex align-items-center justify-content-between ${isSelected ? 'selected' : ''}`;
            
            item.innerHTML = `
                <div style="flex:1; min-width:0;">
                    <h6 class="mb-1 font-weight-bold text-truncate" style="font-size: 0.85rem; color:#1e293b;">${climber.user_name}</h6>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">ID: #${climber.order_id} | Jarak: ${(climber.distance_meters / 1000).toFixed(2)} km</small>
                </div>
                <div>${statusBadge}</div>
            `;

            item.addEventListener('click', () => {
                showHikerDetail(climber);
                map.setView([climber.latitude, climber.longitude], 14);
                if (hikerMarkersCache[climber.order_id]) {
                    hikerMarkersCache[climber.order_id].openPopup();
                }
            });

            group.appendChild(item);
        });
    }

    // 5. Show Details Drawer & Dynamic Traversed Path Draw
    async function showHikerDetail(climber) {
        selectedHikerOrderId = climber.order_id;

        // Tampilkan tab detail, sembunyikan rangkuman
        document.getElementById('hiker-summary-section').classList.add('d-none');
        const detailSection = document.getElementById('hiker-detail-section');
        detailSection.classList.remove('d-none');

        // Isi data detail
        document.getElementById('detail-title').textContent = `Detail Pendaki #${climber.order_id}`;
        document.getElementById('detail-hiker-name').textContent = climber.user_name;
        document.getElementById('detail-email').textContent = climber.email;
        document.getElementById('detail-order-id').textContent = `#${climber.order_id}`;
        document.getElementById('detail-members').textContent = `${climber.total_members} orang`;
        document.getElementById('detail-distance').textContent = `${(climber.distance_meters / 1000).toFixed(2)} km`;
        document.getElementById('detail-date-up').textContent = climber.tanggal_naik;
        document.getElementById('detail-date-down').textContent = climber.tanggal_turun;
        document.getElementById('detail-last-sync').textContent = climber.synced_at;
        
        // Avatar inisial
        const avatar = document.getElementById('detail-avatar');
        avatar.textContent = climber.user_name.charAt(0).toUpperCase();

        // Atur badge status & warna background detail
        const badge = document.getElementById('detail-badge');
        const statusBg = document.getElementById('detail-status-bg');
        badge.className = 'badge mt-2 px-3 py-2 ';

        if (climber.status === 'active') {
            badge.classList.add('bg-success');
            badge.textContent = 'Aktif / Online';
            avatar.style.backgroundColor = '#d1fae5';
            avatar.style.color = '#065f46';
        } else if (climber.status === 'lost_signal') {
            badge.classList.add('bg-secondary');
            badge.textContent = 'Kehilangan Sinyal';
            avatar.style.backgroundColor = '#f1f5f9';
            avatar.style.color = '#334155';
        } else if (climber.status === 'overdue') {
            badge.classList.add('bg-danger');
            badge.textContent = 'Terlambat (Overdue)';
            avatar.style.backgroundColor = '#fee2e2';
            avatar.style.color = '#991b1b';
        }

        // Highlight selected item in sidebar list
        const items = document.querySelectorAll('.hiker-list-item');
        items.forEach(item => item.classList.remove('selected'));
        // Load data list
        loadHikerTraversedPath(climber.order_id);
    }

    // Load path traversed dynamically (AJAX)
    async function loadHikerTraversedPath(orderId) {
        // Hapus polyline sebelumnya jika ada
        pathLayerGroup.clearLayers();
        activePathPolyline = null;
        pathCoordinatesLoadedOrderId = null;

        try {
            const response = await fetch(`/guards/monitoring/path/${orderId}`);
            const data = await response.json();

            if (data.success && data.points && data.points.length > 0) {
                const pathCoords = data.points.map(pt => [pt.lat, pt.lng]);
                
                // Gambar polyline rute lintasan perjalanan hiker berwarna biru
                // Outline putih
                L.polyline(pathCoords, { color: '#ffffff', weight: 8, opacity: 0.85 }).addTo(pathLayerGroup);
                // Garis biru utama
                activePathPolyline = L.polyline(pathCoords, { color: '#2563eb', weight: 4, opacity: 0.95, dashArray: '2, 6' }).addTo(pathLayerGroup);
                
                pathCoordinatesLoadedOrderId = orderId;
                
                // Tambahkan marker kecil penanda titik-titik koordinat tracking sinkronisasi
                pathCoords.forEach((coord, idx) => {
                    if (idx > 0 && idx < pathCoords.length - 1) {
                        L.circleMarker(coord, {
                            radius: 3,
                            color: '#1d4ed8',
                            fillColor: '#3b82f6',
                            fillOpacity: 1,
                            weight: 1
                        }).addTo(pathLayerGroup);
                    }
                });
            }
        } catch (error) {
            console.error("Kesalahan memuat path pendaki:", error);
        }
    }

    function closeHikerDetail() {
        selectedHikerOrderId = null;
        document.getElementById('hiker-summary-section').classList.remove('d-none');
        document.getElementById('hiker-detail-section').classList.add('d-none');
        
        // Bersihkan polyline path perjalanan
        pathLayerGroup.clearLayers();
        activePathPolyline = null;
        pathCoordinatesLoadedOrderId = null;

        // Bersihkan highlight class di list sidebar
        const items = document.querySelectorAll('.hiker-list-item');
        items.forEach(item => item.classList.remove('selected'));
    }

    // Event Listeners
    document.getElementById('close-detail-btn').addEventListener('click', closeHikerDetail);
    document.getElementById('btn-fit-hiker-path').addEventListener('click', closeHikerDetail);
    document.getElementById('refresh-data-btn').addEventListener('click', loadMonitoringData);

    // 6. Polling Real-time (Refresh setiap 10 detik agar responsif & bebas lag)
    loadMonitoringData();
    const pollingInterval = setInterval(loadMonitoringData, 10000);

    // Hapus interval saat navigasi keluar
    window.addEventListener('beforeunload', function() {
        clearInterval(pollingInterval);
    });
});
</script>
@endpush
@endsection
