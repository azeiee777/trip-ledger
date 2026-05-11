<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Create Account — TripLedger</title>
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
  .form-card { width: 100%; max-width: 400px; }
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
      Join your crew.<br>Track together.
    </h1>
    <p style="font-size:14px;color:rgba(255,255,255,.6);line-height:1.7;margin-bottom:36px;">
      Create a free account and start managing group trip expenses in seconds. No credit card required.
    </p>

    <div style="display:flex;flex-direction:column;gap:16px;">
      @foreach([
        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'text' => 'Invite friends via email or link'],
        ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Split expenses any way you like'],
        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Approve & track group expenses'],
        ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'text' => 'Detailed reports & PDF exports'],
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

    <h2 style="font-size:24px;font-weight:800;color:#111827;margin-bottom:6px;">Create your account</h2>
    <p style="font-size:14px;color:#6b7280;margin-bottom:28px;">Free forever. No credit card needed.</p>

    {{-- OTP verified welcome banner --}}
    @if(session('status'))
    <div style="background:#eef2ff;border:1.5px solid #c7d2fe;border-radius:10px;padding:14px 16px;
                margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
      <svg width="18" height="18" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <p style="font-size:13px;font-weight:600;color:#3730a3;margin-bottom:2px;">OTP Verified!</p>
        <p style="font-size:12px;color:#4338ca;line-height:1.5;">{{ session('status') }}</p>
      </div>
    </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
    <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:20px;">
      @foreach($errors->all() as $error)
      <p style="font-size:13px;color:#991b1b;line-height:1.6;">{{ $error }}</p>
      @endforeach
    </div>
    @endif

    @php $prefilledEmail = session('prefill_email', old('email')); @endphp

    <form method="POST" action="{{ route('register') }}">
      @csrf

      <div style="margin-bottom:16px;">
        <label class="form-label" for="name">Full name</label>
        <input class="form-input" id="name" type="text" name="name"
               value="{{ old('name') }}" required autofocus autocomplete="name"
               placeholder="Your name">
      </div>

      <div style="margin-bottom:16px;">
        <label class="form-label" for="email">Email address</label>
        <input class="form-input" id="email" type="email" name="email"
               value="{{ $prefilledEmail }}"
               required autocomplete="username" placeholder="you@example.com"
               @if($prefilledEmail) style="background:#f0f9ff;border-color:#bfdbfe;" @endif>
        @if($prefilledEmail)
        <p style="font-size:11px;color:#3b82f6;margin-top:5px;display:flex;align-items:center;gap:4px;">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Email verified via OTP
        </p>
        @endif
      </div>

      <div style="margin-bottom:16px;">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" id="password" type="password" name="password"
               required autocomplete="new-password" placeholder="Min 8 characters">
      </div>

      <div style="margin-bottom:24px;">
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input class="form-input" id="password_confirmation" type="password" name="password_confirmation"
               required autocomplete="new-password" placeholder="Repeat your password">
      </div>

      <button type="submit" class="btn-primary">Create Account</button>
    </form>

    <p style="text-align:center;font-size:13px;color:#9ca3af;margin-top:24px;">
      Already have an account?
      <a href="{{ route('login') }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">Sign in →</a>
    </p>
  </div>
</div>

</body>
</html>
