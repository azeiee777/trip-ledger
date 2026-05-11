<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $trip->name }} – TripLedger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">

{{-- Top bar --}}
<header class="bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <div class="w-7 h-7 bg-indigo-600 rounded-md flex items-center justify-center">
            <span class="text-white font-bold text-sm">T</span>
        </div>
        <span class="font-bold text-gray-900">TripLedger</span>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500">Viewing as <strong>{{ $member->display_name }}</strong></span>
        <a href="{{ route('register') }}"
           class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">
            Create Account
        </a>
        <a href="{{ route('login') }}" class="text-xs text-indigo-600 hover:underline">Sign In</a>
    </div>
</header>

<div class="max-w-4xl mx-auto px-4 py-8">

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
        {{ session('success') }}
    </div>
    @endif

    {{-- Trip header --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
        <div class="flex items-start justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $trip->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if($trip->destination){{ $trip->destination }} &middot; @endif
                    @if($trip->start_date){{ $trip->start_date->format('d M Y') }}{{ $trip->end_date ? ' – '.$trip->end_date->format('d M Y') : '' }}@endif
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold
                {{ $trip->status === 'ongoing' ? 'bg-green-100 text-green-700' : ($trip->status === 'completed' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-700') }}">
                {{ ucfirst($trip->status) }}
            </span>
        </div>
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="text-center p-3 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500">Total Spend</p>
                <p class="text-xl font-bold text-gray-900">₹{{ number_format($trip->total_spend, 2) }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500">Members</p>
                <p class="text-xl font-bold text-gray-900">{{ $trip->activeMembers->count() }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500">Expenses</p>
                <p class="text-xl font-bold text-gray-900">{{ $trip->expenses->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Expenses</h2>
        </div>
        @if($trip->expenses->isEmpty())
        <div class="p-10 text-center text-gray-400 text-sm">No approved expenses yet.</div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Expense</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Date</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Paid By</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($trip->expenses->sortByDesc('expense_date') as $expense)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                  style="background:{{ $expense->category?->color ?? '#9CA3AF' }}">
                                {{ strtoupper(substr($expense->category?->name ?? 'M', 0, 1)) }}
                            </span>
                            <div>
                                <p class="font-medium text-gray-900">{{ $expense->title }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_',' ',$expense->split_type)) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $expense->paidByMember->display_name }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">₹{{ number_format($expense->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Settlements --}}
    @if($trip->settlements->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Settlements</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($trip->settlements->where('status','pending') as $s)
            <div class="px-5 py-3 flex items-center justify-between text-sm">
                <span class="text-gray-700">
                    <strong>{{ $s->payer->display_name }}</strong>
                    <span class="text-gray-400 mx-2">→</span>
                    <strong>{{ $s->receiver->display_name }}</strong>
                </span>
                <span class="font-semibold text-gray-900">₹{{ number_format($s->amount, 2) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- CTA to create account --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 text-center">
        <h3 class="font-semibold text-indigo-900 mb-1">Want full access?</h3>
        <p class="text-sm text-indigo-700 mb-4">Create a free TripLedger account to add expenses, track settlements, and manage all your trips.</p>
        <a href="{{ route('register') }}"
           class="inline-block px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
            Create Free Account
        </a>
    </div>

</div>
</body>
</html>
