<x-app-layout>
    <x-slot name="header">Edit Expense</x-slot>

    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl border border-gray-100 p-6">

            {{-- Back link --}}
            <a href="{{ route('trips.show', ['trip' => $trip, 'tab' => 'expenses']) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 mb-5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to trip
            </a>

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @php
                $splitsByMember = $expense->splits->keyBy('trip_member_id');
            @endphp

            <form method="POST" action="{{ route('expenses.update', $expense) }}"
                  x-data="{
                    splitType: '{{ old('split_type', $expense->split_type) }}',
                    pctTotal: 0,
                    updatePct() {
                        let t = 0;
                        document.querySelectorAll('.pct-input').forEach(i => t += parseFloat(i.value||0));
                        this.pctTotal = Math.round(t * 100) / 100;
                    },
                    init() { this.updatePct(); }
                  }"
                  class="space-y-5">
                @csrf @method('PUT')

                {{-- Title + Amount --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $expense->title) }}" required maxlength="255"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₹) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" required min="0.01" step="0.01"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('amount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Date + Category --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('expense_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Paid By --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paid By <span class="text-red-500">*</span></label>
                    <select name="paid_by_member_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select member</option>
                        @foreach($trip->activeMembers as $m)
                        <option value="{{ $m->id }}" {{ old('paid_by_member_id', $expense->paid_by_member_id) == $m->id ? 'selected' : '' }}>
                            {{ $m->display_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('paid_by_member_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Split Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Split Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-5 gap-1.5">
                        @foreach(['equal'=>'Equal','personal'=>'Personal','percentage'=>'By %','per_car'=>'Per Car','custom'=>'Custom'] as $k => $v)
                        <label class="cursor-pointer">
                            <input type="radio" name="split_type" value="{{ $k }}" x-model="splitType"
                                   class="sr-only peer" {{ old('split_type', $expense->split_type) === $k ? 'checked' : '' }}>
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
                        @php $split = $splitsByMember->get($m->id); @endphp
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-white transition-colors">
                            <input type="checkbox" name="splits[{{ $m->id }}]" value="1"
                                   {{ old('split_type', $expense->split_type) !== 'equal' || ($split && !$split->is_excluded) || !$split ? 'checked' : '' }}
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
                        @php $split = $splitsByMember->get($m->id); @endphp
                        <div class="flex items-center gap-3">
                            <img src="{{ $m->avatar_url }}" class="w-7 h-7 rounded-full flex-shrink-0">
                            <span class="text-sm text-gray-800 flex-1">{{ $m->display_name }}</span>
                            <input type="number" name="splits[{{ $m->id }}]" min="0" step="0.01"
                                   value="{{ old("splits.{$m->id}", $split?->share_amount ?? '') }}"
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
                        @php $split = $splitsByMember->get($m->id); @endphp
                        <div class="flex items-center gap-3">
                            <img src="{{ $m->avatar_url }}" class="w-7 h-7 rounded-full flex-shrink-0">
                            <span class="text-sm text-gray-800 flex-1">{{ $m->display_name }}</span>
                            <div class="flex items-center gap-1">
                                <input type="number" name="splits[{{ $m->id }}]" min="0" max="100" step="0.01"
                                       value="{{ old("splits.{$m->id}", $split?->share_percentage ?? '') }}"
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
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Car Group <span class="text-red-500">*</span></label>
                    @if($trip->carGroups->isEmpty())
                        <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            No car groups yet. Go back and create one from the Members tab first.
                        </p>
                    @else
                    <select name="car_group_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select car group</option>
                        @foreach($trip->carGroups as $cg)
                        <option value="{{ $cg->id }}" {{ old('car_group_id', $expense->car_group_id) == $cg->id ? 'selected' : '' }}>{{ $cg->name }}</option>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <input type="text" name="note" value="{{ old('note', $expense->note) }}" maxlength="500"
                           placeholder="Optional note about this expense"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('trips.show', ['trip' => $trip, 'tab' => 'expenses']) }}"
                       class="flex-1 py-2.5 border border-gray-200 text-sm text-gray-700 rounded-lg hover:bg-gray-50 text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
