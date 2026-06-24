<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceHistory;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $userId = auth()->id();

        return response()->json([
            'customers' => Customer::where('user_id', $userId)->count(),
            'vehicles'  => Vehicle::where('user_id', $userId)->count(),
            'services'  => ServiceHistory::whereHas('vehicle', fn ($q) => $q->where('user_id', $userId))->count(),
        ]);
    }

    public function recentServices(): JsonResponse
    {
        $userId = auth()->id();

        $services = ServiceHistory::with(['vehicle.customer'])
            ->whereHas('vehicle', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json($services);
    }
}
