<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>You're invited to {{ $trip->name }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #eef0f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
  a { color: inherit; }
</style>
</head>
<body style="background:#eef0f7;padding:40px 16px;">

  <div style="max-width:560px;margin:0 auto;">

    {{-- ── Brand header ─────────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);
                border-radius:16px 16px 0 0;padding:28px 36px;text-align:center;">
      <div style="display:inline-flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:10px;
                    display:inline-flex;align-items:center;justify-content:center;">
          <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
          </svg>
        </div>
        <span style="font-size:20px;font-weight:800;color:#fff;letter-spacing:-0.3px;">Trip<span style="color:#c4b5fd;">Ledger</span></span>
      </div>
    </div>

    {{-- ── Main card ─────────────────────────────────────────── --}}
    <div style="background:#fff;padding:40px 36px;border:1px solid #e5e7eb;border-top:none;">

      {{-- Greeting --}}
      <p style="font-size:15px;color:#374151;line-height:1.7;margin-bottom:6px;">
        @if($member->guest_name)
          Hi <strong style="color:#111827;">{{ $member->guest_name }}</strong>,
        @else
          Hi there,
        @endif
      </p>
      <p style="font-size:15px;color:#374151;line-height:1.7;margin-bottom:28px;">
        <strong style="color:#111827;">{{ $trip->members()->where('role','admin')->first()?->user?->name ?? 'The trip admin' }}</strong>
        has invited you to join
        <strong style="color:#4f46e5;">{{ $trip->name }}</strong> on TripLedger — a group travel expense tracker.
      </p>

      {{-- OTP box --}}
      <div style="border:2px solid #e0e7ff;border-radius:14px;padding:28px 24px;text-align:center;margin-bottom:28px;background:#fafbff;">
        <p style="font-size:11px;font-weight:700;color:#6366f1;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:14px;">
          Your One-Time Password (OTP)
        </p>
        <div style="background:#4f46e5;border-radius:10px;display:inline-block;padding:14px 32px;margin-bottom:14px;">
          <span style="font-size:38px;font-weight:800;color:#fff;letter-spacing:14px;font-family:'Courier New',Courier,monospace;">{{ $otp }}</span>
        </div>
        <p style="font-size:12px;color:#9ca3af;margin-top:4px;">
          Valid for 7 days &nbsp;·&nbsp; Do not share with anyone else
        </p>
      </div>

      {{-- Trip meta --}}
      <div style="background:#f8f9fc;border:1px solid #e9ebf0;border-radius:12px;padding:18px 20px;margin-bottom:28px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td style="padding:4px 0;">
              <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Trip</span>
            </td>
            <td style="padding:4px 0;text-align:right;">
              <span style="font-size:14px;font-weight:600;color:#111827;">{{ $trip->name }}</span>
            </td>
          </tr>
          @if($trip->destination)
          <tr>
            <td style="padding:4px 0;">
              <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Destination</span>
            </td>
            <td style="padding:4px 0;text-align:right;">
              <span style="font-size:14px;color:#374151;">{{ $trip->destination }}</span>
            </td>
          </tr>
          @endif
          @if($trip->start_date)
          <tr>
            <td style="padding:4px 0;">
              <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Dates</span>
            </td>
            <td style="padding:4px 0;text-align:right;">
              <span style="font-size:14px;color:#374151;">
                {{ $trip->start_date->format('d M Y') }}{{ $trip->end_date ? ' – '.$trip->end_date->format('d M Y') : '' }}
              </span>
            </td>
          </tr>
          @endif
        </table>
      </div>

      {{-- Steps --}}
      <p style="font-size:13px;font-weight:700;color:#111827;margin-bottom:14px;">How to join:</p>
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        @foreach(['Click the button below to open the verification page','Enter your email address and the OTP above',"You'll be able to view trip expenses and settlements"] as $i => $step)
        <tr>
          <td width="32" valign="top" style="padding-bottom:10px;">
            <div style="width:24px;height:24px;background:#4f46e5;border-radius:50%;text-align:center;line-height:24px;
                        font-size:12px;font-weight:700;color:#fff;">{{ $i + 1 }}</div>
          </td>
          <td style="padding-bottom:10px;padding-left:10px;font-size:14px;color:#374151;line-height:1.5;">{{ $step }}</td>
        </tr>
        @endforeach
      </table>

      {{-- CTA button --}}
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:20px;">
        <tr>
          <td align="center">
            <a href="{{ $verifyUrl }}"
               style="display:block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;
                      text-decoration:none;font-weight:700;font-size:15px;padding:16px 36px;
                      border-radius:12px;letter-spacing:0.01em;text-align:center;">
              Verify OTP &amp; Join Trip
            </a>
          </td>
        </tr>
      </table>

      <p style="font-size:12px;color:#9ca3af;text-align:center;line-height:1.6;">
        Already have a TripLedger account?
        <a href="{{ url('/login') }}" style="color:#6366f1;font-weight:600;text-decoration:none;">Log in</a>
        with this email to see the trip automatically.
      </p>
    </div>

    {{-- ── Footer ────────────────────────────────────────────── --}}
    <div style="background:#f8f9fc;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 16px 16px;
                padding:20px 36px;text-align:center;">
      <p style="font-size:12px;color:#9ca3af;line-height:1.6;">
        TripLedger &nbsp;·&nbsp; Group travel expense tracker<br>
        <span style="font-size:11px;">You received this because someone added your email to a trip.</span>
      </p>
    </div>

  </div>
</body>
</html>
