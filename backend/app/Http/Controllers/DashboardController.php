<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentReminder;
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

    public function kanban(): JsonResponse
    {
        $userId = auth()->id();

        $services = ServiceHistory::with(['vehicle.customer'])
            ->whereHas('vehicle', fn ($q) => $q->where('user_id', $userId))
            ->where('service_status', '!=', 'entregue')
            ->orderByDesc('service_date')
            ->get();

        return response()->json($services);
    }

    public function agenda(): JsonResponse
    {
        $userId = auth()->id();

        $deliveries = ServiceHistory::with(['vehicle.customer'])
            ->whereHas('vehicle', fn ($q) => $q->where('user_id', $userId))
            ->whereNotNull('estimated_delivery')
            ->where('estimated_delivery', '>=', now()->subDays(1)->toDateString())
            ->orderBy('estimated_delivery')
            ->limit(50)
            ->get()
            ->map(fn ($h) => array_merge($h->toArray(), ['_type' => 'delivery']));

        $returns = ServiceHistory::with(['vehicle.customer'])
            ->whereHas('vehicle', fn ($q) => $q->where('user_id', $userId))
            ->whereNotNull('return_date')
            ->where('return_date', '>=', now()->subDays(1)->toDateString())
            ->orderBy('return_date')
            ->limit(50)
            ->get()
            ->map(fn ($h) => array_merge($h->toArray(), ['_type' => 'return']));

        return response()->json([
            'deliveries' => $deliveries,
            'returns'    => $returns,
        ]);
    }

    public function upcomingReturns(): JsonResponse
    {
        $userId = auth()->id();

        $returns = ServiceHistory::with(['vehicle.customer'])
            ->whereHas('vehicle', fn ($q) => $q->where('user_id', $userId))
            ->whereNotNull('return_date')
            ->whereBetween('return_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('return_date')
            ->limit(10)
            ->get();

        return response()->json($returns);
    }

    public function upcomingPaymentReminders(): JsonResponse
    {
        $userId = auth()->id();

        $reminders = PaymentReminder::where('user_id', $userId)
            ->where('paid', false)
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return response()->json($reminders);
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
