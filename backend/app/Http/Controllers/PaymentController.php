<?php

namespace App\Http\Controllers;

use App\Models\ServiceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $query = ServiceHistory::with(['vehicle.customer'])
            ->whereHas('vehicle', fn ($v) => $v->where('user_id', $userId))
            ->whereNotNull('amount')
            ->orderByDesc('service_date');

        if ($status = $request->query('status')) {
            $query->where('payment_status', $status);
        }

        return response()->json($query->get());
    }
}
