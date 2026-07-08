<?php

namespace App\Http\Controllers;

use App\Models\PaymentReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentReminderController extends Controller
{
    public function index(): JsonResponse
    {
        $reminders = PaymentReminder::where('user_id', auth()->id())
            ->orderBy('paid')
            ->orderBy('due_date')
            ->get();

        return response()->json($reminders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount'      => ['nullable', 'numeric', 'min:0'],
            'due_date'    => ['required', 'date'],
        ]);

        $reminder = PaymentReminder::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return response()->json($reminder, 201);
    }

    public function update(Request $request, PaymentReminder $paymentReminder): JsonResponse
    {
        abort_if($paymentReminder->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'amount'      => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'due_date'    => ['sometimes', 'required', 'date'],
            'paid'        => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('paid', $validated)) {
            $validated['paid_at'] = $validated['paid'] ? now() : null;
        }

        $paymentReminder->update($validated);

        return response()->json($paymentReminder);
    }

    public function destroy(PaymentReminder $paymentReminder): JsonResponse
    {
        abort_if($paymentReminder->user_id !== auth()->id(), 403);

        $paymentReminder->delete();

        return response()->json(null, 204);
    }
}
