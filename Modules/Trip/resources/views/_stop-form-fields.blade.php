{{-- Shared form fields for Add / Edit Stop modal --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Place Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" x-model="form.name" required maxlength="255"
           placeholder="e.g. Valley Viewpoint, Hotel Paradise"
           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
    <select name="place_type" x-model="form.place_type" required
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <option value="hotel">🏨 Hotel / Stay</option>
        <option value="attraction">🏔️ Attraction</option>
        <option value="restaurant">🍽️ Food / Dining</option>
        <option value="activity">🎯 Activity</option>
        <option value="transit">🚌 Transit</option>
        <option value="other">📍 Other</option>
    </select>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="visit_date" x-model="form.visit_date"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
        <input type="time" name="visit_time" x-model="form.visit_time"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Address / Location</label>
    <input type="text" name="address" x-model="form.address" maxlength="500"
           placeholder="e.g. Near Chakrata Market, Uttarakhand"
           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
    <textarea name="notes" x-model="form.notes" rows="3" maxlength="1000"
              placeholder="Highlights, tips, what happened here…"
              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Cost (₹)</label>
        <input type="number" name="estimated_cost" x-model="form.estimated_cost"
               step="0.01" min="0" max="9999999.99"
               placeholder="0.00"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Link to Expense</label>
        <select name="expense_id" x-model="form.expense_id"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">— None —</option>
            @foreach($trip->expenses->where('approval_status','approved') as $exp)
            <option value="{{ $exp->id }}">{{ Str::limit($exp->title, 30) }} (₹{{ number_format($exp->amount,2) }})</option>
            @endforeach
        </select>
    </div>
</div>

<div class="flex gap-3 pt-2">
    <button type="submit"
            class="flex-1 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors"
            x-text="editMode ? 'Save Changes' : 'Add Stop'">
    </button>
    <button type="button" @click="stopModal=false"
            class="flex-1 py-2.5 border border-gray-200 text-sm text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
        Cancel
    </button>
</div>
