<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        $employees = Employee::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        $employee = Employee::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return response()->json($employee, 201);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        abort_if($employee->user_id !== auth()->id(), 403);

        $validated = $this->validated($request);

        $employee->update($validated);

        return response()->json($employee);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        abort_if($employee->user_id !== auth()->id(), 403);

        $employee->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'commission_type'   => ['required', 'string', 'in:nenhuma,percentual,fixo'],
            'commission_value'  => ['required_if:commission_type,percentual,fixo', 'nullable', 'numeric', 'min:0'],
        ]);
    }
}
