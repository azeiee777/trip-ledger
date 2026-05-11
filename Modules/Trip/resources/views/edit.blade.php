<x-app-layout>
    <x-slot name="header">Edit Trip</x-slot>
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <form method="POST" action="{{ route('trips.update', $trip) }}" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trip Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $trip->name) }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
                    <input type="text" name="destination" value="{{ old('destination', $trip->destination) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="trip_type" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                            @foreach(['road_trip'=>'Road Trip','flight'=>'Flight','local'=>'Local','international'=>'International','pilgrimage'=>'Pilgrimage','family'=>'Family'] as $k => $v)
                            <option value="{{ $k }}" {{ old('trip_type',$trip->trip_type) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                            @foreach(['upcoming'=>'Upcoming','ongoing'=>'Ongoing','completed'=>'Completed','archived'=>'Archived'] as $k => $v)
                            <option value="{{ $k }}" {{ old('status',$trip->status) === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $trip->start_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $trip->end_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description', $trip->description) }}</textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">Save Changes</button>
                    <a href="{{ route('trips.show', $trip) }}" class="flex-1 py-2.5 text-center border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
                </div>
            </form>

            {{-- Danger Zone --}}
            <div style="margin-top:24px;padding-top:20px;border-top:1px solid #fee2e2;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <svg width="14" height="14" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span style="font-size:11px;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:.07em;">Danger Zone</span>
                </div>
                <p style="font-size:12px;color:#9ca3af;margin-bottom:12px;">Permanently deletes this trip along with all expenses, members, and settlements. This cannot be undone.</p>
                <form method="POST" action="{{ route('trips.destroy', $trip) }}" id="del-trip-edit-{{ $trip->id }}">
                    @csrf @method('DELETE')
                    <button type="button"
                            @click="$dispatch('open-delete-modal', {formId: 'del-trip-edit-{{ $trip->id }}', title: 'Delete Trip', message: 'Permanently delete \'{{ addslashes($trip->name) }}\' and ALL its expenses, members, and settlements? This cannot be undone.'})"
                            style="width:100%;padding:10px;background:#fff5f5;border:1.5px solid #fecaca;color:#dc2626;font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;transition:background .15s;"
                            onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                        Delete This Trip
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
