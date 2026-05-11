<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your expense has been {{ $decision === 'approved' ? 'approved' : 'rejected' }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #eef0f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
</style>
</head>
<body style="background:#eef0f7;padding:40px 16px;">

  <div style="max-width:560px;margin:0 auto;">

    {{-- ── Brand header (green if approved, red if rejected) ── --}}
    @if($decision === 'approved')
    <div style="background:linear-gradient(135deg,#059669 0%,#10b981 100%);
                border-radius:16px 16px 0 0;padding:28px 36px;text-align:center;">
    @else
    <div style="background:linear-gradient(135deg,#dc2626 0%,#ef4444 100%);
                border-radius:16px 16px 0 0;padding:28px 36px;text-align:center;">
    @endif
      <div style="display:inline-flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:10px;
                    display:inline-flex;align-items:center;justify-content:center;">
          <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
          </svg>
        </div>
        <span style="font-size:20px;font-weight:800;color:#fff;letter-spacing:-0.3px;">Trip<span style="color:rgba(255,255,255,0.7);">Ledger</span></span>
      </div>
    </div>

    {{-- ── Main card ─────────────────────────────────────────── --}}
    <div style="background:#fff;padding:40px 36px;border:1px solid #e5e7eb;border-top:none;">

      @if($decision === 'approved')
        {{-- Approved state --}}
        <div style="display:inline-flex;align-items:center;gap:8px;background:#d1fae5;border:1px solid #a7f3d0;
                    border-radius:8px;padding:6px 14px;margin-bottom:20px;">
          <svg width="15" height="15" fill="none" stroke="#065f46" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span style="font-size:12px;font-weight:700;color:#065f46;">Expense Approved</span>
        </div>
        <h2 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:8px;">Your expense has been approved</h2>
        <p style="font-size:14px;color:#6b7280;line-height:1.7;margin-bottom:28px;">
          Great news! The trip admin has approved your expense. It is now included in the group calculations and settlements.
        </p>

      @else
        {{-- Rejected state --}}
        <div style="display:inline-flex;align-items:center;gap:8px;background:#fee2e2;border:1px solid #fecaca;
                    border-radius:8px;padding:6px 14px;margin-bottom:20px;">
          <svg width="15" height="15" fill="none" stroke="#991b1b" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span style="font-size:12px;font-weight:700;color:#991b1b;">Expense Rejected</span>
        </div>
        <h2 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:8px;">Your expense was not approved</h2>
        <p style="font-size:14px;color:#6b7280;line-height:1.7;margin-bottom:20px;">
          The trip admin reviewed your expense and decided not to include it in the group calculations.
        </p>
        @if($reason)
        <div style="background:#fef2f2;border-left:4px solid #f87171;border-radius:0 10px 10px 0;
                    padding:14px 18px;margin-bottom:28px;">
          <p style="font-size:12px;font-weight:700;color:#991b1b;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.05em;">Reason</p>
          <p style="font-size:14px;color:#7f1d1d;line-height:1.6;">{{ $reason }}</p>
        </div>
        @else
        <div style="margin-bottom:28px;"></div>
        @endif
      @endif

      {{-- Expense details --}}
      <div style="border:1.5px solid #e9ebf0;border-radius:14px;overflow:hidden;margin-bottom:28px;">
        <div style="background:#f8f9fc;padding:14px 20px;border-bottom:1px solid #e9ebf0;">
          <p style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;">Expense Details</p>
        </div>
        <div style="padding:20px;">
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td style="padding:5px 0;font-size:13px;color:#6b7280;width:40%;">Trip</td>
              <td style="padding:5px 0;font-size:13px;font-weight:600;color:#111827;text-align:right;">{{ $trip->name }}</td>
            </tr>
            <tr>
              <td style="padding:5px 0;font-size:13px;color:#6b7280;">Expense</td>
              <td style="padding:5px 0;font-size:13px;font-weight:600;color:#111827;text-align:right;">{{ $expense->title }}</td>
            </tr>
            <tr>
              <td style="padding:5px 0;font-size:13px;color:#6b7280;">Amount</td>
              <td style="padding:5px 0;font-size:15px;font-weight:800;color:#4f46e5;text-align:right;">₹{{ number_format($expense->amount, 2) }}</td>
            </tr>
            <tr>
              <td style="padding:5px 0;font-size:13px;color:#6b7280;">Date</td>
              <td style="padding:5px 0;font-size:13px;font-weight:600;color:#111827;text-align:right;">{{ $expense->expense_date->format('d M Y') }}</td>
            </tr>
          </table>
        </div>
      </div>

      {{-- CTA --}}
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:0;">
        <tr>
          <td align="center">
            <a href="{{ url('/trips/'.$trip->id) }}"
               style="display:block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;
                      text-decoration:none;font-weight:700;font-size:15px;padding:16px 36px;
                      border-radius:12px;text-align:center;">
              View Trip
            </a>
          </td>
        </tr>
      </table>
    </div>

    {{-- ── Footer ────────────────────────────────────────────── --}}
    <div style="background:#f8f9fc;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 16px 16px;
                padding:20px 36px;text-align:center;">
      <p style="font-size:12px;color:#9ca3af;line-height:1.6;">
        TripLedger &nbsp;·&nbsp; Group travel expense tracker<br>
        <span style="font-size:11px;">You received this because you are a member of {{ $trip->name }}.</span>
      </p>
    </div>

  </div>
</body>
</html>
