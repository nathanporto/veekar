<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ServiceHistory;
use Illuminate\Http\JsonResponse;

class CommissionController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = auth()->id();
        $now    = now();

        $employees = Employee::where('user_id', $userId)->orderBy('name')->get();

        $data = $employees->map(function (Employee $employee) use ($now) {
            $services = ServiceHistory::where('employee_id', $employee->id)
                ->where('payment_status', 'pago')
                ->whereMonth('service_date', $now->month)
                ->whereYear('service_date', $now->year)
                ->orderByDesc('service_date')
                ->get(['id', 'vehicle_id', 'description', 'service_date', 'amount', 'commission_amount']);

            return [
                'employee_id'      => $employee->id,
                'employee_name'    => $employee->name,
                'commission_type'  => $employee->commission_type,
                'commission_value' => $employee->commission_value,
                'total_commission' => (float) $services->sum('commission_amount'),
                'services'         => $services,
            ];
        });

        return response()->json($data);
    }
}
