<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sign In — TripLedger</title>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Inter, sans-serif; height: 100vh; display: flex; overflow: hidden; background: #f6f7fb; }
  .auth-left {
    width: 420px; flex-shrink: 0;
    background: linear-gradient(160deg, #0f0c29 0%, #1a1740 35%, #312e81 70%, #4f46e5 100%);
    display: flex; flex-direction: column; justify-content: space-between;
    padding: 44px 40px; position: relative; overflow: hidden;
  }
  .auth-left::before {
    content: ''; position: absolute; top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(99,102,241,.25) 0%, transparent 70%);
    pointer-events: none;
  }
  .auth-left::after {
    content: ''; position: absolute; bottom: -60px; left: -60px;
    width: 250px; height: 250px;
    background: radial-gradient(circle, rgba(139,92,246,.2) 0%, transparent 70%);
    pointer-events: none;
  }
  .auth-right {
    flex: 1; display: flex; align-items: center; justify-content: center;
    padding: 40px; overflow-y: auto;
  }
  .form-card {
    width: 100%; max-width: 400px;
  }
  .form-input {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px;
    padding: 11px 14px; font-size: 14px; color: #111827;
    font-family: Inter, sans-serif; outline: none; transition: border-color .15s;
    background: #fff;
  }
  .form-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
  .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .btn-primary {
    width: 100%; padding: 13px; border: none; border-radius: 11px; cursor: pointer;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff; font-size: 15px; font-weight: 700; font-family: Inter, sans-serif;
    box-shadow: 0 4px 14px rgba(99,102,241,.35); transition: all .2s;
  }
  .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.45); }
  .feature-item { display: flex; align-items: center; gap: 12px; }
  .feature-dot {
    width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.12);
    display: flex; align-items: center; justify-content: center;
  }
  @media (max-width: 768px) {
    .auth-left { display: none; }
    .auth-right { padding: 24px 20px; }
  }
</style>
</head>
<body>

{{-- ── Left branding panel ──────────────────────────────── --}}
<div class="auth-left">
  <div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:56px;">
      <div style="width:40px;height:40px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
        <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
        </svg>
      </div>
      <span style="font-size:21px;font-weight:800;color:#fff;letter-spacing:-.3px;">Trip<span style="color:#a5b4fc;">Ledger</span></span>
    </div>

    <h1 style="font-size:30px;font-weight:800;color:#fff;line-height:1.25;margin-bottom:14px;">
      Track every rupee.<br>Settle every trip.
    </h1>
    <p style="font-size:14px;color:rgba(255,255,255,.6);line-height:1.7;margin-bottom:36px;">
      The smarter way to manage group travel expenses — split bills, track settlements, and export reports.
    </p>

    <div style="display:flex;flex-direction:column;gap:16px;">
      @foreach([
        ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'text' => 'Smart expense splitting across members'],
        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Auto-calculate who owes whom'],
        ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'text' => 'PDF reports & settlement exports'],
        ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'text' => 'Invite guests via email & OTP'],
      ] as $f)
      <div class="feature-item">
        <div class="feature-dot">
          <svg width="13" height="13" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
          </svg>
        </div>
        <span style="font-size:13px;color:rgba(255,255,255,.75);">{{ $f['text'] }}</span>
      </div>
      @endforeach
    </div>
  </div>

  <p style="font-size:11px;color:rgba(255,255,255,.25);position:relative;z-index:1;">© {{ date('Y') }} TripLedger. All rights reserved.</p>
</div>

{{-- ── Right form panel ─────────────────────────────────── --}}
<div class="auth-right">
  <div class="form-card">

    <h2 style="font-size:24px;font-weight:800;color:#111827;margin-bottom:6px;">Welcome back</h2>
    <p style="font-size:14px;color:#6b7280;margin-bottom:28px;">Sign in to your TripLedger account</p>

    {{-- Status / success message (from OTP flow or password reset) --}}
    @if(session('status'))
    <div style="background:#eef2ff;border:1.5px solid #c7d2fe;border-radius:10px;padding:12px 16px;
                margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
      <svg width="16" height="16" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <p style="font-size:13px;color:#3730a3;line-height:1.5;">{{ session('status') }}</p>
    </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
    <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:20px;">
      @foreach($errors->all() as $error)
      <p style="font-size:13px;color:#991b1b;line-height:1.5;">{{ $error }}</p>
      @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div style="margin-bottom:18px;">
        <label class="form-label" for="email">Email address</label>
        <input class="form-input" id="email" type="email" name="email"
               value="{{ session('login_prefill_email', old('email')) }}"
               required autofocus autocomplete="username" placeholder="you@example.com">
      </div>

      <div style="margin-bottom:10px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
          <label class="form-label" for="password" style="margin-bottom:0;">Password</label>
          @if(Route::has('password.request'))
          <a href="{{ route('password.request') }}"
             style="font-size:12px;color:#6366f1;font-weight:600;text-decoration:none;">
            Forgot password?
          </a>
          @endif
        </div>
        <input class="form-input" id="password" type="password" name="password"
               required autocomplete="current-password" placeholder="••••••••">
      </div>

      <div style="margin-bottom:24px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="remember" id="remember"
                 style="width:15px;height:15px;accent-color:#6366f1;border-radius:4px;">
          <span style="font-size:13px;color:#6b7280;">Remember me for 30 days</span>
        </label>
      </div>

      <button type="submit" class="btn-primary">Sign In</button>
    </form>

    {{-- Divider --}}
    <div style="display:flex;align-items:center;gap:12px;margin:20px 0;">
      <div style="flex:1;height:1px;background:#e5e7eb;"></div>
      <span style="font-size:12px;color:#9ca3af;">OR</span>
      <div style="flex:1;height:1px;background:#e5e7eb;"></div>
    </div>

    {{-- Passwordless / magic link --}}
    <a href="{{ route('password.request') }}"
       style="display:block;text-align:center;padding:12px;border:1.5px solid #e5e7eb;border-radius:11px;
              font-size:13px;font-weight:600;color:#374151;text-decoration:none;background:#fff;transition:all .15s;"
       onmouseover="this.style.borderColor='#6366f1';this.style.color='#4f46e5'"
       onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#374151'">
      Send me a login link (no password needed)
    </a>

    <p style="text-align:center;font-size:13px;color:#9ca3af;margin-top:24px;">
      Don't have an account?
      <a href="{{ route('register') }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">Create one free →</a>
    </p>
  </div>
</div>

</body>
</html>
