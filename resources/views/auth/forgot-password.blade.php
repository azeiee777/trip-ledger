<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Get Login Link — TripLedger</title>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Inter, sans-serif; min-height: 100vh; display: flex; align-items: center;
         justify-content: center; background: #f6f7fb; padding: 24px; }
  .form-input {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px;
    padding: 12px 14px; font-size: 14px; color: #111827;
    font-family: Inter, sans-serif; outline: none; transition: border-color .15s; background: #fff;
  }
  .form-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
  .btn-primary {
    width: 100%; padding: 13px; border: none; border-radius: 11px; cursor: pointer;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff; font-size: 15px; font-weight: 700; font-family: Inter, sans-serif;
    box-shadow: 0 4px 14px rgba(99,102,241,.35); transition: all .2s;
  }
  .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.45); }
</style>
</head>
<body>

  <div style="width:100%;max-width:420px;">

    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:32px;">
      <a href="{{ route('login') }}"
         style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;">
        <div style="width:40px;height:40px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:12px;
                    display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(99,102,241,.4);">
          <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
          </svg>
        </div>
        <span style="font-size:20px;font-weight:800;color:#111827;">Trip<span style="color:#6366f1;">Ledger</span></span>
      </a>
    </div>

    {{-- Card --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:36px;
                box-shadow:0 4px 24px rgba(0,0,0,.05);">

      {{-- Icon --}}
      <div style="width:52px;height:52px;background:#eef2ff;border:1.5px solid #c7d2fe;border-radius:14px;
                  display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
        <svg width="24" height="24" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
      </div>

      <h2 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:8px;">Get a login link</h2>
      <p style="font-size:14px;color:#6b7280;line-height:1.65;margin-bottom:24px;">
        No password? No problem. Enter your email and we'll send you a secure link to sign in and set your password.
      </p>

      {{-- Success status --}}
      @if(session('status'))
      <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:14px 16px;
                  margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
        <svg width="17" height="17" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p style="font-size:13px;color:#166534;font-weight:500;line-height:1.5;">{{ session('status') }}</p>
      </div>
      @endif

      {{-- Errors --}}
      @if($errors->any())
      <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:20px;">
        @foreach($errors->all() as $error)
        <p style="font-size:13px;color:#991b1b;line-height:1.6;">{{ $error }}</p>
        @endforeach
      </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;" for="email">
            Email address
          </label>
          <input class="form-input" id="email" type="email" name="email"
                 value="{{ old('email') }}" required autofocus placeholder="you@example.com">
        </div>
        <button type="submit" class="btn-primary">Send Login Link</button>
      </form>
    </div>

    <p style="text-align:center;font-size:13px;color:#9ca3af;margin-top:20px;">
      Remember your password?
      <a href="{{ route('login') }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">Back to sign in →</a>
    </p>
  </div>

</body>
</html>
