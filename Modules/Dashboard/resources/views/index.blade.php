<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    @php
        $typeConfig = [
            'road_trip'     => ['emoji'=>'🚗', 'label'=>'Road Trip',     'color'=>'#f97316', 'bg'=>'#fff7ed'],
            'flight'        => ['emoji'=>'✈️', 'label'=>'Flight',        'color'=>'#6366f1', 'bg'=>'#f5f3ff'],
            'local'         => ['emoji'=>'🏙️', 'label'=>'Local',         'color'=>'#10b981', 'bg'=>'#f0fdf4'],
            'international' => ['emoji'=>'🌍', 'label'=>'International', 'color'=>'#3b82f6', 'bg'=>'#eff6ff'],
            'pilgrimage'    => ['emoji'=>'🙏', 'label'=>'Pilgrimage',    'color'=>'#ec4899', 'bg'=>'#fdf2f8'],
            'family'        => ['emoji'=>'👨‍👩‍👧', 'label'=>'Family',    'color'=>'#f59e0b', 'bg'=>'#fffbeb'],
        ];
    @endphp

    <style>
        :root {
            --c-radius: 18px;
            --c-border: #eef0f4;
            --c-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.04);
            --gap: 14px;
        }
        .db-card {
            background: #fff;
            border-radius: var(--c-radius);
            border: 1.5px solid var(--c-border);
            box-shadow: var(--c-shadow);
            overflow: hidden;
        }
        .db-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 15px 18px 13px; border-bottom: 1px solid #f5f7fa;
        }
        .db-label  { font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 3px; }
        .db-title  { font-size: 14px; font-weight: 800; color: #111827; margin: 0; line-height: 1.2; }
        .db-sub    { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .db-link   {
            font-size: 11px; font-weight: 700; color: #6366f1; text-decoration: none;
            background: #f5f3ff; padding: 5px 11px; border-radius: 8px; transition: background .12s;
        }
        .db-link:hover { background: #ede9fe; }
        .db-row {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 18px; border-bottom: 1px solid #f5f7fa;
            transition: background .1s;
        }
        .db-row:last-child { border-bottom: none; }
        .db-row:hover { background: #fafbff; }

        /* filter */
        .fp-btn {
            font-size: 12px; font-weight: 700; padding: 6px 13px;
            border-radius: 9px; border: none; cursor: pointer;
            background: none; color: #6b7280; transition: all .12s;
        }
        .fp-btn:hover { background: #f5f3ff; color: #4f46e5; }
        .fp-btn.on { background: #6366f1; color: #fff; box-shadow: 0 3px 10px rgba(99,102,241,.3); }
        .fp-date {
            font-size: 12px; border: 1.5px solid #e5e7eb; border-radius: 8px;
            padding: 5px 10px; color: #374151; background: #fff;
        }
        .fp-date:focus { outline: none; border-color: #6366f1; }

        @keyframes pulse-dot { 0%,100%{box-shadow:0 0 0 0 rgba(34,197,94,.4)} 50%{box-shadow:0 0 0 5px rgba(34,197,94,0)} }
        .live-dot { animation: pulse-dot 2s infinite; }

        /* ── Responsive grids ── */
        .db-grid-r1 { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:var(--gap); }
        .db-grid-r2 { display:grid; grid-template-columns:1.4fr 1fr; gap:var(--gap); }
        .db-grid-r3 { display:grid; grid-template-columns:1fr 1fr 1.2fr; gap:var(--gap); }
        .db-grid-r4 { display:grid; gap:var(--gap); }

        @media (max-width: 1100px) {
            .db-grid-r1 { grid-template-columns: 1fr 1fr; }
            .db-grid-r2 { grid-template-columns: 1fr; }
            .db-grid-r3 { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 700px) {
            .db-grid-r1 { grid-template-columns: 1fr 1fr; }
            .db-grid-r2, .db-grid-r3, .db-grid-r4 { grid-template-columns: 1fr; }
            .fp-btn { font-size: 11px; padding: 5px 10px; }
            .fp-date { font-size: 11px; padding: 4px 8px; }
            .db-row { padding: 9px 14px; }
        }
        @media (max-width: 480px) {
            .db-grid-r1 { grid-template-columns: 1fr; }
        }
    </style>

    {{-- ═══════════ FILTER BAR ═══════════ --}}
    <form method="GET" action="{{ route('dashboard') }}"
          x-data="{ showCustom: {{ $period === 'custom' ? 'true' : 'false' }} }"
          style="display:flex;flex-wrap:wrap;align-items:center;gap:4px;margin-bottom:18px;padding:7px 10px;background:#fff;border-radius:13px;border:1.5px solid var(--c-border);box-shadow:var(--c-shadow);">

        <svg width="13" height="13" fill="none" stroke="#c4c9d4" stroke-width="2" viewBox="0 0 24 24" style="margin:0 4px;flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>

        @foreach(['all'=>'All Time','month'=>'This Month','quarter'=>'Quarter','year'=>'This Year'] as $val=>$lbl)
        <button type="submit" name="period" value="{{ $val }}"
                class="fp-btn {{ $period === $val ? 'on' : '' }}">{{ $lbl }}</button>
        @endforeach

        <button type="button" @click="showCustom=!showCustom"
                class="fp-btn" :class="showCustom ? 'on' : ''">📅 Custom</button>

        <div x-show="showCustom" x-cloak style="display:flex;align-items:center;gap:6px;margin-left:4px;">
            <input type="date" name="from" value="{{ request('from') }}" class="fp-date">
            <span style="color:#d1d5db;">—</span>
            <input type="date" name="to" value="{{ request('to') }}" class="fp-date">
            <button type="submit" name="period" value="custom" class="fp-btn on">Apply</button>
        </div>

        @if($period === 'custom' && $fromDate)
        <div style="margin-left:auto;font-size:11px;color:#7c3aed;font-weight:600;background:#f5f3ff;padding:4px 11px;border-radius:7px;">
            {{ $fromDate->format('d M Y') }} – {{ $toDate ? $toDate->format('d M Y') : 'Today' }}
        </div>
        @endif
    </form>

    {{-- ═══════════ ROW 1: STAT CARDS ═══════════ --}}
    <div class="db-grid-r1" style="margin-bottom:var(--gap);">

        {{-- MY TRIPS hero --}}
        <div style="background:linear-gradient(140deg,#4f46e5 0%,#7c3aed 55%,#6d28d9 100%);border-radius:var(--c-radius);padding:22px;position:relative;overflow:hidden;box-shadow:0 8px 28px rgba(99,102,241,.3);">
            <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.06);pointer-events:none;"></div>
            <div style="position:absolute;bottom:-50px;left:10px;width:130px;height:130px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none;"></div>

            <div style="display:flex;align-items:center;gap:9px;margin-bottom:12px;position:relative;">
                <div style="width:36px;height:36px;border-radius:11px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;">
                    <svg width="17" height="17" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </div>
                <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,.6);letter-spacing:.07em;text-transform:uppercase;">My Trips</span>
                <a href="{{ route('trips.index') }}" style="margin-left:auto;font-size:11px;color:rgba(255,255,255,.55);text-decoration:none;background:rgba(255,255,255,.12);padding:4px 10px;border-radius:7px;font-weight:600;">View →</a>
            </div>

            <div style="font-size:52px;font-weight:900;color:#fff;line-height:1;margin-bottom:14px;position:relative;letter-spacing:-2px;">{{ $totalTrips }}</div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:5px;position:relative;">
                @foreach([['Created',$createdCount],['Joined',$joinedCount],['Active',$activeCount],['Done',$completedCount]] as [$l,$v])
                <div style="background:rgba(255,255,255,.13);border-radius:10px;padding:8px 4px;text-align:center;border:1px solid rgba(255,255,255,.08);">
                    <div style="font-size:17px;font-weight:900;color:#fff;line-height:1;">{{ $v }}</div>
                    <div style="font-size:9px;color:rgba(255,255,255,.5);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-top:3px;">{{ $l }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- GROUP SPEND --}}
        <div class="db-card" style="padding:20px;">
            <div style="width:38px;height:38px;border-radius:11px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin-bottom:13px;">
                <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div style="font-size:10px;font-weight:700;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;margin-bottom:5px;">Group Spend</div>
            <div style="font-size:24px;font-weight:900;color:#111827;line-height:1.1;letter-spacing:-0.5px;">₹{{ number_format($totalSpent, 0) }}</div>
            <div style="margin-top:10px;padding-top:10px;border-top:1px solid #f5f7fa;">
                <div style="font-size:10px;color:#9ca3af;margin-bottom:2px;">My share</div>
                <div style="font-size:15px;font-weight:800;color:#374151;">₹{{ number_format($mySpend, 0) }}</div>
            </div>
        </div>

        {{-- I OWE --}}
        @php $oweRed = $iOwe > 0; @endphp
        <div class="db-card" style="padding:20px;">
            <div style="width:38px;height:38px;border-radius:11px;background:{{ $oweRed ? '#fef2f2' : '#f0fdf4' }};display:flex;align-items:center;justify-content:center;margin-bottom:13px;">
                <svg width="18" height="18" fill="none" stroke="{{ $oweRed ? '#ef4444' : '#10b981' }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            </div>
            <div style="font-size:10px;font-weight:700;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;margin-bottom:5px;">I Owe</div>
            <div style="font-size:24px;font-weight:900;color:{{ $oweRed ? '#dc2626' : '#111827' }};line-height:1.1;letter-spacing:-0.5px;">₹{{ number_format($iOwe, 0) }}</div>
            <div style="margin-top:10px;font-size:11px;color:{{ $oweRed ? '#ef4444' : '#10b981' }};font-weight:600;">{{ $oweRed ? 'Needs settlement' : "All clear!" }}</div>
        </div>

        {{-- OWED TO ME --}}
        @php $owedBlue = $owedToMe > 0; @endphp
        <div class="db-card" style="padding:20px;">
            <div style="width:38px;height:38px;border-radius:11px;background:{{ $owedBlue ? '#eff6ff' : '#f9fafb' }};display:flex;align-items:center;justify-content:center;margin-bottom:13px;">
                <svg width="18" height="18" fill="none" stroke="{{ $owedBlue ? '#3b82f6' : '#9ca3af' }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div style="font-size:10px;font-weight:700;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;margin-bottom:5px;">Owed to Me</div>
            <div style="font-size:24px;font-weight:900;color:{{ $owedBlue ? '#1d4ed8' : '#111827' }};line-height:1.1;letter-spacing:-0.5px;">₹{{ number_format($owedToMe, 0) }}</div>
            <div style="margin-top:10px;font-size:11px;color:{{ $owedBlue ? '#3b82f6' : '#9ca3af' }};font-weight:600;">{{ $owedBlue ? 'Pending collection' : 'Nothing pending' }}</div>
        </div>
    </div>

    {{-- ═══════════ ROW 2: CHART + TOP TRIPS ═══════════ --}}
    <div class="db-grid-r2" style="margin-bottom:var(--gap);">

        {{-- MONTHLY CHART --}}
        <div style="background:linear-gradient(135deg,#1e1b4b 0%,#2d2a7e 55%,#3730a3 100%);border-radius:var(--c-radius);padding:22px 22px 18px;position:relative;overflow:hidden;box-shadow:0 6px 24px rgba(49,46,129,.28);">
            <div style="position:absolute;top:-50px;right:-50px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none;"></div>
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;">
                <div>
                    <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.35);letter-spacing:.08em;text-transform:uppercase;">Spending Trend</div>
                    <div style="font-size:16px;font-weight:800;color:#fff;margin-top:3px;">Monthly Spend</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3);margin-top:2px;">Last 6 months · group only</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:9px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">avg / month</div>
                    <div style="font-size:20px;font-weight:900;color:#fff;letter-spacing:-0.5px;">₹{{ number_format($monthlySpend->avg('total'), 0) }}</div>
                    @php
                        $spendValues = $monthlySpend->pluck('total');
                        $last = $spendValues->last();
                        $prev = $spendValues->count() > 1 ? $spendValues->slice(-2,1)->first() : 0;
                        $trend = $prev > 0 ? round((($last - $prev) / $prev) * 100, 1) : null;
                    @endphp
                    @if($trend !== null)
                    <div style="margin-top:5px;display:inline-flex;align-items:center;gap:3px;padding:2px 9px;border-radius:99px;background:{{ $trend>=0?'rgba(239,68,68,.2)':'rgba(16,185,129,.2)' }};">
                        <span style="font-size:11px;font-weight:700;color:{{ $trend>=0?'#fca5a5':'#6ee7b7' }};">{{ $trend>=0?'↑':'↓' }} {{ abs($trend) }}%</span>
                        <span style="font-size:9px;color:rgba(255,255,255,.3);">vs last</span>
                    </div>
                    @endif
                </div>
            </div>
            <div style="height:150px;"><canvas id="monthlyChart"></canvas></div>
        </div>

        {{-- TOP TRIPS --}}
        <div class="db-card">
            <div class="db-head">
                <div>
                    <div class="db-label" style="color:#8b5cf6;">Performance</div>
                    <div class="db-title">Top Trips by Spend</div>
                </div>
                <a href="{{ route('trips.index') }}" class="db-link">All →</a>
            </div>
            @if($topTrips->isEmpty())
            <div style="padding:40px 20px;text-align:center;">
                <div style="font-size:30px;margin-bottom:8px;">✈️</div>
                <p style="font-size:12px;color:#9ca3af;font-weight:500;">No trips with expenses yet</p>
            </div>
            @else
            @php $rankColors=['#6366f1','#8b5cf6','#3b82f6','#06b6d4','#10b981']; $medals=['🥇','🥈','🥉','4','5']; @endphp
            @foreach($topTrips as $i => $trip)
            @php $pct = $maxTripSpend > 0 ? round(($trip->period_spend / $maxTripSpend) * 100) : 0; @endphp
            <a href="{{ route('trips.show', $trip) }}" class="db-row" style="text-decoration:none;">
                <div style="width:30px;height:30px;border-radius:9px;background:{{ $rankColors[$i] }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:{{ $i<3?'15':'11' }}px;font-weight:900;color:{{ $rankColors[$i] }};">{{ $medals[$i] }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px;">{{ $trip->name }}</div>
                    <div style="background:#f3f4f6;border-radius:99px;height:4px;overflow:hidden;">
                        <div style="height:4px;border-radius:99px;background:{{ $rankColors[$i] }};width:{{ $pct }}%;transition:width .8s;"></div>
                    </div>
                </div>
                <div style="font-size:12px;font-weight:800;color:{{ $rankColors[$i] }};flex-shrink:0;margin-left:4px;">₹{{ number_format($trip->period_spend, 0) }}</div>
            </a>
            @endforeach
            @endif
        </div>
    </div>

    {{-- ═══════════ ACTIVE TRIPS ═══════════ --}}
    @if($activeTrips->isNotEmpty())
    <div style="margin-bottom:var(--gap);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="display:flex;align-items:center;gap:7px;">
                <div class="live-dot" style="width:8px;height:8px;border-radius:50%;background:#22c55e;flex-shrink:0;"></div>
                <span style="font-size:14px;font-weight:800;color:#111827;">Active Trips</span>
                <span style="font-size:11px;color:#9ca3af;font-weight:500;">&middot; {{ $activeTrips->count() }}</span>
            </div>
            <a href="{{ route('trips.index', ['status'=>'ongoing']) }}" class="db-link" style="margin-left:auto;">View all →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
            @foreach($activeTrips as $trip)
            @php
                $tc = $typeConfig[$trip->trip_type] ?? $typeConfig['road_trip'];
                $isOngoing = $trip->status === 'ongoing';
                $ac = $isOngoing ? '#10b981' : '#3b82f6';
                $ab = $isOngoing ? '#f0fdf4' : '#eff6ff';
                $abr = $isOngoing ? '#bbf7d0' : '#bfdbfe';
                $abar = $isOngoing ? 'linear-gradient(90deg,#22c55e,#10b981)' : 'linear-gradient(90deg,#3b82f6,#6366f1)';
            @endphp
            <a href="{{ route('trips.show', $trip) }}"
               style="display:block;background:#fff;border-radius:16px;border:1.5px solid {{ $abr }};padding:0;text-decoration:none;box-shadow:0 2px 10px rgba(0,0,0,.04);transition:all .18s;overflow:hidden;"
               onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 28px rgba(0,0,0,.1)'"
               onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 10px rgba(0,0,0,.04)'">
                <div style="height:3px;background:{{ $abar }};"></div>
                <div style="padding:15px 16px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:9px;min-width:0;">
                            <div style="width:34px;height:34px;border-radius:10px;background:{{ $tc['bg'] }};display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;border:1px solid {{ $tc['color'] }}22;">{{ $tc['emoji'] }}</div>
                            <div style="min-width:0;">
                                <div style="font-size:12px;font-weight:800;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;">{{ $trip->name }}</div>
                                <div style="font-size:10px;color:#9ca3af;margin-top:1px;">{{ $trip->destination ?? $tc['label'] }}</div>
                            </div>
                        </div>
                        <span style="flex-shrink:0;font-size:10px;font-weight:700;padding:3px 8px;border-radius:99px;background:{{ $ab }};color:{{ $ac }};">{{ $isOngoing ? '● ' : '' }}{{ ucfirst($trip->status) }}</span>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <div>
                            <div style="font-size:9px;color:#9ca3af;font-weight:600;text-transform:uppercase;margin-bottom:1px;">Spent</div>
                            <div style="font-size:17px;font-weight:900;color:{{ $ac }};">₹{{ number_format($trip->total_spend, 0) }}</div>
                        </div>
                        <div style="display:flex;">
                            @foreach($trip->activeMembers->take(4) as $m)
                            <img src="{{ $m->avatar_url }}" title="{{ $m->display_name }}" style="width:24px;height:24px;border-radius:50%;border:2px solid #fff;margin-left:-6px;box-shadow:0 1px 3px rgba(0,0,0,.1);">
                            @endforeach
                            @if($trip->activeMembers->count() > 4)
                            <div style="width:24px;height:24px;border-radius:50%;border:2px solid #fff;margin-left:-6px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:8px;color:#6b7280;font-weight:800;">+{{ $trip->activeMembers->count()-4 }}</div>
                            @endif
                        </div>
                    </div>
                    @if($trip->start_date)
                    <div style="margin-top:10px;padding-top:8px;border-top:1px solid #f5f7fa;font-size:10px;color:#9ca3af;font-weight:500;">
                        📅 {{ $trip->start_date->format('d M') }}{{ $trip->end_date ? ' – '.$trip->end_date->format('d M Y') : '' }}
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════ ROW 3: CATEGORY + TYPES + RECENT ═══════════ --}}
    <div class="db-grid-r3" style="margin-bottom:var(--gap);">

        {{-- CATEGORY BREAKDOWN --}}
        <div class="db-card">
            <div class="db-head">
                <div>
                    <div class="db-label" style="color:#10b981;">Breakdown</div>
                    <div class="db-title">Spending by Category</div>
                </div>
            </div>
            @if($categoryBreakdown->isEmpty())
            <div style="padding:40px 20px;text-align:center;">
                <div style="font-size:28px;margin-bottom:8px;">🗂️</div>
                <p style="font-size:12px;color:#9ca3af;">No expenses yet</p>
            </div>
            @else
            <div style="padding:14px 18px 10px;">
                <div style="height:138px;margin-bottom:12px;"><canvas id="categoryChart"></canvas></div>
                @php $catTotal = $categoryBreakdown->sum('total') ?: 1; @endphp
                <div style="display:flex;flex-direction:column;gap:7px;">
                    @foreach($categoryBreakdown->take(5) as $cat)
                    @php $catPct = round(($cat['total']/$catTotal)*100); @endphp
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:8px;height:8px;border-radius:2px;flex-shrink:0;background:{{ $cat['color'] }};"></div>
                        <span style="font-size:11px;color:#6b7280;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $cat['name'] }}</span>
                        <span style="font-size:10px;color:#c4c9d4;font-weight:600;min-width:28px;text-align:right;">{{ $catPct }}%</span>
                        <span style="font-size:11px;font-weight:700;color:#374151;min-width:48px;text-align:right;">₹{{ number_format($cat['total'], 0) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- TRIP TYPES --}}
        <div class="db-card">
            <div class="db-head">
                <div>
                    <div class="db-label" style="color:#f97316;">Insights</div>
                    <div class="db-title">Trip Types</div>
                </div>
            </div>
            @if($tripTypeBreakdown->isEmpty())
            <div style="padding:40px 20px;text-align:center;">
                <div style="font-size:28px;margin-bottom:8px;">🗺️</div>
                <p style="font-size:12px;color:#9ca3af;">No trips yet</p>
            </div>
            @else
            @php $maxTypeCount = $tripTypeBreakdown->max() ?: 1; @endphp
            <div style="padding:14px 18px;display:flex;flex-direction:column;gap:11px;">
                @foreach($typeConfig as $type => $cfg)
                @if($tripTypeBreakdown->has($type))
                @php $count = $tripTypeBreakdown[$type]; $pct = round(($count/$maxTypeCount)*100); @endphp
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:{{ $cfg['bg'] }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;border:1px solid {{ $cfg['color'] }}22;">{{ $cfg['emoji'] }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                            <span style="font-size:11px;font-weight:600;color:#374151;">{{ $cfg['label'] }}</span>
                            <span style="font-size:11px;font-weight:800;color:{{ $cfg['color'] }};">{{ $count }}</span>
                        </div>
                        <div style="background:#f3f4f6;border-radius:99px;height:5px;overflow:hidden;">
                            <div style="height:5px;border-radius:99px;background:{{ $cfg['color'] }};width:{{ $pct }}%;transition:width .8s;"></div>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>

        {{-- RECENT EXPENSES --}}
        <div class="db-card">
            <div class="db-head">
                <div>
                    <div class="db-label" style="color:#6366f1;">Activity</div>
                    <div class="db-title">Recent Expenses</div>
                </div>
            </div>
            @forelse($recentExpenses as $expense)
            <div class="db-row">
                <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;background:{{ $expense->category?->color ?? '#6366f1' }};">
                    {{ strtoupper(substr($expense->category?->name ?? 'E',0,1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $expense->title }}</div>
                    <div style="font-size:10px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px;">
                        {{ $expense->trip->name }}@if($expense->expense_date) · {{ $expense->expense_date->format('d M') }}@endif
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:13px;font-weight:800;color:#111827;">₹{{ number_format($expense->amount, 0) }}</div>
                    @if($expense->paidByMember)
                    <div style="font-size:9px;color:#9ca3af;margin-top:1px;">{{ $expense->paidByMember->display_name }}</div>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:40px 20px;text-align:center;">
                <div style="font-size:28px;margin-bottom:8px;">🧾</div>
                <p style="font-size:12px;color:#9ca3af;">No expenses yet</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════ ROW 4: TOP PARTNERS + PENDING APPROVALS ═══════════ --}}
    @if($topPartners->isNotEmpty() || $pendingApprovals->isNotEmpty())
    @php
        $bothExist = $topPartners->isNotEmpty() && $pendingApprovals->isNotEmpty();
        $cols = $bothExist ? '1fr 1.5fr' : '1fr';
    @endphp
    <div class="db-grid-r4" style="grid-template-columns:{{ $cols }};margin-bottom:var(--gap);">

        {{-- TOP PARTNERS --}}
        @if($topPartners->isNotEmpty())
        <div class="db-card">
            <div class="db-head">
                <div>
                    <div class="db-label" style="color:#ec4899;">Network</div>
                    <div class="db-title">Travel Partners</div>
                </div>
            </div>
            @php $partnerColors=['#6366f1','#ec4899','#3b82f6','#10b981','#f59e0b']; @endphp
            @foreach($topPartners as $i => $partner)
            @php $maxT = $topPartners->max('trips') ?: 1; $pct = round(($partner['trips']/$maxT)*100); @endphp
            <div class="db-row">
                <img src="{{ $partner['member']->avatar_url }}"
                     style="width:34px;height:34px;border-radius:10px;object-fit:cover;flex-shrink:0;border:2px solid {{ $partnerColors[$i] }}25;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px;">{{ $partner['user']->name ?? $partner['member']->display_name }}</div>
                    <div style="background:#f3f4f6;border-radius:99px;height:4px;overflow:hidden;">
                        <div style="height:4px;border-radius:99px;background:{{ $partnerColors[$i] }};width:{{ $pct }}%;"></div>
                    </div>
                </div>
                <span style="font-size:11px;font-weight:700;color:{{ $partnerColors[$i] }};background:{{ $partnerColors[$i] }}14;padding:3px 9px;border-radius:99px;flex-shrink:0;">{{ $partner['trips'] }} trip{{ $partner['trips']>1?'s':'' }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- PENDING APPROVALS --}}
        @if($pendingApprovals->isNotEmpty())
        <div class="db-card" style="border-color:#fde68a;">
            <div class="db-head" style="background:linear-gradient(135deg,#fffbeb,#fef9e9);">
                <div style="display:flex;align-items:center;gap:10px;flex:1;">
                    <div style="width:32px;height:32px;border-radius:9px;background:#fde68a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="db-title" style="color:#92400e;">Pending Approvals</div>
                        <div class="db-sub">{{ $pendingApprovals->count() }} expense{{ $pendingApprovals->count()>1?'s':'' }} need review</div>
                    </div>
                </div>
            </div>
            @foreach($pendingApprovals as $exp)
            <div class="db-row" style="border-color:#fef3c7;">
                <div style="width:34px;height:34px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:#78350f;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $exp->title }}</div>
                    <div style="font-size:10px;color:#a16207;margin-top:1px;">{{ $exp->trip->name }} · {{ $exp->paidByMember->display_name }}</div>
                </div>
                <div style="font-size:14px;font-weight:800;color:#92400e;flex-shrink:0;margin-right:8px;">₹{{ number_format($exp->amount, 0) }}</div>
                <a href="{{ route('trips.show', ['trip'=>$exp->trip,'tab'=>'expenses']) }}"
                   style="font-size:11px;font-weight:700;color:#fff;background:#d97706;padding:6px 13px;border-radius:8px;text-decoration:none;flex-shrink:0;white-space:nowrap;">Review →</a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- ═══════════ QUICK ACTIONS ═══════════ --}}
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:12px 16px;background:#fff;border-radius:14px;border:1.5px solid var(--c-border);box-shadow:var(--c-shadow);">
        <span style="font-size:10px;font-weight:700;color:#d1d5db;letter-spacing:.07em;text-transform:uppercase;margin-right:6px;flex-shrink:0;">Actions</span>
        <a href="{{ route('trips.create') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#6366f1;color:#fff;font-size:12px;font-weight:700;border-radius:9px;text-decoration:none;box-shadow:0 3px 10px rgba(99,102,241,.28);">
            <svg width="11" height="11" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Trip
        </a>
        <a href="{{ route('trips.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f5f3ff;color:#6366f1;font-size:12px;font-weight:700;border-radius:9px;text-decoration:none;">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            All Trips
        </a>
        <a href="{{ route('trips.index', ['status'=>'ongoing']) }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f0fdf4;color:#059669;font-size:12px;font-weight:700;border-radius:9px;text-decoration:none;">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            Active Trips
        </a>
    </div>

    {{-- ═══════════ CHARTS ═══════════ --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthly = @json($monthlySpend->values());
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: monthly.map(m => m.label),
                datasets: [{
                    data: monthly.map(m => m.total),
                    backgroundColor: monthly.map((m, i) =>
                        i === monthly.length - 1 ? 'rgba(255,255,255,.92)' : 'rgba(255,255,255,.1)'
                    ),
                    hoverBackgroundColor: monthly.map((m, i) =>
                        i === monthly.length - 1 ? '#fff' : 'rgba(255,255,255,.22)'
                    ),
                    borderRadius: { topLeft: 7, topRight: 7 },
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(10,8,30,.92)',
                        titleColor: 'rgba(255,255,255,.5)',
                        bodyColor: '#fff', padding: 11, cornerRadius: 10,
                        callbacks: { label: ctx => ' ₹' + ctx.parsed.y.toLocaleString('en-IN') }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: 'rgba(255,255,255,.35)', font: { size: 11, weight: '600' } } },
                    y: {
                        grid: { color: 'rgba(255,255,255,.05)' }, border: { display: false },
                        ticks: {
                            color: 'rgba(255,255,255,.3)', font: { size: 10 }, maxTicksLimit: 4,
                            callback: v => '₹' + (v >= 1000 ? Math.round(v/1000) + 'k' : v)
                        }
                    }
                }
            }
        });

        @if($categoryBreakdown->isNotEmpty())
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: @json($categoryBreakdown->pluck('name')),
                datasets: [{
                    data: @json($categoryBreakdown->pluck('total')),
                    backgroundColor: @json($categoryBreakdown->pluck('color')),
                    borderWidth: 3, borderColor: '#fff', hoverOffset: 5,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827', padding: 10, cornerRadius: 10,
                        callbacks: { label: ctx => ' ' + ctx.label + ': ₹' + ctx.parsed.toLocaleString('en-IN') }
                    }
                }
            }
        });
        @endif
    });
    </script>
</x-app-layout>
