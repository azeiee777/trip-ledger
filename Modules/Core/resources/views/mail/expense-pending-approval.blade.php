<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Expense needs your approval</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #eef0f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
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

      {{-- Status badge --}}
      <div style="display:inline-block;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;
                  padding:5px 12px;font-size:12px;font-weight:700;color:#92400e;margin-bottom:20px;">
        ⏳ &nbsp;Pending Your Approval
      </div>

      <h2 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:8px;">New expense needs your review</h2>
      <p style="font-size:14px;color:#6b7280;line-height:1.7;margin-bottom:28px;">
        A member added an expense to <strong style="color:#111827;">{{ $trip->name }}</strong>
        that requires your approval before it's counted in group calculations.
      </p>

      {{-- Expense card --}}
      <div style="border:1.5px solid #e9ebf0;border-radius:14px;overflow:hidden;margin-bottom:28px;">
        <div style="background:#f8f9fc;padding:16px 20px;border-bottom:1px solid #e9ebf0;">
          <p style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">Expense Details</p>
          <p style="font-size:18px;font-weight:700;color:#111827;">{{ $expense->title }}</p>
        </div>
        <div style="padding:20px;">
          <div style="margin-bottom:16px;">
            <span style="font-size:32px;font-weight:800;color:#4f46e5;">₹{{ number_format($expense->amount, 2) }}</span>
          </div>
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td style="padding:4px 0;font-size:13px;color:#6b7280;width:50%;">Added by</td>
              <td style="padding:4px 0;font-size:13px;font-weight:600;color:#111827;text-align:right;">{{ $expense->paidByMember->display_name }}</td>
            </tr>
            <tr>
              <td style="padding:4px 0;font-size:13px;color:#6b7280;">Date</td>
              <td style="padding:4px 0;font-size:13px;font-weight:600;color:#111827;text-align:right;">{{ $expense->expense_date->format('d M Y') }}</td>
            </tr>
            <tr>
              <td style="padding:4px 0;font-size:13px;color:#6b7280;">Split type</td>
              <td style="padding:4px 0;font-size:13px;font-weight:600;color:#111827;text-align:right;">{{ ucfirst(str_replace('_', ' ', $expense->split_type)) }}</td>
            </tr>
            @if($expense->note)
            <tr>
              <td style="padding:4px 0;font-size:13px;color:#6b7280;">Note</td>
              <td style="padding:4px 0;font-size:13px;color:#374151;text-align:right;">{{ $expense->note }}</td>
            </tr>
            @endif
          </table>
        </div>
      </div>

      {{-- Action buttons --}}
      <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom:20px;">
        <tr>
          <td style="padding-right:8px;">
            <a href="{{ $approveUrl }}"
               style="display:block;background:linear-gradient(135deg,#10b981,#059669);color:#fff;
                      text-decoration:none;font-weight:700;font-size:14px;padding:14px;
                      border-radius:10px;text-align:center;">
              ✓ &nbsp;Approve
            </a>
          </td>
          <td style="padding-left:8px;">
            <a href="{{ $rejectUrl }}"
               style="display:block;background:#fff;color:#374151;border:1.5px solid #e5e7eb;
                      text-decoration:none;font-weight:700;font-size:14px;padding:14px;
                      border-radius:10px;text-align:center;">
              ✗ &nbsp;Reject
            </a>
          </td>
        </tr>
      </table>

      <p style="font-size:12px;color:#9ca3af;text-align:center;line-height:1.6;">
        You can also manage this from the trip's Expenses tab in TripLedger.
      </p>
    </div>

    {{-- ── Footer ────────────────────────────────────────────── --}}
    <div style="background:#f8f9fc;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 16px 16px;
                padding:20px 36px;text-align:center;">
      <p style="font-size:12px;color:#9ca3af;line-height:1.6;">
        TripLedger &nbsp;·&nbsp; Group travel expense tracker<br>
        <span style="font-size:11px;">You received this because you are an admin of {{ $trip->name }}.</span>
      </p>
    </div>

  </div>
</body>
</html>
