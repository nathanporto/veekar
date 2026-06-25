<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $query = Vehicle::with('customer')->where('user_id', $userId);

        if ($customerId = $request->query('customer_id')) {
            $query->where('customer_id', (int) $customerId);
        }

        if ($search = $request->query('search')) {
            $term = strtoupper($search);
            $query->where(function ($q) use ($search, $term) {
                $q->where('plate', 'ilike', "%{$term}%")
                  ->orWhere('brand', 'ilike', "%{$search}%")
                  ->orWhere('model', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'ilike', "%{$search}%"));
            });
        }

        $vehicles = $query->orderBy('plate')->paginate(20);

        return response()->json($vehicles);
    }

    public function search(Request $request): JsonResponse
    {
        $plate = strtoupper(preg_replace('/\s+/', '', $request->query('plate', '')));

        $vehicle = Vehicle::with('customer')
            ->where('user_id', auth()->id())
            ->where('plate', $plate)
            ->first();

        if (! $vehicle) {
            return response()->json(['message' => 'Veículo não encontrado'], 404);
        }

        return response()->json($vehicle);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $subscription = $request->attributes->get('subscription');

        if ($subscription?->isTrial() && Vehicle::where('user_id', $userId)->count() >= 3) {
            return response()->json([
                'message' => 'Limite do período gratuito atingido (3 veículos). Assine o Veekar para continuar.',
                'code'    => 'trial_limit_reached',
            ], 403);
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('user_id', $userId)],
            'brand'       => ['required', 'string', 'max:50'],
            'model'       => ['required', 'string', 'max:100'],
            'year'        => ['required', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'color'       => ['required', 'string', 'max:50'],
            'plate'       => ['required', 'string', 'max:10', Rule::unique('vehicles', 'plate')->where('user_id', $userId)],
            'mileage'     => ['required', 'integer', 'min:0'],
        ]);

        $validated['plate'] = strtoupper(preg_replace('/\s+/', '', $validated['plate']));
        $validated['user_id'] = $userId;

        $vehicle = Vehicle::create($validated);
        $vehicle->load('customer');

        return response()->json($vehicle, 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $vehicle->load('customer');

        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $userId = auth()->id();
        abort_if($vehicle->user_id !== $userId, 403);

        $validated = $request->validate([
            'customer_id' => ['sometimes', 'integer', Rule::exists('customers', 'id')->where('user_id', $userId)],
            'brand'       => ['sometimes', 'required', 'string', 'max:50'],
            'model'       => ['sometimes', 'required', 'string', 'max:100'],
            'year'        => ['sometimes', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'color'       => ['sometimes', 'required', 'string', 'max:50'],
            'plate'       => ['sometimes', 'required', 'string', 'max:10', Rule::unique('vehicles', 'plate')->where('user_id', $userId)->ignore($vehicle->id)],
            'mileage'     => ['sometimes', 'integer', 'min:0'],
        ]);

        if (isset($validated['plate'])) {
            $validated['plate'] = strtoupper(preg_replace('/\s+/', '', $validated['plate']));
        }

        $vehicle->update($validated);
        $vehicle->load('customer');

        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $vehicle->delete();

        return response()->json(null, 204);
    }
}
