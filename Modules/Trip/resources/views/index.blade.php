<x-app-layout>
    <x-slot name="header">My Trips</x-slot>

    @php
        $typeConfig = [
            'road_trip'     => ['emoji' => '🚗', 'label' => 'Road Trip',      'grad' => 'linear-gradient(135deg,#f97316,#fb923c)', 'light' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#9a3412'],
            'flight'        => ['emoji' => '✈️', 'label' => 'Flight',         'grad' => 'linear-gradient(135deg,#6366f1,#8b5cf6)', 'light' => '#f5f3ff', 'border' => '#ddd6fe', 'text' => '#4c1d95'],
            'local'         => ['emoji' => '🏙️', 'label' => 'Local',          'grad' => 'linear-gradient(135deg,#10b981,#34d399)', 'light' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#064e3b'],
            'international' => ['emoji' => '🌍', 'label' => 'International',  'grad' => 'linear-gradient(135deg,#3b82f6,#06b6d4)', 'light' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1e3a8a'],
            'pilgrimage'    => ['emoji' => '🙏', 'label' => 'Pilgrimage',     'grad' => 'linear-gradient(135deg,#ec4899,#f472b6)', 'light' => '#fdf2f8', 'border' => '#fbcfe8', 'text' => '#831843'],
            'family'        => ['emoji' => '👨‍👩‍👧', 'label' => 'Family',     'grad' => 'linear-gradient(135deg,#f59e0b,#fbbf24)', 'light' => '#fffbeb', 'border' => '#fde68a', 'text' => '#78350f'],
        ];
        $statusConfig = [
            'ongoing'   => ['bg' => '#dcfce7', 'fg' => '#16a34a', 'dot' => '#22c55e', 'label' => 'Ongoing'],
            'upcoming'  => ['bg' => '#dbeafe', 'fg' => '#2563eb', 'dot' => '#3b82f6', 'label' => 'Upcoming'],
            'completed' => ['bg' => '#f3f4f6', 'fg' => '#6b7280', 'dot' => '#9ca3af', 'label' => 'Completed'],
            'archived'  => ['bg' => '#fef3c7', 'fg' => '#d97706', 'dot' => '#f59e0b', 'label' => 'Archived'],
        ];
        $activeStatus = request('status', '');
        $activeType   = request('type', '');
    @endphp

    <style>
        .trips-filter-btn {
            font-size: 12px; font-weight: 700; padding: 7px 16px; border-radius: 10px;
            border: 1.5px solid transparent; cursor: pointer; background: none;
            transition: all .15s; color: #6b7280; background: rgba(99,102,241,.05);
        }
        .trips-filter-btn:hover { background: rgba(99,102,241,.1); color: #4f46e5; border-color: rgba(99,102,241,.2); }
        .trips-filter-btn.active { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; box-shadow: 0 3px 10px rgba(99,102,241,.35); }
        .trip-card {
            display: block; border-radius: 20px; text-decoration: none; overflow: hidden;
            border: 1.5px solid #f1f5f9;
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
            background: #fff;
            transition: transform .2s cubic-bezier(.4,0,.2,1), box-shadow .2s;
            position: relative;
        }
        .trip-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.1); }
        .type-select {
            font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 10px;
            border: 1.5px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer;
            appearance: none; padding-right: 32px; transition: border-color .15s;
        }
        .type-select:focus { outline: none; border-color: #6366f1; }
        .select-wrap { position: relative; display: inline-block; }
        .select-wrap::after { content: '▾'; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #9ca3af; pointer-events: none; }
    </style>

    {{-- ===== SUMMARY ROW ===== --}}
    <div style="display:grid;grid-template-columns:1fr auto;align-items:center;gap:16px;margin-bottom:24px;">
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div style="background:#fff;border:1px solid #e8e6ff;border-radius:14px;padding:14px 20px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(99,102,241,.06);">
                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:900;color:#1e1b4b;line-height:1;">{{ $tripCounts->total }}</div>
                    <div style="font-size:10px;color:#a78bfa;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Total Trips</div>
                </div>
            </div>
            @if($tripCounts->ongoing > 0)
            <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #a7f3d0;border-radius:14px;padding:14px 20px;display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#10b981,#34d399);display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M12 8v4l2 2"/></svg>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:900;color:#065f46;line-height:1;">{{ $tripCounts->ongoing }}</div>
                    <div style="font-size:10px;color:#10b981;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">● Live Now</div>
                </div>
            </div>
            @endif
            @if($tripCounts->upcoming > 0)
            <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;border-radius:14px;padding:14px 20px;display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:900;color:#1e3a8a;line-height:1;">{{ $tripCounts->upcoming }}</div>
                    <div style="font-size:10px;color:#3b82f6;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Upcoming</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ===== FILTER BAR ===== --}}
    <form method="GET" id="filterForm"
          style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:24px;background:#fff;border:1px solid #e8e6ff;border-radius:16px;padding:12px 18px;box-shadow:0 2px 12px rgba(99,102,241,.05);">

        <div style="display:flex;align-items:center;gap:6px;margin-right:6px;">
            <div style="width:20px;height:20px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:6px;display:flex;align-items:center;justify-content:center;">
                <svg width="10" height="10" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            </div>
            <span style="font-size:11px;font-weight:700;color:#6366f1;letter-spacing:.06em;text-transform:uppercase;">Filter</span>
        </div>

        <div style="width:1px;height:20px;background:#e5e7eb;margin-right:4px;"></div>

        {{-- Status pills — each carries current type so it's preserved --}}
        @foreach(['' => 'All', 'ongoing' => '● Ongoing', 'upcoming' => 'Upcoming', 'completed' => 'Completed', 'archived' => 'Archived'] as $val => $lbl)
        <a href="{{ route('trips.index', array_filter(['status' => $val, 'type' => $activeType])) }}"
           class="trips-filter-btn {{ $activeStatus === $val ? 'active' : '' }}"
           style="text-decoration:none;">
            {{ $lbl }}
        </a>
        @endforeach

        <div style="width:1px;height:20px;background:#e5e7eb;margin:0 4px;"></div>

        {{-- Type dropdown --}}
        <div class="select-wrap">
            <select name="type" class="type-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach(['road_trip'=>'🚗 Road Trip','flight'=>'✈️ Flight','local'=>'🏙️ Local','international'=>'🌍 International','pilgrimage'=>'🙏 Pilgrimage','family'=>'👨‍👩‍👧 Family'] as $k => $v)
                <option value="{{ $k }}" {{ $activeType === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        {{-- Hidden status carries through type-dropdown submit --}}
        @if($activeStatus)
        <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif

        @if($activeStatus || $activeType)
        <a href="{{ route('trips.index') }}"
           style="margin-left:auto;font-size:12px;font-weight:600;color:#9ca3af;text-decoration:none;display:flex;align-items:center;gap:4px;padding:6px 12px;border-radius:8px;border:1px solid #e5e7eb;transition:all .15s;"
           onmouseover="this.style.color='#ef4444';this.style.borderColor='#fca5a5'" onmouseout="this.style.color='#9ca3af';this.style.borderColor='#e5e7eb'">
            ✕ Clear
        </a>
        @endif
    </form>

    {{-- ===== EMPTY STATE ===== --}}
    @if($trips->isEmpty())
    <div style="background:#fff;border-radius:24px;border:1px solid #f1f5f9;box-shadow:0 4px 24px rgba(0,0,0,.05);padding:64px 40px;text-align:center;">
        <div style="width:80px;height:80px;border-radius:24px;background:linear-gradient(135deg,#f5f3ff,#ede9fe);display:flex;align-items:center;justify-content:center;font-size:36px;margin:0 auto 20px;">
            @if($activeStatus || $activeType) 🔍 @else ✈️ @endif
        </div>
        <h3 style="font-size:18px;font-weight:800;color:#1e1b4b;margin-bottom:8px;">
            @if($activeStatus || $activeType) No trips match your filters @else No trips yet @endif
        </h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:24px;">
            @if($activeStatus || $activeType)
                Try a different filter or clear to see all trips.
            @else
                Create your first trip and invite your group to get started.
            @endif
        </p>
        @if($activeStatus || $activeType)
        <a href="{{ route('trips.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#f5f3ff;color:#6366f1;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid #ddd6fe;">
            Clear Filters
        </a>
        @else
        <a href="{{ route('trips.create') }}"
           style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(99,102,241,.35);">
            <svg width="14" height="14" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Plan Your First Trip
        </a>
        @endif
    </div>

    {{-- ===== TRIP CARDS GRID ===== --}}
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;">
        @foreach($trips as $trip)
        @php
            $tc = $typeConfig[$trip->trip_type] ?? $typeConfig['road_trip'];
            $sc = $statusConfig[$trip->status]  ?? $statusConfig['archived'];
        @endphp
        <a href="{{ route('trips.show', $trip) }}" class="trip-card">

            {{-- Colored header strip --}}
            <div style="height:6px;background:{{ $sc['dot'] === '#22c55e' ? 'linear-gradient(90deg,#22c55e,#34d399)' : ($sc['dot'] === '#3b82f6' ? 'linear-gradient(90deg,#3b82f6,#6366f1)' : ($sc['dot'] === '#9ca3af' ? 'linear-gradient(90deg,#d1d5db,#e5e7eb)' : 'linear-gradient(90deg,#f59e0b,#fbbf24)')) }};"></div>

            <div style="padding:20px;">

                {{-- Top row: type icon + status badge --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                        <div style="width:44px;height:44px;border-radius:14px;background:{{ $tc['light'] }};border:1.5px solid {{ $tc['border'] }};display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                            {{ $tc['emoji'] }}
                        </div>
                        <div style="min-width:0;">
                            <div style="font-size:14px;font-weight:800;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px;">{{ $trip->name }}</div>
                            @if($trip->destination)
                            <div style="font-size:11px;color:#9ca3af;margin-top:2px;display:flex;align-items:center;gap:3px;">
                                <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $trip->destination }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <span style="flex-shrink:0;font-size:10px;font-weight:800;padding:4px 10px;border-radius:99px;background:{{ $sc['bg'] }};color:{{ $sc['fg'] }};white-space:nowrap;">
                        @if($trip->status === 'ongoing') ● @endif{{ $sc['label'] }}
                    </span>
                </div>

                {{-- Trip type pill --}}
                <div style="display:inline-flex;align-items:center;gap:4px;background:{{ $tc['light'] }};border:1px solid {{ $tc['border'] }};border-radius:99px;padding:3px 10px;margin-bottom:14px;">
                    <span style="font-size:10px;font-weight:700;color:{{ $tc['text'] }};letter-spacing:.04em;">{{ $tc['label'] }}</span>
                </div>

                {{-- Date range --}}
                <div style="display:flex;align-items:center;gap:5px;margin-bottom:16px;font-size:12px;color:#6b7280;font-weight:500;">
                    <svg width="11" height="11" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @if($trip->start_date)
                        {{ $trip->start_date->format('d M') }}
                        @if($trip->end_date) &ndash; {{ $trip->end_date->format('d M Y') }} @endif
                    @else
                        <span style="color:#d1d5db;">No dates set</span>
                    @endif
                </div>

                {{-- Divider --}}
                <div style="height:1px;background:#f3f4f6;margin-bottom:14px;"></div>

                {{-- Bottom: spend + members --}}
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Total Spent</div>
                        <div style="font-size:18px;font-weight:900;color:{{ $trip->total_spend > 0 ? '#1e1b4b' : '#d1d5db' }};">
                            ₹{{ number_format($trip->total_spend, 0) }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;text-align:right;">{{ $trip->activeMembers->count() }} member{{ $trip->activeMembers->count() !== 1 ? 's' : '' }}</div>
                        <div style="display:flex;justify-content:flex-end;">
                            @foreach($trip->activeMembers->take(4) as $m)
                            <img src="{{ $m->avatar_url }}" title="{{ $m->display_name }}"
                                 style="width:28px;height:28px;border-radius:50%;border:2px solid #fff;margin-left:-8px;box-shadow:0 1px 4px rgba(0,0,0,.12);">
                            @endforeach
                            @if($trip->activeMembers->count() > 4)
                            <div style="width:28px;height:28px;border-radius:50%;border:2px solid #fff;margin-left:-8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:9px;color:#6b7280;font-weight:800;box-shadow:0 1px 4px rgba(0,0,0,.12);">
                                +{{ $trip->activeMembers->count() - 4 }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($trips->hasPages())
    <div style="margin-top:28px;display:flex;justify-content:center;">
        {{ $trips->links() }}
    </div>
    @endif
    @endif

</x-app-layout>
