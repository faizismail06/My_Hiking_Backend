@extends('layouts.guards')

@section('page-title', 'SAR Dashboard')
@section('page-subtitle', 'Kelola permintaan darurat dari pendaki')

@section('content')
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.1s">
            <div class="stat-card" style="border-left: 4px solid #e74c3c;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Menunggu Respons</p>
                        <h3 class="value mb-0">{{ $countPending }}</h3>
                    </div>
                    <div class="icon" style="background: rgba(231, 76, 60, 0.15); color: #e74c3c;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.2s">
            <div class="stat-card" style="border-left: 4px solid #f39c12;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Sedang Ditangani</p>
                        <h3 class="value mb-0">{{ $countResponding }}</h3>
                    </div>
                    <div class="icon" style="background: rgba(243, 156, 18, 0.15); color: #f39c12;">
                        <i class="fas fa-ambulance"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="stat-card" style="border-left: 4px solid #27ae60;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="label mb-1">Selesai</p>
                        <h3 class="value mb-0">{{ $countResolved }}</h3>
                    </div>
                    <div class="icon" style="background: rgba(39, 174, 96, 0.15); color: #27ae60;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="modern-card mb-4 animate-fade-in">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('guards.sar.index', ['filter' => 'all']) }}" 
                   class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-list me-1"></i> Semua
                </a>
                <a href="{{ route('guards.sar.index', ['filter' => 'pending']) }}" 
                   class="btn {{ $filter === 'pending' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="fas fa-clock me-1"></i> Menunggu ({{ $countPending }})
                </a>
                <a href="{{ route('guards.sar.index', ['filter' => 'responding']) }}" 
                   class="btn {{ $filter === 'responding' ? 'btn-warning' : 'btn-outline-warning' }}">
                    <i class="fas fa-ambulance me-1"></i> Ditangani ({{ $countResponding }})
                </a>
                <a href="{{ route('guards.sar.index', ['filter' => 'resolved']) }}" 
                   class="btn {{ $filter === 'resolved' ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-check-circle me-1"></i> Selesai ({{ $countResolved }})
                </a>
            </div>
        </div>
    </div>

    <!-- Panic Requests List -->
    <div class="modern-card animate-fade-in">
        <div class="card-header">
            <h5><i class="fas fa-exclamation-triangle text-danger me-2"></i>Daftar Permintaan Darurat</h5>
        </div>
        <div class="card-body p-0">
            @forelse($panicRequests as $panic)
                <div class="panic-item p-4 border-bottom {{ $panic->status === 'pending' ? 'bg-danger-subtle' : '' }}">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="panic-icon {{ $panic->status === 'pending' ? 'pulse-animation' : '' }}" 
                                     style="width: 50px; height: 50px; border-radius: 50%; 
                                            background: {{ $panic->status === 'pending' ? '#e74c3c' : ($panic->status === 'responding' ? '#f39c12' : '#27ae60') }}; 
                                            display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas {{ $panic->status === 'pending' ? 'fa-exclamation' : ($panic->status === 'responding' ? 'fa-ambulance' : 'fa-check') }} fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ $panic->emergency_type }}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-user me-1"></i>{{ $panic->user->name ?? 'N/A' }}
                                    </p>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-ticket-alt me-1"></i>Order #{{ $panic->order_id }}
                                    </p>
                                    <p class="mb-0 text-muted small">
                                        <i class="fas fa-clock me-1"></i>{{ $panic->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            @if($panic->status === 'pending')
                                <span class="badge bg-danger fs-6">Menunggu Respons</span>
                            @elseif($panic->status === 'responding')
                                <span class="badge bg-warning text-dark fs-6">Sedang Ditangani</span>
                                <p class="small mt-1 mb-0 text-muted">oleh {{ $panic->responder->name ?? 'N/A' }}</p>
                            @else
                                <span class="badge bg-success fs-6">Selesai</span>
                            @endif
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                <a href="{{ route('guards.sar.show', $panic->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                @if($panic->status === 'pending')
                                    <form action="{{ route('guards.sar.respond', $panic->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fas fa-hand-paper me-1"></i>Respons
                                        </button>
                                    </form>
                                @elseif($panic->status === 'responding')
                                    <form action="{{ route('guards.sar.resolve', $panic->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i>Selesai
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($panic->description)
                        <div class="mt-2 pt-2 border-top">
                            <p class="mb-0 small"><strong>Keterangan:</strong> {{ $panic->description }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">Tidak ada permintaan darurat</h5>
                    <p class="text-muted small">Semua pendaki dalam kondisi aman</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        .pulse-animation {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(231, 76, 60, 0); }
            100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); }
        }
        .bg-danger-subtle {
            background-color: rgba(231, 76, 60, 0.05) !important;
        }
        .panic-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* ===== SOS MODAL STYLES ===== */
        #sosModal .modal-dialog {
            max-width: 640px;
        }
        #sosModal .modal-content {
            background: #1a0000;
            border: 3px solid #ff0000;
            border-radius: 16px;
            overflow: hidden;
        }
        #sosModal .sos-header {
            background: linear-gradient(135deg, #c0392b, #8b0000);
            padding: 28px 32px 20px;
            text-align: center;
            position: relative;
        }
        #sosModal .sos-header .sos-icon-ring {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            animation: sosPulse 1s ease-in-out infinite;
        }
        #sosModal .sos-header h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 1px;
            margin: 0;
            text-shadow: 0 0 20px rgba(255,100,100,0.8);
        }
        #sosModal .sos-body {
            padding: 28px 32px;
        }
        #sosModal .sos-info-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 12px;
        }
        #sosModal .sos-info-row .sos-info-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: rgba(231,76,60,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff6b6b;
            flex-shrink: 0;
        }
        #sosModal .sos-info-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
            margin-bottom: 2px;
        }
        #sosModal .sos-info-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
        }
        #sosModal .sos-emergency-badge {
            display: inline-block;
            background: rgba(231,76,60,0.3);
            border: 1px solid #e74c3c;
            color: #ff8080;
            border-radius: 6px;
            padding: 2px 10px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 18px;
        }
        #btnTerimaCall {
            width: 100%;
            padding: 18px;
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: 2px;
            border-radius: 12px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            color: #fff;
            box-shadow: 0 0 30px rgba(231,76,60,0.7), 0 6px 20px rgba(0,0,0,0.4);
            transition: transform 0.15s, box-shadow 0.15s;
            animation: btnPulse 1.8s ease-in-out infinite;
        }
        #btnTerimaCall:hover {
            transform: scale(1.03);
            box-shadow: 0 0 50px rgba(231,76,60,0.9), 0 8px 25px rgba(0,0,0,0.5);
        }
        #btnTerimaCall:active { transform: scale(0.98); }

        @keyframes sosPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,80,80,0.7), 0 0 0 10px rgba(255,80,80,0.3); }
            50%       { box-shadow: 0 0 0 18px rgba(255,80,80,0), 0 0 0 30px rgba(255,80,80,0); }
        }
        @keyframes btnPulse {
            0%, 100% { box-shadow: 0 0 20px rgba(231,76,60,0.6), 0 6px 20px rgba(0,0,0,0.4); }
            50%       { box-shadow: 0 0 45px rgba(231,76,60,1),   0 6px 25px rgba(0,0,0,0.5); }
        }
        @keyframes shakeModal {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }
        .sos-shake { animation: shakeModal 0.5s ease-in-out; }

        /* Striped alert bar at top of modal */
        .sos-stripe-bar {
            height: 8px;
            background: repeating-linear-gradient(
                45deg,
                #e74c3c,
                #e74c3c 10px,
                #ff0 10px,
                #ff0 20px
            );
            animation: stripeScroll 1s linear infinite;
            background-size: 200% 100%;
        }
        @keyframes stripeScroll {
            0%   { background-position: 0 0; }
            100% { background-position: 40px 0; }
        }
    </style>
@endsection

{{-- ===== SOS FORCED MODAL ===== --}}
@push('modals')
<div class="modal fade" id="sosModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false"
     aria-labelledby="sosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            {{-- Stripe bar --}}
            <div class="sos-stripe-bar"></div>

            {{-- Header --}}
            <div class="sos-header">
                <div class="sos-info-row mb-3 justify-content-center" style="background:transparent;border:none;">
                    <div class="sos-icon-ring">
                        <i class="fas fa-exclamation-triangle fa-3x text-white"></i>
                    </div>
                </div>
                <h2 id="sosModalLabel">🚨 PERMINTAAN DARURAT MASUK!</h2>
                <p class="text-white-50 mt-2 mb-0" style="font-size:0.9rem;">
                    Segera tangani permintaan SOS ini
                </p>
            </div>

            {{-- Body --}}
            <div class="sos-body">
                <div class="text-center mb-3">
                    <span class="sos-emergency-badge" id="sosEmergencyType">—</span>
                </div>

                <div class="sos-info-row">
                    <div class="sos-info-icon"><i class="fas fa-user fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Nama Pendaki</div>
                        <div class="sos-info-value" id="sosHikerName">—</div>
                    </div>
                </div>

                <div class="sos-info-row">
                    <div class="sos-info-icon"><i class="fas fa-route fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Jalur / Gunung</div>
                        <div class="sos-info-value" id="sosTrailName">—</div>
                    </div>
                </div>

                <div class="sos-info-row">
                    <div class="sos-info-icon"><i class="fas fa-clock fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Waktu SOS</div>
                        <div class="sos-info-value" id="sosTime">—</div>
                    </div>
                </div>

                <div class="sos-info-row" id="sosDescRow" style="display:none;">
                    <div class="sos-info-icon"><i class="fas fa-notes-medical fa-lg"></i></div>
                    <div>
                        <div class="sos-info-label">Keterangan</div>
                        <div class="sos-info-value" id="sosDesc" style="font-size:0.92rem;font-weight:500;">—</div>
                    </div>
                </div>

                <div class="mt-4">
                    <button id="btnTerimaCall" type="button">
                        <i class="fas fa-phone-alt me-2"></i>TERIMA PANGGILAN
                    </button>
                </div>

                <p class="text-center text-muted mt-3 mb-0" style="font-size:0.78rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    Anda akan diarahkan ke halaman detail setelah menerima panggilan
                </p>
            </div>

            <div class="sos-stripe-bar"></div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    /* ------------------------------------------------------------------ */
    /*  CONFIG                                                              */
    /* ------------------------------------------------------------------ */
    const POLL_INTERVAL_MS  = 5000;          // 5 seconds
    const API_URL           = '{{ route("guards.sar.check-new-panics") }}';
    const ALARM_FREQ        = 850;           // Hz
    const ALARM_VOLUME      = 0.5;
    const ALARM_BEEP_ON_MS  = 800;           // beep on duration
    const ALARM_BEEP_OFF_MS = 400;           // silence between beeps
    const ALARM_CYCLE_MS    = 3000;          // full SOS pattern repeats every 3s

    /* ------------------------------------------------------------------ */
    /*  STATE                                                               */
    /* ------------------------------------------------------------------ */
    let lastSeenId      = 0;      // tracks highest panic id already alerted
    let currentPanicId  = null;   // id shown in the modal right now
    let currentDetailUrl = null;  // redirect URL for "TERIMA PANGGILAN"
    let alarmCtx        = null;   // Web Audio API context
    let alarmNodes      = [];     // active oscillator/gain nodes
    let alarmTimer      = null;   // setInterval handle for looping
    let pollTimer       = null;   // setInterval handle for polling
    let modalInstance   = null;   // Bootstrap modal instance

    /* ------------------------------------------------------------------ */
    /*  ALARM — Web Audio API                                               */
    /* ------------------------------------------------------------------ */
    function startAlarm() {
        stopAlarm(); // never double-play

        try {
            alarmCtx = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.warn('Web Audio API not supported:', e);
            return;
        }

        function playBeep(startTime, durationSec) {
            const osc  = alarmCtx.createOscillator();
            const gain = alarmCtx.createGain();

            osc.type      = 'square';
            osc.frequency.setValueAtTime(ALARM_FREQ, startTime);

            gain.gain.setValueAtTime(0, startTime);
            gain.gain.linearRampToValueAtTime(ALARM_VOLUME, startTime + 0.01);
            gain.gain.setValueAtTime(ALARM_VOLUME, startTime + durationSec - 0.02);
            gain.gain.linearRampToValueAtTime(0, startTime + durationSec);

            osc.connect(gain);
            gain.connect(alarmCtx.destination);

            osc.start(startTime);
            osc.stop(startTime + durationSec);

            alarmNodes.push({ osc, gain });
        }

        function scheduleSOSPattern() {
            if (!alarmCtx) return;
            const now = alarmCtx.currentTime;

            // SOS: · · · — — — · · ·  (simplified: 3 short + pause)
            const shortSec  = ALARM_BEEP_ON_MS  / 1000;
            const pauseSec  = ALARM_BEEP_OFF_MS / 1000;

            let t = now;
            for (let i = 0; i < 3; i++) {
                playBeep(t, shortSec);
                t += shortSec + pauseSec;
            }
            // gap before next cycle
        }

        scheduleSOSPattern();
        alarmTimer = setInterval(scheduleSOSPattern, ALARM_CYCLE_MS);
    }

    function stopAlarm() {
        clearInterval(alarmTimer);
        alarmTimer = null;

        alarmNodes.forEach(function (n) {
            try { n.osc.stop();  } catch (_) {}
            try { n.osc.disconnect();  } catch (_) {}
            try { n.gain.disconnect(); } catch (_) {}
        });
        alarmNodes = [];

        if (alarmCtx) {
            try { alarmCtx.close(); } catch (_) {}
            alarmCtx = null;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  MODAL                                                               */
    /* ------------------------------------------------------------------ */
    function getModal() {
        if (!modalInstance) {
            const el = document.getElementById('sosModal');
            modalInstance = new bootstrap.Modal(el, {
                backdrop: 'static',
                keyboard: false
            });
        }
        return modalInstance;
    }

    function populateModal(panic) {
        document.getElementById('sosEmergencyType').textContent = panic.emergency_type || 'DARURAT';
        document.getElementById('sosHikerName').textContent     = panic.hiker_name;
        document.getElementById('sosTrailName').textContent     =
            panic.mountain_name !== 'N/A'
                ? panic.trail_name + ' — ' + panic.mountain_name
                : panic.trail_name;
        document.getElementById('sosTime').textContent          = panic.created_at;

        const descRow = document.getElementById('sosDescRow');
        if (panic.description) {
            document.getElementById('sosDesc').textContent = panic.description;
            descRow.style.display = 'flex';
        } else {
            descRow.style.display = 'none';
        }

        currentDetailUrl = panic.detail_url;
        currentPanicId   = panic.id;
    }

    function showSOSModal(panic) {
        populateModal(panic);
        startAlarm();

        const modal = getModal();
        modal.show();

        // Shake the modal dialog for extra drama after it's shown
        const dialogEl = document.querySelector('#sosModal .modal-dialog');
        setTimeout(function () {
            dialogEl.classList.add('sos-shake');
            setTimeout(function () { dialogEl.classList.remove('sos-shake'); }, 600);
        }, 400);

        // Shake again every 8 seconds while modal is open
        const shakeLoop = setInterval(function () {
            const m = document.getElementById('sosModal');
            if (!m.classList.contains('show')) {
                clearInterval(shakeLoop);
                return;
            }
            dialogEl.classList.add('sos-shake');
            setTimeout(function () { dialogEl.classList.remove('sos-shake'); }, 600);
        }, 8000);
    }

    function closeSOSModal() {
        stopAlarm();
        const modal = getModal();
        modal.hide();
        currentPanicId  = null;
        currentDetailUrl = null;
    }

    /* ------------------------------------------------------------------ */
    /*  ACCEPT BUTTON                                                       */
    /* ------------------------------------------------------------------ */
    document.getElementById('btnTerimaCall').addEventListener('click', function () {
        const url = currentDetailUrl;
        closeSOSModal();
        if (url) {
            window.location.href = url;
        }
    });

    /* ------------------------------------------------------------------ */
    /*  POLLING                                                             */
    /* ------------------------------------------------------------------ */
    function checkNewPanics() {
        // If a modal is already open, don't overlap — just keep polling
        const modalEl = document.getElementById('sosModal');
        if (modalEl && modalEl.classList.contains('show')) {
            return;
        }

        fetch(API_URL + '?last_seen_id=' + lastSeenId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            if (data.panics && data.panics.length > 0) {
                // Show alert for the first/latest unread panic
                const latest = data.panics[data.panics.length - 1];

                // Advance the cursor so we don't re-alert
                lastSeenId = latest.id;

                showSOSModal(latest);
            }
        })
        .catch(function (err) {
            console.warn('[SOS Polling] Error:', err);
        });
    }

    /* ------------------------------------------------------------------ */
    /*  INIT                                                                */
    /* ------------------------------------------------------------------ */
    // Initialise lastSeenId from the highest id already on-page
    // so a guard loading the dashboard doesn't get alerted for old panics
    (function seedLastSeenId() {
        fetch(API_URL + '?last_seen_id=0', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.panics && d.panics.length > 0) {
                // On first load: just seed the cursor — don't alarm for existing panics
                // (guard may already be aware). Comment this block out if you WANT
                // the alarm to fire immediately on page load for any pending panics.
                lastSeenId = d.panics[d.panics.length - 1].id;
            }
            // Start polling AFTER seeding
            pollTimer = setInterval(checkNewPanics, POLL_INTERVAL_MS);
        })
        .catch(function () {
            // Still start polling even if seed fails
            pollTimer = setInterval(checkNewPanics, POLL_INTERVAL_MS);
        });
    })();

    // Cleanup on page leave
    window.addEventListener('beforeunload', function () {
        clearInterval(pollTimer);
        stopAlarm();
    });

})();
</script>
@endpush
