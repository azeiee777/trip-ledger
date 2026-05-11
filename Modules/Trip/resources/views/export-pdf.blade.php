<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $trip->name }} — Expense Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1f2937;
            background: #fff;
        }

        /* ── Page header ── */
        .page-header {
            text-align: center;
            padding: 18px 0 10px;
            border-bottom: 3px solid #6366f1;
            margin-bottom: 18px;
        }
        .page-title {
            font-size: 20pt;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: -0.3pt;
        }
        .page-subtitle {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 4px;
        }

        /* ── Section header ── */
        .section-header {
            background: #4f46e5;
            color: #fff;
            padding: 7px 12px;
            font-size: 10.5pt;
            font-weight: bold;
            text-align: center;
            border-radius: 4px 4px 0 0;
        }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }

        thead th {
            background: #6366f1;
            color: #fff;
            padding: 6px 8px;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: left;
            border: 1px solid #4f46e5;
        }
        thead th.r { text-align: right; }
        thead th.c { text-align: center; }

        tbody td {
            padding: 5px 8px;
            font-size: 8pt;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        tbody td.r { text-align: right; }
        tbody td.c { text-align: center; }

        tbody tr:nth-child(even) td { background: #f5f3ff; }
        tbody tr:nth-child(odd)  td { background: #ffffff; }

        .row-total td {
            background: #eef2ff !important;
            font-weight: bold;
            border-top: 2px solid #6366f1;
            font-size: 8.5pt;
        }

        /* ── Status badges ── */
        .paid    { color: #16a34a; font-weight: bold; }
        .pending { color: #dc2626; font-weight: bold; }
        .partial { color: #d97706; font-weight: bold; }
        .creditor{ color: #2563eb; font-weight: bold; }

        /* ── Summary boxes (settlement totals) ── */
        .summary-wrap { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 18px; }
        .box-settled { background: #f0fdf4; border: 2px solid #86efac; padding: 10px; text-align: center; border-radius: 4px; }
        .box-pending { background: #fef2f2; border: 2px solid #fca5a5; padding: 10px; text-align: center; border-radius: 4px; }
        .box-total   { background: #eef2ff; border: 2px solid #a5b4fc; padding: 10px; text-align: center; border-radius: 4px; }
        .box-label   { font-size: 7.5pt; color: #6b7280; margin-bottom: 4px; }
        .box-value   { font-size: 13pt; font-weight: bold; }
        .box-settled .box-value { color: #16a34a; }
        .box-pending .box-value { color: #dc2626; }
        .box-total   .box-value { color: #4f46e5; }

        /* ── Footer ── */
        .footer {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 7px;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
        }

        /* ── Page break ── */
        .page-break { page-break-after: always; }

        /* ── Meta chips ── */
        .meta-row { text-align: center; margin-bottom: 14px; font-size: 7.5pt; color: #6b7280; }
        .meta-chip {
            display: inline;
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
            padding: 2px 8px;
            border-radius: 10px;
            margin: 0 3px;
            font-size: 7.5pt;
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════
     PAGE 1
     ═══════════════════════════════════ --}}

<div class="page-header">
    <div class="page-title">{{ $trip->name }} — Expense Report</div>
    <div class="page-subtitle">Complete breakdown of all expenses, payments &amp; settlements</div>
</div>

<div class="meta-row">
    @if($trip->destination)
    <span class="meta-chip">📍 {{ $trip->destination }}</span>
    @endif
    @if($trip->start_date)
    <span class="meta-chip">📅 {{ $trip->start_date->format('d M Y') }}{{ $trip->end_date ? ' – '.$trip->end_date->format('d M Y') : '' }}</span>
    @endif
    <span class="meta-chip">👥 {{ $trip->activeMembers->count() }} members</span>
    <span class="meta-chip">🧾 {{ $expenses->count() }} expenses</span>
    <span class="meta-chip">Generated {{ now()->format('d M Y') }}</span>
</div>

{{-- ── SECTION 1: EXPENSE DETAILS ── --}}
<div class="section-header">1. Expense Details</div>
<table>
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th class="r" style="width:10%">Amount (₹)</th>
            <th style="width:26%">Description</th>
            <th style="width:12%">Category</th>
            <th style="width:13%">Paid By</th>
            <th style="width:24%">Divided Among</th>
            <th class="r" style="width:11%">Per Head (₹)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $i => $expense)
        @php
            $nonExcluded = $expense->splits->filter(fn($s) => !$s->is_excluded);
            $splitCount  = $nonExcluded->count();
            $activeCount = $trip->activeMembers->count();

            if ($expense->split_type === 'per_car') {
                $dividedAmong = 'Car group: ' . ($expense->carGroup->name ?? 'N/A');
            } elseif ($splitCount === 0) {
                $dividedAmong = '—';
            } elseif ($splitCount === $activeCount) {
                $dividedAmong = 'All ' . $activeCount . ' members';
            } else {
                $names = $nonExcluded->map(fn($s) => $s->member?->display_name ?? '?');
                $dividedAmong = $names->count() <= 4
                    ? $names->implode(', ')
                    : $names->take(3)->implode(', ') . ' +' . ($names->count() - 3) . ' more';
            }

            $perHead = $splitCount > 0 ? round((float)$expense->amount / $splitCount, 2) : 0;
        @endphp
        <tr>
            <td class="c">{{ $i + 1 }}</td>
            <td class="r">{{ number_format((float)$expense->amount, 2) }}</td>
            <td>{{ $expense->title }}</td>
            <td>{{ $expense->category?->name ?? '—' }}</td>
            <td>{{ $expense->paidByMember?->display_name ?? '—' }}</td>
            <td style="font-size:7.5pt;">{{ $dividedAmong }}</td>
            <td class="r">{{ $splitCount > 0 ? number_format($perHead, 2) : '—' }}</td>
        </tr>
        @endforeach
        <tr class="row-total">
            <td colspan="1"></td>
            <td class="r">{{ number_format($expenses->sum(fn($e) => (float)$e->amount), 2) }}</td>
            <td colspan="5">TOTAL</td>
        </tr>
    </tbody>
</table>

{{-- Category legend if there are splits with different groups --}}
@php
    $categorySpend = $expenses->groupBy(fn($e) => $e->category?->name ?? 'Uncategorised')
        ->map(fn($g) => $g->sum(fn($e) => (float)$e->amount))
        ->sortByDesc(fn($v) => $v);
@endphp
@if($categorySpend->count() > 1)
<table style="margin-bottom:18px;">
    <thead>
        <tr>
            <th style="background:#374151;">Category</th>
            <th class="r" style="background:#374151;">Total (₹)</th>
            <th class="r" style="background:#374151;">% of Spend</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = $categorySpend->sum(); @endphp
        @foreach($categorySpend as $catName => $catAmount)
        <tr>
            <td>{{ $catName }}</td>
            <td class="r">{{ number_format($catAmount, 2) }}</td>
            <td class="r">{{ $grandTotal > 0 ? number_format(($catAmount / $grandTotal) * 100, 1) : 0 }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">TripLedger · {{ $trip->name }} · Page 1 of 3</div>
<div class="page-break"></div>

{{-- ═══════════════════════════════════
     PAGE 2
     ═══════════════════════════════════ --}}

<div class="page-header">
    <div class="page-title">{{ $trip->name }} — Expense Report</div>
    <div class="page-subtitle">Payments &amp; Per-Person Breakdown</div>
</div>

{{-- ── SECTION 2: WHO PAID ── --}}
<div class="section-header">2. Who Paid</div>
<table>
    <thead>
        <tr>
            <th style="width:22%">Member</th>
            <th class="r" style="width:20%">Amount Paid (₹)</th>
            <th style="width:58%">Expenses Paid For</th>
        </tr>
    </thead>
    <tbody>
        @php $totalPaidAll = 0; @endphp
        @foreach($trip->activeMembers as $i => $member)
        @php
            $paid = $memberPaid[$member->id] ?? 0;
            $totalPaidAll += $paid;
            $paidExpenses  = $expenses->where('paid_by_member_id', $member->id);
            $details = $paidExpenses->pluck('title')->take(6)->implode(', ');
            if ($paidExpenses->count() > 6) $details .= ' + ' . ($paidExpenses->count() - 6) . ' more';
            if (!$details) $details = 'Nothing paid';
        @endphp
        <tr>
            <td><strong>{{ $member->display_name }}</strong>@if($member->role === 'admin') <span style="font-size:6.5pt;color:#6366f1;">(admin)</span>@endif</td>
            <td class="r">{{ number_format($paid, 2) }}</td>
            <td style="font-size:7pt;color:#4b5563;">{{ $details }}</td>
        </tr>
        @endforeach
        <tr class="row-total">
            <td>TOTAL</td>
            <td class="r">{{ number_format($totalPaidAll, 2) }}</td>
            <td></td>
        </tr>
    </tbody>
</table>

{{-- ── SECTION 3: PER PERSON BREAKDOWN ── --}}
<div class="section-header">3. Per Person Breakdown</div>
<table>
    <thead>
        <tr>
            <th style="width:22%">Member</th>
            <th class="r" style="width:18%">Trip Share (₹)</th>
            <th class="r" style="width:18%">Amount Paid (₹)</th>
            <th class="r" style="width:18%">Balance (₹)</th>
            <th class="c" style="width:12%">Status</th>
            <th class="r" style="width:12%">Settled (₹)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trip->activeMembers as $i => $member)
        @php
            $paid    = $memberPaid[$member->id]   ?? 0;
            $share   = $memberShares[$member->id] ?? 0;
            $balance = $paid - $share;

            // Settlements this member has made
            $settledByMember = $trip->settlements
                ->where('payer_member_id', $member->id)
                ->where('status', 'paid')
                ->sum(fn($s) => (float)$s->amount);

            if ($balance >  0.005) { $status = 'CREDITOR'; $sc = 'creditor'; }
            elseif ($balance >= -0.005) { $status = 'SETTLED';   $sc = 'paid'; }
            else                   { $status = 'PENDING';   $sc = 'pending'; }
        @endphp
        <tr>
            <td><strong>{{ $member->display_name }}</strong></td>
            <td class="r">{{ number_format($share, 2) }}</td>
            <td class="r">{{ number_format($paid,  2) }}</td>
            <td class="r {{ $balance >= 0 ? 'creditor' : 'pending' }}">
                {{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 2) }}
            </td>
            <td class="c {{ $sc }}">{{ $status }}</td>
            <td class="r paid">{{ $settledByMember > 0 ? number_format($settledByMember, 2) : '—' }}</td>
        </tr>
        @endforeach
        <tr class="row-total">
            <td>TOTAL</td>
            <td class="r">{{ number_format(array_sum($memberShares), 2) }}</td>
            <td class="r">{{ number_format(array_sum($memberPaid),   2) }}</td>
            <td></td><td></td><td></td>
        </tr>
    </tbody>
</table>

<div class="footer">TripLedger · {{ $trip->name }} · Page 2 of 3</div>
<div class="page-break"></div>

{{-- ═══════════════════════════════════
     PAGE 3
     ═══════════════════════════════════ --}}

<div class="page-header">
    <div class="page-title">{{ $trip->name }} — Expense Report</div>
    <div class="page-subtitle">Settlement Summary</div>
</div>

{{-- ── SECTION 4: SETTLEMENT SUMMARY ── --}}
<div class="section-header">4. Settlement Summary</div>

@if($trip->settlements->isEmpty())
<table><tbody><tr><td style="text-align:center;padding:24px;color:#9ca3af;">No settlements recorded yet.</td></tr></tbody></table>
@else
<table>
    <thead>
        <tr>
            <th style="width:20%">Who Pays</th>
            <th style="width:20%">Pays To</th>
            <th class="r" style="width:16%">Amount (₹)</th>
            <th class="r" style="width:16%">Paid (₹)</th>
            <th class="r" style="width:16%">Remaining (₹)</th>
            <th class="c" style="width:12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @php
            $settledTotal  = 0;
            $pendingTotal  = 0;
        @endphp
        @foreach($trip->settlements->sortBy('status') as $i => $s)
        @php
            $isPaid    = $s->status === 'paid';
            $isPartial = $s->status === 'partial';
            if ($isPaid)        { $settledTotal += (float)$s->amount; $sc = 'paid'; }
            elseif ($isPartial) { $pendingTotal += $s->remaining_amount; $sc = 'partial'; }
            else                { $pendingTotal += $s->remaining_amount; $sc = 'pending'; }
        @endphp
        <tr>
            <td><strong>{{ $s->payer?->display_name ?? '—' }}</strong></td>
            <td>{{ $s->receiver?->display_name ?? '—' }}</td>
            <td class="r">{{ number_format((float)$s->amount, 2) }}</td>
            <td class="r paid">{{ number_format((float)$s->paid_amount, 2) }}</td>
            <td class="r {{ $isPaid ? 'paid' : 'pending' }}">
                {{ $isPaid ? '—' : number_format($s->remaining_amount, 2) }}
            </td>
            <td class="c {{ $sc }}">{{ strtoupper($s->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals row --}}
<table class="summary-wrap">
    <tr>
        <td style="width:32%; padding:0 6px 0 0;">
            <div class="box-settled">
                <div class="box-label">Total Settled (₹)</div>
                <div class="box-value">{{ number_format($settledTotal, 2) }}</div>
            </div>
        </td>
        <td style="width:32%; padding:0 3px;">
            <div class="box-pending">
                <div class="box-label">Total Pending (₹)</div>
                <div class="box-value">{{ number_format($pendingTotal, 2) }}</div>
            </div>
        </td>
        <td style="width:32%; padding:0 0 0 6px;">
            <div class="box-total">
                <div class="box-label">Grand Total (₹)</div>
                <div class="box-value">{{ number_format($settledTotal + $pendingTotal, 2) }}</div>
            </div>
        </td>
    </tr>
</table>
@endif

{{-- ── SECTION 5: MEMBER DIRECTORY ── --}}
<div class="section-header" style="margin-top:10px;">5. Member Directory</div>
<table>
    <thead>
        <tr>
            <th style="width:30%">Name</th>
            <th style="width:20%">Role</th>
            <th style="width:25%">Contact / Email</th>
            <th style="width:25%">Joined</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trip->activeMembers as $i => $member)
        <tr>
            <td><strong>{{ $member->display_name }}</strong></td>
            <td class="c" style="{{ $member->role === 'admin' ? 'color:#4f46e5;font-weight:bold;' : '' }}">
                {{ ucfirst($member->role) }}
            </td>
            <td style="font-size:7pt;">{{ $member->user?->email ?? $member->invite_email ?? $member->guest_phone ?? '—' }}</td>
            <td style="font-size:7.5pt;">{{ $member->joined_at?->format('d M Y') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Trip summary box --}}
<table style="margin-top:8px;margin-bottom:0;">
    <tbody>
        <tr>
            <td style="background:#f8faff;border:1.5px solid #c7d2fe;padding:12px 16px;border-radius:6px;">
                <table style="margin:0;">
                    <tr>
                        <td style="width:50%;font-size:8pt;color:#6b7280;padding:3px 8px 3px 0;">Trip Name</td>
                        <td style="font-size:8.5pt;font-weight:bold;color:#111827;padding:3px 0;">{{ $trip->name }}</td>
                    </tr>
                    @if($trip->destination)
                    <tr>
                        <td style="font-size:8pt;color:#6b7280;padding:3px 8px 3px 0;">Destination</td>
                        <td style="font-size:8.5pt;color:#111827;padding:3px 0;">{{ $trip->destination }}</td>
                    </tr>
                    @endif
                    @if($trip->start_date)
                    <tr>
                        <td style="font-size:8pt;color:#6b7280;padding:3px 8px 3px 0;">Dates</td>
                        <td style="font-size:8.5pt;color:#111827;padding:3px 0;">
                            {{ $trip->start_date->format('d M Y') }}{{ $trip->end_date ? ' – '.$trip->end_date->format('d M Y') : '' }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="font-size:8pt;color:#6b7280;padding:3px 8px 3px 0;">Total Group Spend</td>
                        <td style="font-size:9pt;font-weight:bold;color:#4f46e5;padding:3px 0;">₹{{ number_format($expenses->sum(fn($e) => (float)$e->amount), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:8pt;color:#6b7280;padding:3px 8px 3px 0;">Members</td>
                        <td style="font-size:8.5pt;color:#111827;padding:3px 0;">{{ $trip->activeMembers->count() }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>

<div class="footer" style="margin-top:18px;">
    TripLedger · Generated on {{ now()->format('d M Y, h:i A') }} · {{ $trip->name }} · Page 3 of 3
</div>

</body>
</html>
