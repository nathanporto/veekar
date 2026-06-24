<?php

namespace App\Http\Controllers;

use App\Models\ServiceHistory;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceHistoryController extends Controller
{
    public function index(Vehicle $vehicle): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $histories = $vehicle->serviceHistories()->with('items')->get();

        return response()->json($histories);
    }

    public function store(Request $request, Vehicle $vehicle): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'service_date'          => ['required', 'date'],
            'description'           => ['required', 'string'],
            'mileage'               => ['required', 'integer', 'min:0'],
            'amount'                => ['nullable', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string'],
            'items'                 => ['nullable', 'array'],
            'items.*.description'   => ['required', 'string', 'max:255'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
        ]);

        $history = DB::transaction(function () use ($validated, $vehicle) {
            $items = $validated['items'] ?? [];

            $amount = count($items) > 0
                ? collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price'])
                : ($validated['amount'] ?? null);

            $history = $vehicle->serviceHistories()->create([
                'service_date' => $validated['service_date'],
                'description'  => $validated['description'],
                'mileage'      => $validated['mileage'],
                'amount'       => $amount,
                'notes'        => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $history->items()->create($item);
            }

            if ($validated['mileage'] > $vehicle->mileage) {
                $vehicle->update(['mileage' => $validated['mileage']]);
            }

            return $history->load('items');
        });

        return response()->json($history, 201);
    }

    public function destroy(Vehicle $vehicle, ServiceHistory $serviceHistory): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);
        abort_if($serviceHistory->vehicle_id !== $vehicle->id, 404);

        $serviceHistory->delete();

        return response()->json(null, 204);
    }
}
