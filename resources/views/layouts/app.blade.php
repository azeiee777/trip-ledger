<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($header) ? $header . ' — TripLedger' : 'TripLedger' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak]{display:none!important}
        * { box-sizing: border-box; }

        /* ── Sidebar ─────────────────────────── */
        .sidebar {
            width: 256px;
            background: linear-gradient(180deg, #1a1740 0%, #16133a 100%);
            border-right: 1px solid rgba(255,255,255,.06);
            display: flex; flex-direction: column;
            position: fixed; top: 0; bottom: 0; left: 0; height: 100vh; z-index: 30;
            transition: transform .2s ease;
        }
        .sidebar-logo-wrap {
            height: 64px;
            display: flex; align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            gap: 10px; text-decoration: none; flex-shrink: 0;
        }
        .sidebar-logo-icon {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(99,102,241,.45);
        }
        .sidebar-logo-text { font-size: 17px; font-weight: 800; color: #fff; letter-spacing: -.01em; }
        .sidebar-logo-dot  { font-size: 17px; font-weight: 800; color: #8b5cf6; }

        .sidebar-nav { flex: 1; padding: 14px 12px; overflow-y: auto; }
        .sidebar-section-label {
            font-size: 10px; font-weight: 700; color: rgba(255,255,255,.25);
            letter-spacing: .1em; text-transform: uppercase;
            padding: 0 10px; margin: 16px 0 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 12px; border-radius: 12px;
            font-size: 13px; font-weight: 600; color: rgba(255,255,255,.5);
            text-decoration: none; transition: all .15s; margin-bottom: 2px;
            position: relative;
        }
        .nav-item svg { flex-shrink: 0; opacity: .7; transition: opacity .15s; }
        .nav-item:hover { background: rgba(255,255,255,.06); color: rgba(255,255,255,.85); }
        .nav-item:hover svg { opacity: 1; }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(99,102,241,.3), rgba(139,92,246,.2));
            color: #fff;
            border: 1px solid rgba(99,102,241,.3);
            box-shadow: 0 2px 12px rgba(99,102,241,.2);
        }
        .nav-item.active svg { opacity: 1; }
        .nav-item.active::before {
            content: ''; position: absolute; left: 0; top: 25%; bottom: 25%;
            width: 3px; border-radius: 0 3px 3px 0;
            background: linear-gradient(180deg,#818cf8,#a78bfa);
        }
        .nav-badge {
            margin-left: auto; font-size: 10px; font-weight: 800;
            background: rgba(99,102,241,.35); color: #a5b4fc;
            padding: 1px 7px; border-radius: 99px;
        }

        /* Sidebar user footer */
        .sidebar-user {
            padding: 14px 12px;
            border-top: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
        }
        .sidebar-user-inner {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 10px; border-radius: 12px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.07);
        }
        .sidebar-user-name  { font-size: 12px; font-weight: 700; color: #fff; }
        .sidebar-user-email { font-size: 10px; color: rgba(255,255,255,.35); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-logout {
            width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0; background: none; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,.3); transition: all .15s;
        }
        .sidebar-logout:hover { background: rgba(239,68,68,.15); color: #f87171; }

        /* ── Header ──────────────────────────── */
        .app-header {
            height: 64px; background: #fff;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center;
            padding: 0 28px; gap: 14px;
            position: sticky; top: 0; z-index: 20;
            box-shadow: 0 1px 0 #f1f5f9;
        }
        .header-title {
            flex: 1; font-size: 16px; font-weight: 800; color: #111827;
            letter-spacing: -.01em;
        }
        .header-new-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; font-size: 13px; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 4px 12px rgba(99,102,241,.35);
            transition: all .2s;
        }
        .header-new-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(99,102,241,.45); }

        /* ── Alerts ─────────────────────────── */
        .alert {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 12px;
            font-size: 13px; font-weight: 500; margin: 16px 28px 0;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

        /* ── Body bg ─────────────────────────── */
        .app-body { background: #f6f7fb; min-height: 100vh; }
        .app-main  { padding: 24px 28px; }

        /* ── Main content wrapper ────────────── */
        .app-wrap { padding-left: 256px; }

        /* ── Mobile overlay ─────────────────── */
        .sidebar-overlay {
            position: fixed; inset: 0; z-index: 20;
            background: rgba(0,0,0,.6); backdrop-filter: blur(2px);
        }

        /* ── Responsive breakpoints ──────────── */
        @media (max-width: 1023px) {
            .app-wrap { padding-left: 0; }
            .app-main { padding: 14px 16px; }
            .app-header { padding: 0 16px; gap: 10px; }
            .header-new-btn { padding: 8px 14px; font-size: 12px; }
            .alert { margin: 10px 16px 0; }
        }
        @media (max-width: 480px) {
            .header-title { font-size: 14px; }
            .header-new-btn span { display: none; }
        }
    </style>
</head>
<body class="h-full app-body" style="font-family:Inter,sans-serif" x-data="{ sidebarOpen: false }">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false" class="sidebar-overlay lg:hidden"></div>

{{-- ===== SIDEBAR ===== --}}
<aside class="sidebar"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       style="transform: translateX(0);"
       :style="window.innerWidth < 1024 ? (sidebarOpen ? 'transform:translateX(0)' : 'transform:translateX(-100%)') : 'transform:translateX(0)'">

    {{-- Logo --}}
    <a href="{{ route('dashboard') }}" class="sidebar-logo-wrap">
        <div class="sidebar-logo-icon">
            <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
        </div>
        <span class="sidebar-logo-text">Trip<span class="sidebar-logo-dot">Ledger</span></span>
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Main</div>

        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-2a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('trips.index') }}"
           class="nav-item {{ request()->routeIs('trips*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            My Trips
        </a>

        <div class="sidebar-section-label">Account</div>

        <a href="{{ route('profile.edit') }}"
           class="nav-item {{ request()->routeIs('profile*') ? 'active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profile
        </a>
    </nav>

    {{-- User footer --}}
    <div class="sidebar-user">
        <div class="sidebar-user-inner">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff&size=80&bold=true"
                 style="width:34px;height:34px;border-radius:10px;flex-shrink:0;" alt="">
            <div style="flex:1;min-width:0;">
                <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user-email">{{ auth()->user()->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="sidebar-logout" title="Sign out">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ===== MAIN AREA ===== --}}
<div class="flex flex-col min-h-screen app-wrap">

    {{-- Header --}}
    <header class="app-header">
        <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden"
                style="color:#6b7280;background:none;border:none;cursor:pointer;padding:4px;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <h1 class="header-title">{{ $header ?? '' }}</h1>
        <a href="{{ route('trips.create') }}" class="header-new-btn">
            <svg width="14" height="14" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Trip
        </a>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
    <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
         class="alert alert-success" style="margin:16px 28px 0;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
        <button @click="show=false" style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;opacity:.6;font-size:16px;">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error" style="margin:16px 28px 0;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(session('success_otp'))
    @php $otp = session('success_otp'); @endphp
    <div class="alert alert-warning" style="margin:16px 28px 0;flex-wrap:wrap;gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        <span>{{ $otp['message'] ?? '' }}</span>
        @if(!empty($otp['otp']))
        <span style="font-size:11px;background:rgba(0,0,0,.08);padding:2px 10px;border-radius:6px;font-weight:800;letter-spacing:.08em;font-family:monospace;">
            OTP: {{ $otp['otp'] }}
        </span>
        @endif
    </div>
    @endif

    {{-- Page content --}}
    <main class="app-main flex-1">{{ $slot }}</main>
</div>

<script>
    // Fix sidebar on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            document.querySelector('aside.sidebar').style.transform = 'translateX(0)';
        }
    });
</script>

{{-- ===== Global Delete Confirmation Modal ===== --}}
<div x-data="{
        show: false,
        title: '',
        message: '',
        formId: '',
        open(detail) {
            this.title   = detail.title   || 'Confirm Delete';
            this.message = detail.message || 'This action cannot be undone.';
            this.formId  = detail.formId  || '';
            this.show    = true;
        },
        confirm() {
            if (this.formId) document.getElementById(this.formId).submit();
            this.show = false;
        }
     }"
     x-on:open-delete-modal.window="open($event.detail)"
     x-show="show"
     x-cloak
     style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">

    {{-- Backdrop --}}
    <div @click="show=false"
         style="position:absolute;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);"></div>

    {{-- Modal card --}}
    <div x-show="show" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         style="position:relative;background:#fff;border-radius:16px;padding:28px;max-width:400px;width:100%;
                box-shadow:0 20px 60px rgba(0,0,0,.2);z-index:1;">

        {{-- Warning icon --}}
        <div style="width:48px;height:48px;border-radius:12px;background:#fef2f2;border:1.5px solid #fecaca;
                    display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <svg width="22" height="22" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>

        <h3 x-text="title" style="font-size:16px;font-weight:700;color:#111827;margin-bottom:8px;"></h3>
        <p x-text="message" style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:24px;"></p>

        <div style="display:flex;gap:10px;">
            <button @click="show=false"
                    style="flex:1;padding:10px 0;border:1.5px solid #e5e7eb;background:#fff;color:#374151;
                           font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;"
                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                Cancel
            </button>
            <button @click="confirm()"
                    style="flex:1;padding:10px 0;background:#ef4444;border:none;color:#fff;
                           font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;"
                    onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                Delete
            </button>
        </div>
    </div>
</div>
</body>
</html>
