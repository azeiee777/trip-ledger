<x-app-layout>
    <x-slot name="header">{{ $trip->name }}</x-slot>

    {{-- OTP reveal modal (shown once after adding a member with email) --}}
    @if(session('success_otp'))
    @php $otpData = session('success_otp'); @endphp
    <div x-data="{ open: true }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-1">Invite Sent!</h3>
            <p class="text-sm text-gray-500 mb-4">{{ $otpData['message'] }}</p>

            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-4">
                <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-2">OTP for {{ $otpData['name'] }}</p>
                <p class="text-4xl font-black text-indigo-700 tracking-widest font-mono">{{ $otpData['otp'] }}</p>
                <p class="text-xs text-gray-400 mt-2">You can share this OTP with them in person to activate immediately</p>
            </div>

            <p class="text-xs text-gray-400 mb-4">They will also receive this OTP in their email along with the verification link.</p>

            <button @click="open = false"
                    class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                Got it
            </button>
        </div>
    </div>
    @endif

    {{-- Trip meta bar --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <span class="text-sm text-gray-500">{{ $trip->destination ?? 'No destination' }}</span>
        @if($trip->start_date)
        <span class="text-gray-300">·</span>
        <span class="text-sm text-gray-500">{{ $trip->start_date->format('d M Y') }}{{ $trip->end_date ? ' – '.$trip->end_date->format('d M Y') : '' }}</span>
        @endif
        <span class="ml-auto flex gap-2">
            <a href="{{ route('trips.export-pdf', $trip) }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:5px;font-size:13px;padding:6px 13px;border:1.5px solid #c7d2fe;border-radius:9px;color:#4f46e5;background:#eef2ff;text-decoration:none;font-weight:600;transition:all .15s;"
               onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Export PDF
            </a>
            @can('update', $trip)
            <a href="{{ route('trips.edit', $trip) }}" class="text-sm px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Edit</a>
            @endcan
            @can('delete', $trip)
            <form method="POST" action="{{ route('trips.destroy', $trip) }}" id="del-trip-{{ $trip->id }}">
                @csrf @method('DELETE')
                <button type="button"
                        @click="$dispatch('open-delete-modal', {formId: 'del-trip-{{ $trip->id }}', title: 'Delete Trip', message: 'Delete \'{{ addslashes($trip->name) }}\' and all its expenses, members, and settlements? This cannot be undone.'})"
                        class="text-sm px-3 py-1.5 border border-red-200 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                    Delete Trip
                </button>
            </form>
            @endcan
            <button onclick="navigator.clipboard.writeText('{{ route('trips.otp.show', $trip) }}').then(()=>{ this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy Invite Link',2000) })"
                    class="text-sm px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-lg text-indigo-700 hover:bg-indigo-100 transition-colors">
                Copy Invite Link
            </button>
        </span>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: '{{ request('tab', 'overview') }}' }">
        <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-6 overflow-x-auto">
            @foreach(['overview'=>'Overview','expenses'=>'Expenses','members'=>'Members','settlement'=>'Settlement','itinerary'=>'Itinerary'] as $key => $label)
            <button @click="tab='{{ $key }}'"
                    :class="tab==='{{ $key }}' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 min-w-fit text-sm px-4 py-2 rounded-lg transition-all whitespace-nowrap">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Overview Tab --}}
        <div x-show="tab==='overview'">
            <div class="grid md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-100 p-5 text-center">
                    <p class="text-2xl font-bold text-gray-900">₹{{ number_format($trip->total_spend, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Group Spend</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-5 text-center">
                    <p class="text-2xl font-bold text-indigo-600">
                        ₹{{ $trip->member_count > 0 ? number_format($trip->total_spend / $trip->member_count, 2) : '0.00' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Per Head ({{ $trip->member_count }} members)</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-5 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $trip->expenses->count() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Expenses</p>
                </div>
            </div>

            {{-- Member balance table --}}
            @php
                $memberPaid = [];
                foreach($trip->expenses as $exp) {
                    if($exp->split_type !== 'personal' && $exp->approval_status === 'approved') {
                        $memberPaid[$exp->paid_by_member_id] = ($memberPaid[$exp->paid_by_member_id] ?? 0) + (float)$exp->amount;
                    }
                }
            @endphp
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden mb-6">
                <div class="px-5 py-3 border-b border-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Member Balances</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Member</th>
                                <th class="text-right px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Paid</th>
                                <th class="text-right px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Share</th>
                                <th class="text-right px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($trip->activeMembers as $member)
                            @php
                                $paid    = $memberPaid[$member->id] ?? 0;
                                $share   = $memberShares[$member->id] ?? 0;
                                $balance = $paid - $share;
                            @endphp
                            <tr>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $member->avatar_url }}" class="w-7 h-7 rounded-full">
                                        <span class="font-medium text-gray-900">{{ $member->display_name }}</span>
                                    </div>
                                </td>
                                <td class="text-right px-5 py-3 text-gray-700">₹{{ number_format($paid, 2) }}</td>
                                <td class="text-right px-5 py-3 text-gray-700">₹{{ number_format($share, 2) }}</td>
                                <td class="text-right px-5 py-3 font-semibold {{ $balance >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $balance >= 0 ? '+' : '-' }}₹{{ number_format(abs($balance), 2) }}
                                    <span class="text-xs font-normal">{{ $balance >= 0 ? 'overpaid' : 'owes' }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Charts row --}}
            @php
                $memberChartLabels = [];
                $memberChartData   = [];
                $memberChartColors = ['#6366f1','#22c55e','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899'];
                foreach ($trip->activeMembers as $m) {
                    $memberChartLabels[] = $m->display_name;
                    $memberChartData[]   = round((float)($memberPaid[$m->id] ?? 0), 2);
                }
                $showCategoryChart = $categorySpends->isNotEmpty();
                $showMemberChart   = array_sum($memberChartData) > 0;
            @endphp
            @if($showCategoryChart || $showMemberChart)
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;">

                {{-- Category Breakdown --}}
                @if($showCategoryChart)
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Category Breakdown</h3>
                    <div style="height:180px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            new Chart(document.getElementById('categoryChart'), {
                                type: 'doughnut',
                                data: {
                                    labels: @json($categorySpends->pluck('name')),
                                    datasets: [{ data: @json($categorySpends->pluck('total')), backgroundColor: @json($categorySpends->pluck('color')), borderWidth: 2, borderColor: '#fff' }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '65%',
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            callbacks: {
                                                label: ctx => ctx.label + ': ₹' + ctx.parsed.toLocaleString('en-IN')
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                </div>
                @endif

                {{-- Spending by Person --}}
                @if($showMemberChart)
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Spending by Person</h3>
                    <div style="height:180px;">
                        <canvas id="memberChart"></canvas>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            new Chart(document.getElementById('memberChart'), {
                                type: 'bar',
                                data: {
                                    labels: @json($memberChartLabels),
                                    datasets: [{
                                        data: @json($memberChartData),
                                        backgroundColor: @json(array_slice($memberChartColors, 0, count($memberChartLabels))),
                                        borderRadius: 6,
                                        borderSkipped: false,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            callbacks: {
                                                label: ctx => '₹' + ctx.parsed.y.toLocaleString('en-IN')
                                            }
                                        }
                                    },
                                    scales: {
                                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                                        y: {
                                            beginAtZero: true,
                                            grid: { color: '#f3f4f6' },
                                            ticks: {
                                                font: { size: 11 },
                                                callback: v => '₹' + v.toLocaleString('en-IN')
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                </div>
                @endif

            </div>
            @endif
        </div>

        {{-- Expenses Tab --}}
        <div x-show="tab==='expenses'" x-cloak>
            @can('addExpense', $trip)
            <div class="mb-4 flex justify-end">
                <button onclick="document.getElementById('addExpenseModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Expense
                </button>
            </div>
            @endcan

            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                @if($trip->expenses->isEmpty())
                    <div class="p-10 text-center text-gray-500 text-sm">No expenses yet. Add the first one!</div>
                @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Expense</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide hidden sm:table-cell">Date</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">Paid By</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($trip->expenses->sortByDesc('expense_date') as $expense)
                        <tr class="hover:bg-gray-50/50 {{ $expense->approval_status === 'pending_approval' ? 'bg-amber-50/40' : ($expense->approval_status === 'rejected' ? 'bg-red-50/40' : '') }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                          style="background:{{ $expense->category?->color ?? '#9CA3AF' }}">
                                        {{ strtoupper(substr($expense->category?->name ?? 'M', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $expense->title }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-400">{{ ucfirst(str_replace('_',' ',$expense->split_type)) }}</span>
                                            @if($expense->approval_status === 'pending_approval')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                            @elseif($expense->approval_status === 'rejected')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $expense->paidByMember->display_name }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $expense->approval_status === 'pending_approval' ? 'text-amber-600' : ($expense->approval_status === 'rejected' ? 'text-red-400 line-through' : 'text-gray-900') }}">
                                ₹{{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end items-center gap-2 flex-wrap">
                                    @if($isAdmin && $expense->approval_status === 'pending_approval')
                                        <form method="POST" action="{{ route('expenses.approve', $expense) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs text-emerald-600 hover:underline font-medium">Approve</button>
                                        </form>
                                        <button onclick="document.getElementById('rejectModal-{{ $expense->id }}').classList.remove('hidden')"
                                                class="text-xs text-red-500 hover:underline font-medium">Reject</button>
                                    @endif
                                    @if($isAdmin || $expense->created_by === auth()->id())
                                        <a href="{{ route('expenses.edit', $expense) }}" class="text-xs text-indigo-600 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" id="del-exp-{{ $expense->id }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    @click="$dispatch('open-delete-modal', {formId: 'del-exp-{{ $expense->id }}', title: 'Delete Expense', message: 'Delete &quot;{{ addslashes($expense->title) }}&quot; (₹{{ number_format($expense->amount, 2) }})? This cannot be undone.'})"
                                                    class="text-xs text-red-500 hover:underline">Del</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        {{-- Reject reason modal --}}
                        @if($isAdmin && $expense->approval_status === 'pending_approval')
                        <tr id="rejectModal-{{ $expense->id }}" class="hidden bg-red-50">
                            <td colspan="5" class="px-4 py-3">
                                <form method="POST" action="{{ route('expenses.reject', $expense) }}" class="flex items-center gap-3">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Rejection reason (optional)"
                                           class="flex-1 border border-red-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400">
                                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700">Confirm Reject</button>
                                    <button type="button"
                                            onclick="document.getElementById('rejectModal-{{ $expense->id }}').classList.add('hidden')"
                                            class="text-xs text-gray-500 hover:underline">Cancel</button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Members Tab --}}
        <div x-show="tab==='members'" x-cloak>
            @can('update', $trip)
            <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Member</h3>
                <form method="POST" action="{{ route('trips.members.store', $trip) }}" class="flex flex-wrap gap-3">
                    @csrf
                    <input type="text" name="guest_name" placeholder="Guest name (e.g. Nishi)"
                           class="flex-1 min-w-48 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" name="upi_id" placeholder="UPI ID (optional)"
                           class="flex-1 min-w-48 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        Add
                    </button>
                </form>
            </div>
            @endcan

            <div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
                @forelse($trip->members as $m)
                <div x-data="{ addEmail: false }" class="px-5 py-3">
                    <div class="flex items-center gap-4">
                        <img src="{{ $m->avatar_url }}" class="w-10 h-10 rounded-full flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900">{{ $m->display_name }}</p>
                            <div class="flex items-center gap-2 flex-wrap mt-0.5">
                                @if($m->invite_email)
                                    <span class="text-xs text-gray-400">{{ $m->invite_email }}</span>
                                    @if($m->invite_status === 'pending')
                                        <span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">Invite pending</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">No email</span>
                                @endif
                                @if($m->upi_id)
                                    <span class="text-xs text-gray-400">· {{ $m->upi_id }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Status badge --}}
                        <span class="text-xs px-2 py-1 rounded-full flex-shrink-0
                            {{ $m->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $m->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        @can('update', $trip)
                        @if($m->role !== 'admin')
                        <div class="flex items-center gap-2 flex-shrink-0">
                            {{-- Add / change email --}}
                            @if(! $m->invite_email || $m->invite_status === 'pending')
                            <button @click="addEmail = !addEmail"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded px-2 py-1">
                                {{ $m->invite_email ? 'Resend OTP' : 'Add Email' }}
                            </button>
                            @endif

                            <form method="POST" action="{{ route('trips.members.toggle', [$trip, $m]) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded px-2 py-1">
                                    {{ $m->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            @if($m->expensesPaid->count() === 0)
                            <form method="POST" action="{{ route('trips.members.destroy', [$trip, $m]) }}" id="del-member-{{ $m->id }}">
                                @csrf @method('DELETE')
                                <button type="button"
                                        @click="$dispatch('open-delete-modal', {formId: 'del-member-{{ $m->id }}', title: 'Remove Member', message: 'Remove {{ addslashes($m->display_name) }} from this trip? This cannot be undone.'})"
                                        class="text-xs text-red-500 hover:text-red-700 border border-red-200 rounded px-2 py-1">Remove</button>
                            </form>
                            @endif
                        </div>
                        @endif
                        @endcan
                    </div>

                    {{-- Inline "Add / Update Email" form (admin only, toggles open) --}}
                    @can('update', $trip)
                    @if($m->role !== 'admin')
                    <div x-show="addEmail" x-cloak class="mt-3 pl-14">
                        <form method="POST" action="{{ route('trips.members.update-email', [$trip, $m]) }}"
                              class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="email" name="invite_email"
                                   value="{{ $m->invite_email }}"
                                   placeholder="member@email.com" required
                                   class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="submit"
                                    class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700">
                                Send OTP
                            </button>
                            <button type="button" @click="addEmail = false"
                                    class="text-xs text-gray-400 hover:underline">Cancel</button>
                        </form>
                        <p class="text-xs text-gray-400 mt-1">
                            An OTP will be emailed to them and shown to you here. They must verify to get access.
                        </p>
                    </div>
                    @endif
                    @endcan
                </div>
                @empty
                <div class="p-6 text-center text-sm text-gray-500">No members yet.</div>
                @endforelse
            </div>

            {{-- Car Groups --}}
            @if($trip->carGroups->isNotEmpty())
            <div class="mt-4 bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Car Groups</h3>
                <div class="space-y-3">
                    @foreach($trip->carGroups as $cg)
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $cg->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $cg->members()->pluck('guest_name')->filter()->join(', ') }}</p>
                        </div>
                        @can('update', $trip)
                        <form method="POST" action="{{ route('trips.cargroups.destroy', [$trip, $cg]) }}" id="del-cg-{{ $cg->id }}">
                            @csrf @method('DELETE')
                            <button type="button"
                                    @click="$dispatch('open-delete-modal', {formId: 'del-cg-{{ $cg->id }}', title: 'Remove Car Group', message: 'Remove the &quot;{{ addslashes($cg->name) }}&quot; car group? This cannot be undone.'})"
                                    class="text-xs text-red-500 hover:underline">Remove</button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Settlement Tab --}}
        <div x-show="tab==='settlement'" x-cloak>

            {{-- Header row: summary stats + recalculate --}}
            @php
                $pending  = $trip->settlements->where('status', 'pending');
                $partial  = $trip->settlements->where('status', 'partial');
                $paid     = $trip->settlements->where('status', 'paid');
                $allDone  = $trip->settlements->isNotEmpty()
                            && $trip->settlements->where('status', '!=', 'paid')->isEmpty();
            @endphp
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-4">
                    @if($trip->settlements->isNotEmpty())
                    <span class="text-sm text-gray-500">
                        <span class="font-semibold text-gray-900">{{ $pending->count() + $partial->count() }}</span> pending
                        &nbsp;·&nbsp;
                        <span class="font-semibold text-emerald-600">{{ $paid->count() }}</span> settled
                    </span>
                    @if($allDone)
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        All settled!
                    </span>
                    @endif
                    @endif
                </div>
                @can('update', $trip)
                <form method="POST" action="{{ route('trips.settlements.calculate', $trip) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Recalculate
                    </button>
                </form>
                @endcan
            </div>

            {{-- Balance summary cards --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.75rem;margin-bottom:1.25rem;">
                @foreach($trip->activeMembers as $member)
                @php
                    $mPaid    = $memberPaid[$member->id] ?? 0;
                    $mShare   = $memberShares[$member->id] ?? 0;
                    $mBalance = $mPaid - $mShare;
                    $isCredit = $mBalance >= 0;
                    $balColor = $isCredit ? '#059669' : '#dc2626';
                    $balBg    = $isCredit ? '#ecfdf5' : '#fef2f2';
                    $balBorder= $isCredit ? '#a7f3d0' : '#fecaca';
                @endphp
                <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">
                    {{-- Colour strip top --}}
                    <div style="height:4px;background:{{ $isCredit ? '#10b981' : '#f87171' }};"></div>
                    <div style="padding:14px 16px;">
                        {{-- Avatar + name --}}
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                            <img src="{{ $member->avatar_url }}"
                                 style="width:36px;height:36px;border-radius:50%;flex-shrink:0;">
                            <div style="min-width:0;">
                                <p style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $member->display_name }}
                                </p>
                                <p style="font-size:11px;color:#9ca3af;">
                                    {{ $member->role === 'admin' ? '★ Admin' : 'Member' }}
                                </p>
                            </div>
                        </div>
                        {{-- Paid / Share row --}}
                        <div style="display:flex;gap:8px;margin-bottom:10px;">
                            <div style="flex:1;background:#f9fafb;border-radius:8px;padding:8px 10px;text-align:center;">
                                <p style="font-size:10px;color:#6b7280;margin-bottom:2px;">Paid</p>
                                <p style="font-size:13px;font-weight:600;color:#111827;">₹{{ number_format($mPaid, 0) }}</p>
                            </div>
                            <div style="flex:1;background:#f9fafb;border-radius:8px;padding:8px 10px;text-align:center;">
                                <p style="font-size:10px;color:#6b7280;margin-bottom:2px;">Share</p>
                                <p style="font-size:13px;font-weight:600;color:#111827;">₹{{ number_format($mShare, 0) }}</p>
                            </div>
                        </div>
                        {{-- Balance badge --}}
                        <div style="background:{{ $balBg }};border:1px solid {{ $balBorder }};border-radius:8px;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;">
                            <span style="font-size:11px;color:{{ $balColor }};font-weight:500;">
                                {{ $isCredit ? 'Overpaid' : 'Owes' }}
                            </span>
                            <span style="font-size:14px;font-weight:700;color:{{ $balColor }};">
                                {{ $isCredit ? '+' : '-' }}₹{{ number_format(abs($mBalance), 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- No settlements yet --}}
            @if($trip->settlements->isEmpty())
                @if($trip->expenses->where('split_type','!=','personal')->where('approval_status','approved')->isNotEmpty())
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
                    <p class="text-sm text-amber-800 font-medium mb-3">Expenses exist but settlements haven't been calculated yet.</p>
                    @can('update', $trip)
                    <form method="POST" action="{{ route('trips.settlements.calculate', $trip) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-5 py-2 bg-amber-600 text-white text-sm font-semibold rounded-lg hover:bg-amber-700 transition-colors">
                            Generate Settlements Now
                        </button>
                    </form>
                    @endcan
                </div>
                @else
                <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400 text-sm">
                    No group expenses to settle yet.
                </div>
                @endif
            @else

            {{-- Pending / Partial settlements --}}
            @if($pending->count() + $partial->count() > 0)
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden mb-4">
                <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Payments Needed</h3>
                    <span class="text-xs text-gray-400">{{ $pending->count() + $partial->count() }} remaining</span>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($trip->settlements->whereIn('status', ['pending','partial']) as $s)
                    @php
                        $progressPct = $s->amount > 0 ? round(($s->paid_amount / $s->amount) * 100) : 0;
                    @endphp
                    <div class="px-5 py-4">
                        {{-- Payer → Receiver row --}}
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <img src="{{ $s->payer->avatar_url }}" class="w-8 h-8 rounded-full flex-shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $s->payer->display_name }}</p>
                                    <p class="text-xs text-gray-400">pays</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-center">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                <span class="text-xs font-bold text-gray-900 mt-0.5">₹{{ number_format($s->remaining_amount, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
                                <div class="min-w-0 text-right">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $s->receiver->display_name }}</p>
                                    <p class="text-xs text-gray-400">receives</p>
                                </div>
                                <img src="{{ $s->receiver->avatar_url }}" class="w-8 h-8 rounded-full flex-shrink-0">
                            </div>
                        </div>

                        {{-- Progress bar for partial --}}
                        @if($s->status === 'partial')
                        <div class="mb-3">
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span>Paid ₹{{ number_format($s->paid_amount, 2) }} of ₹{{ number_format($s->amount, 2) }}</span>
                                <span>{{ $progressPct }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="bg-amber-500 h-1.5 rounded-full" style="width:{{ $progressPct }}%"></div>
                            </div>
                        </div>
                        @endif

                        {{-- Action buttons --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs px-2 py-1 rounded-full {{ $s->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($s->status) }}
                            </span>
                            <div class="flex gap-2 ml-auto">
                                @if($s->receiver->upi_id)
                                <a href="{{ $s->generateUpiLink() }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                    Pay via UPI
                                </a>
                                @endif
                                <button type="button"
                                        onclick="document.getElementById('payModal-{{ $s->id }}').classList.remove('hidden')"
                                        class="text-xs px-3 py-1.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                    Record Payment
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Record Payment Modal --}}
                    <div id="payModal-{{ $s->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div class="bg-white rounded-xl w-full max-w-sm shadow-xl">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Record Payment</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $s->payer->display_name }} → {{ $s->receiver->display_name }}
                                    </p>
                                </div>
                                <button type="button" onclick="document.getElementById('payModal-{{ $s->id }}').classList.add('hidden')"
                                        class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <form method="POST" action="{{ route('settlements.mark-paid', $s) }}" class="p-6 space-y-4">
                                @csrf @method('PATCH')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid (₹) <span class="text-red-500">*</span></label>
                                    <input type="number" name="paid_amount" step="0.01" min="0.01"
                                           value="{{ $s->remaining_amount }}" required
                                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <p class="text-xs text-gray-400 mt-1">Remaining: ₹{{ number_format($s->remaining_amount, 2) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                                    <select name="payment_method" required
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                        <option value="upi">UPI</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank Transfer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                                    <input type="text" name="payment_note" maxlength="255"
                                           placeholder="e.g. Google Pay, PhonePe"
                                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div class="flex gap-3 pt-1">
                                    <button type="submit"
                                            class="flex-1 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                                        Confirm Payment
                                    </button>
                                    <button type="button"
                                            onclick="document.getElementById('payModal-{{ $s->id }}').classList.add('hidden')"
                                            class="flex-1 py-2.5 border border-gray-200 text-sm text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Completed settlements --}}
            @if($paid->count() > 0)
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-50">
                    <h3 class="text-sm font-semibold text-gray-500">Completed Payments</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($trip->settlements->where('status','paid') as $s)
                    <div class="flex flex-wrap items-center gap-3 px-5 py-3">
                        <img src="{{ $s->payer->avatar_url }}" class="w-7 h-7 rounded-full opacity-70">
                        <span class="text-sm text-gray-500">{{ $s->payer->display_name }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        <img src="{{ $s->receiver->avatar_url }}" class="w-7 h-7 rounded-full opacity-70">
                        <span class="text-sm text-gray-500">{{ $s->receiver->display_name }}</span>
                        <span class="ml-auto font-semibold text-gray-400 line-through text-sm">₹{{ number_format($s->amount, 2) }}</span>
                        <span class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-medium">Paid ✓</span>
                        @if($s->payment_method)
                        <span class="text-xs text-gray-400">via {{ ucfirst($s->payment_method) }}</span>
                        @endif
                        @if($s->settled_at)
                        <span class="text-xs text-gray-400">{{ $s->settled_at->format('d M') }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @endif
        </div>

        {{-- Itinerary Tab --}}
        <div x-show="tab==='itinerary'" x-cloak
             x-data="{
                stopModal: false,
                editMode: false,
                editId: null,
                form: { name:'', place_type:'attraction', visit_date:'', visit_time:'', address:'', notes:'', estimated_cost:'', expense_id:'' },
                openAdd() { this.editMode=false; this.editId=null; this.form={name:'',place_type:'attraction',visit_date:'',visit_time:'',address:'',notes:'',estimated_cost:'',expense_id:''}; this.stopModal=true; },
                openEdit(stop) { this.editMode=true; this.editId=stop.id; this.form={name:stop.name,place_type:stop.place_type,visit_date:stop.visit_date??'',visit_time:stop.visit_time??'',address:stop.address??'',notes:stop.notes??'',estimated_cost:stop.estimated_cost??'',expense_id:stop.expense_id??''}; this.stopModal=true; }
             }">

            @can('update', $trip)
            <div class="mb-4 flex justify-end">
                <button @click="openAdd()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Stop
                </button>
            </div>
            @endcan

            @php
                $typeConfig = [
                    'hotel'      => ['emoji' => '🏨', 'label' => 'Hotel / Stay',  'dot' => 'bg-indigo-500'],
                    'attraction' => ['emoji' => '🏔️', 'label' => 'Attraction',    'dot' => 'bg-green-500'],
                    'restaurant' => ['emoji' => '🍽️', 'label' => 'Food / Dining', 'dot' => 'bg-orange-500'],
                    'activity'   => ['emoji' => '🎯', 'label' => 'Activity',       'dot' => 'bg-purple-500'],
                    'transit'    => ['emoji' => '🚌', 'label' => 'Transit',        'dot' => 'bg-blue-500'],
                    'other'      => ['emoji' => '📍', 'label' => 'Other',          'dot' => 'bg-gray-400'],
                ];
                $stopsByDate = $trip->stops->groupBy(fn($s) => $s->visit_date?->toDateString() ?? '__none__');
                $sortedDates = $stopsByDate->keys()->sort(fn($a,$b) => $a === '__none__' ? -1 : ($b === '__none__' ? 1 : strcmp($a,$b)))->values();
            @endphp

            @if($trip->stops->isEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
                    <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-2xl">🗺️</span>
                    </div>
                    <p class="text-gray-500 text-sm">No stops added yet.</p>
                    @can('update', $trip)
                    <p class="text-gray-400 text-xs mt-1">Click "Add Stop" to start building the trip itinerary.</p>
                    @endcan
                </div>
            @else
            <div class="space-y-6">
                @foreach($sortedDates as $dateKey)
                @php $dayStops = $stopsByDate[$dateKey]; @endphp

                {{-- Day header --}}
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-px flex-1 bg-gray-200"></div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide px-2">
                            @if($dateKey === '__none__')
                                Flexible / No Date
                            @else
                                {{ \Carbon\Carbon::parse($dateKey)->format('l, d M Y') }}
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-gray-200"></div>
                    </div>

                    {{-- Timeline --}}
                    <div class="relative pl-6">
                        {{-- Vertical line --}}
                        <div class="absolute left-2 top-2 bottom-2 w-px bg-gray-200"></div>

                        <div class="space-y-4">
                            @foreach($dayStops as $stop)
                            @php $cfg = $typeConfig[$stop->place_type] ?? $typeConfig['other']; @endphp
                            <div class="relative">
                                {{-- Timeline dot --}}
                                <div class="absolute -left-4 top-3 w-3 h-3 rounded-full {{ $cfg['dot'] }} ring-2 ring-white"></div>

                                <div class="bg-white rounded-xl border border-gray-100 p-4 hover:border-gray-200 transition-colors">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3 flex-1 min-w-0">
                                            <span class="text-xl leading-none mt-0.5 flex-shrink-0">{{ $cfg['emoji'] }}</span>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="font-semibold text-gray-900">{{ $stop->name }}</p>
                                                    <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{{ $cfg['label'] }}</span>
                                                    @if($stop->visit_time)
                                                    <span class="text-xs text-indigo-600 font-medium">{{ \Carbon\Carbon::createFromFormat('H:i:s', $stop->visit_time)->format('g:i A') }}</span>
                                                    @endif
                                                </div>

                                                @if($stop->address)
                                                <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    {{ $stop->address }}
                                                </p>
                                                @endif

                                                @if($stop->notes)
                                                <p class="text-sm text-gray-600 mt-2 leading-relaxed">{{ $stop->notes }}</p>
                                                @endif

                                                <div class="flex items-center gap-4 mt-2 flex-wrap">
                                                    @if($stop->estimated_cost)
                                                    <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium">
                                                        Est. ₹{{ number_format($stop->estimated_cost, 2) }}
                                                    </span>
                                                    @endif
                                                    @if($stop->expense)
                                                    <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                                                        Linked: {{ $stop->expense->title }} (₹{{ number_format($stop->expense->amount, 2) }})
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @can('update', $trip)
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button @click="openEdit({
                                                        id: {{ $stop->id }},
                                                        name: @js($stop->name),
                                                        place_type: '{{ $stop->place_type }}',
                                                        visit_date: '{{ $stop->visit_date?->format('Y-m-d') }}',
                                                        visit_time: '{{ $stop->visit_time ? substr($stop->visit_time,0,5) : '' }}',
                                                        address: @js($stop->address ?? ''),
                                                        notes: @js($stop->notes ?? ''),
                                                        estimated_cost: '{{ $stop->estimated_cost ?? '' }}',
                                                        expense_id: '{{ $stop->expense_id ?? '' }}'
                                                    })"
                                                    class="p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('trips.stops.destroy', [$trip, $stop]) }}" id="del-stop-{{ $stop->id }}">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                        @click="$dispatch('open-delete-modal', {formId: 'del-stop-{{ $stop->id }}', title: 'Remove Stop', message: 'Remove this stop from the itinerary? This cannot be undone.'})"
                                                        class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors" title="Remove">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Add / Edit Stop Modal --}}
            <div x-show="stopModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="stopModal=false">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900" x-text="editMode ? 'Edit Stop' : 'Add Stop'"></h3>
                        <button @click="stopModal=false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Store form (shown when adding) --}}
                        <form x-show="!editMode"
                              method="POST" action="{{ route('trips.stops.store', $trip) }}"
                              class="space-y-4">
                            @csrf
                            @include('trip::_stop-form-fields')
                        </form>

                        {{-- Update forms (one per stop, shown when editing) --}}
                        @foreach($trip->stops as $stop)
                        <form x-show="editMode && editId === {{ $stop->id }}"
                              method="POST" action="{{ route('trips.stops.update', [$trip, $stop]) }}"
                              class="space-y-4">
                            @csrf @method('PUT')
                            @include('trip::_stop-form-fields')
                        </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Expense Modal --}}
    @php $hasExpenseErrors = $errors->any() && old('_expense_form'); @endphp
    <div id="addExpenseModal" class="{{ $hasExpenseErrors ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Add Expense</h3>
                <button type="button" onclick="document.getElementById('addExpenseModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('trips.expenses.store', $trip) }}"
                  x-data="{
                    splitType: '{{ old('split_type', 'equal') }}',
                    pctTotal: 0,
                    updatePct() {
                        let t = 0;
                        document.querySelectorAll('.pct-input').forEach(i => t += parseFloat(i.value||0));
                        this.pctTotal = Math.round(t * 100) / 100;
                    }
                  }"
                  class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_expense_form" value="1">

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Title --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                           placeholder="e.g. Hotel room, Petrol, Dinner"
                           class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Amount + Date --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Amount (₹) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required placeholder="0.00"
                               class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                               class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                {{-- Category + Paid By --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Paid By <span class="text-red-500">*</span></label>
                        <select name="paid_by_member_id" required class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select member</option>
                            @foreach($trip->activeMembers as $m)
                            <option value="{{ $m->id }}" {{ old('paid_by_member_id') == $m->id ? 'selected' : '' }}>{{ $m->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Split Type --}}
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-2">Split Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-5 gap-1.5">
                        @foreach(['equal'=>'Equal','personal'=>'Personal','percentage'=>'By %','per_car'=>'Per Car','custom'=>'Custom'] as $k => $v)
                        <label class="cursor-pointer">
                            <input type="radio" name="split_type" value="{{ $k }}" x-model="splitType"
                                   class="sr-only peer" {{ $k === 'equal' ? 'checked' : '' }}>
                            <div class="peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600
                                        border border-gray-200 rounded-lg py-2 px-1 text-center hover:border-indigo-300 transition-colors text-xs font-semibold">
                                {{ $v }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- EQUAL — member checkboxes --}}
                <div x-show="splitType === 'equal'" x-cloak>
                    <label class="text-sm font-medium text-gray-700 block mb-2">Who's included? <span class="text-xs text-gray-400 font-normal">(uncheck to exclude)</span></label>
                    <div class="space-y-2 bg-gray-50 rounded-xl p-3">
                        @foreach($trip->activeMembers as $m)
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-white transition-colors">
                            <input type="checkbox" name="splits[{{ $m->id }}]" value="1" checked
                                   :disabled="splitType !== 'equal'"
                                   class="w-4 h-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <img src="{{ $m->avatar_url }}" class="w-7 h-7 rounded-full flex-shrink-0">
                            <span class="text-sm text-gray-800 flex-1">{{ $m->display_name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- CUSTOM — amount per member --}}
                <div x-show="splitType === 'custom'" x-cloak>
                    <label class="text-sm font-medium text-gray-700 block mb-2">Amount per person (₹)</label>
                    <div class="space-y-2 bg-gray-50 rounded-xl p-3">
                        @foreach($trip->activeMembers as $m)
                        <div class="flex items-center gap-3">
                            <img src="{{ $m->avatar_url }}" class="w-7 h-7 rounded-full flex-shrink-0">
                            <span class="text-sm text-gray-800 flex-1">{{ $m->display_name }}</span>
                            <input type="number" name="splits[{{ $m->id }}]" min="0" step="0.01"
                                   :disabled="splitType !== 'custom'"
                                   placeholder="0.00"
                                   class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- PERCENTAGE — % per member --}}
                <div x-show="splitType === 'percentage'" x-cloak>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Percentage per person</label>
                        <span class="text-xs font-semibold"
                              :class="pctTotal === 100 ? 'text-emerald-600' : 'text-amber-600'"
                              x-text="'Total: ' + pctTotal + '%'"></span>
                    </div>
                    <div class="space-y-2 bg-gray-50 rounded-xl p-3">
                        @foreach($trip->activeMembers as $m)
                        <div class="flex items-center gap-3">
                            <img src="{{ $m->avatar_url }}" class="w-7 h-7 rounded-full flex-shrink-0">
                            <span class="text-sm text-gray-800 flex-1">{{ $m->display_name }}</span>
                            <div class="flex items-center gap-1">
                                <input type="number" name="splits[{{ $m->id }}]" min="0" max="100" step="0.01"
                                       :disabled="splitType !== 'percentage'"
                                       placeholder="0"
                                       @input="updatePct()"
                                       class="pct-input w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <span class="text-xs text-gray-400">%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p x-show="pctTotal !== 100 && pctTotal > 0" class="text-xs text-amber-600 mt-1.5">
                        Percentages must add up to exactly 100%
                    </p>
                </div>

                {{-- PER CAR — car group selector --}}
                <div x-show="splitType === 'per_car'" x-cloak>
                    <label class="text-sm font-medium text-gray-700">Car Group <span class="text-red-500">*</span></label>
                    @if($trip->carGroups->isEmpty())
                        <p class="mt-1 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            No car groups yet. Go to the <strong>Members</strong> tab to create one first.
                        </p>
                    @else
                    <select name="car_group_id" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select car group</option>
                        @foreach($trip->carGroups as $cg)
                        <option value="{{ $cg->id }}">{{ $cg->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>

                {{-- PERSONAL — info note --}}
                <div x-show="splitType === 'personal'" x-cloak>
                    <p class="text-xs text-gray-500 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                        Personal expenses are only visible to you and the trip admin. They are not included in group settlements.
                    </p>
                </div>

                {{-- Note --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Note</label>
                    <input type="text" name="note" value="{{ old('note') }}" maxlength="500"
                           placeholder="Optional note about this expense"
                           class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                        Add Expense
                    </button>
                    <button type="button" onclick="document.getElementById('addExpenseModal').classList.add('hidden')"
                            class="flex-1 py-2.5 border border-gray-200 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
