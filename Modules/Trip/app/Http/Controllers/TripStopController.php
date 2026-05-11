<?php

namespace Modules\Trip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Trip\Models\Trip;
use Modules\Trip\Models\TripStop;

class TripStopController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'place_type'     => 'required|in:hotel,attraction,restaurant,activity,transit,other',
            'visit_date'     => 'nullable|date',
            'visit_time'     => 'nullable|date_format:H:i',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0|max:9999999.99',
            'expense_id'     => 'nullable|exists:expenses,id',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        // Default sort_order to end of list for that date
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = $trip->stops()
                ->when($validated['visit_date'] ?? null, fn ($q, $d) => $q->whereDate('visit_date', $d))
                ->max('sort_order') + 1;
        }

        $stop = $trip->stops()->create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'stop' => $stop]);
        }

        return redirect()->route('trips.show', [$trip, 'tab' => 'itinerary'])
            ->with('success', "Stop \"{$stop->name}\" added.");
    }

    public function update(Request $request, Trip $trip, TripStop $stop)
    {
        $this->authorize('update', $trip);
        abort_unless($stop->trip_id === $trip->id, 403);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'place_type'     => 'required|in:hotel,attraction,restaurant,activity,transit,other',
            'visit_date'     => 'nullable|date',
            'visit_time'     => 'nullable|date_format:H:i',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0|max:9999999.99',
            'expense_id'     => 'nullable|exists:expenses,id',
        ]);

        $stop->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'stop' => $stop->fresh()]);
        }

        return redirect()->route('trips.show', [$trip, 'tab' => 'itinerary'])
            ->with('success', "Stop \"{$stop->name}\" updated.");
    }

    public function destroy(Trip $trip, TripStop $stop)
    {
        $this->authorize('update', $trip);
        abort_unless($stop->trip_id === $trip->id, 403);

        $name = $stop->name;
        $stop->delete();

        return redirect()->route('trips.show', [$trip, 'tab' => 'itinerary'])
            ->with('success', "Stop \"{$name}\" removed.");
    }
}
