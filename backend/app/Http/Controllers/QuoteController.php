<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function index(): JsonResponse
    {
        $quotes = Quote::with(['customer', 'vehicle', 'items'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($q) => $this->formatQuote($q));

        return response()->json($quotes);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id'          => 'nullable|integer',
            'vehicle_id'           => 'nullable|integer',
            'notes'                => 'nullable|string|max:1000',
            'expires_at'           => 'nullable|date',
            'items'                => 'required|array|min:1',
            'items.*.description'  => 'required|string|max:255',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        if (! empty($data['customer_id'])) {
            Customer::where('id', $data['customer_id'])->where('user_id', auth()->id())->firstOrFail();
        }

        if (! empty($data['vehicle_id'])) {
            Vehicle::where('id', $data['vehicle_id'])->where('user_id', auth()->id())->firstOrFail();
        }

        $quote = Quote::create([
            'user_id'     => auth()->id(),
            'customer_id' => $data['customer_id'] ?? null,
            'vehicle_id'  => $data['vehicle_id'] ?? null,
            'token'       => Str::random(32),
            'status'      => 'pending',
            'notes'       => $data['notes'] ?? null,
            'expires_at'  => $data['expires_at'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            QuoteItem::create([
                'quote_id'    => $quote->id,
                'description' => $item['description'],
                'quantity'    => (int) $item['quantity'],
                'unit_price'  => (float) $item['unit_price'],
            ]);
        }

        return response()->json($this->formatQuote($quote->load(['items', 'customer', 'vehicle'])), 201);
    }

    public function show(Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        return response()->json($this->formatQuote($quote->load(['items', 'customer', 'vehicle'])));
    }

    public function destroy(Quote $quote): JsonResponse
    {
        if ($quote->user_id !== auth()->id()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $quote->delete();

        return response()->json(null, 204);
    }

    public function publicShow(string $token): JsonResponse
    {
        $quote = Quote::with(['items', 'customer', 'vehicle', 'user'])
            ->where('token', $token)
            ->firstOrFail();

        return response()->json([
            'token'      => $quote->token,
            'status'     => $quote->status,
            'notes'      => $quote->notes,
            'expires_at' => $quote->expires_at,
            'mechanic'   => [
                'name'         => $quote->user->name,
                'company_name' => $quote->user->company_name ?? null,
            ],
            'customer' => $quote->customer ? ['name' => $quote->customer->name] : null,
            'vehicle'  => $quote->vehicle ? [
                'plate' => $quote->vehicle->plate,
                'brand' => $quote->vehicle->brand,
                'model' => $quote->vehicle->model,
                'year'  => $quote->vehicle->year,
            ] : null,
            'items' => $quote->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit_price'  => (float) $item->unit_price,
                'subtotal'    => $item->quantity * $item->unit_price,
            ]),
            'total' => $quote->items->sum(fn ($item) => $item->quantity * $item->unit_price),
        ]);
    }

    public function respond(string $token, Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => 'required|in:approved,rejected',
        ]);

        $quote = Quote::where('token', $token)->firstOrFail();

        if ($quote->status !== 'pending') {
            return response()->json(['message' => 'Este orçamento já foi respondido.'], 409);
        }

        $quote->update(['status' => $data['action']]);

        return response()->json(['status' => $quote->status]);
    }

    private function formatQuote(Quote $quote): array
    {
        $items = $quote->items ?? collect();
        $total = $items->sum(fn ($i) => $i->quantity * $i->unit_price);

        return [
            'id'         => $quote->id,
            'token'      => $quote->token,
            'status'     => $quote->status,
            'notes'      => $quote->notes,
            'expires_at' => $quote->expires_at,
            'total'      => (float) $total,
            'customer'   => $quote->customer ? ['id' => $quote->customer->id, 'name' => $quote->customer->name] : null,
            'vehicle'    => $quote->vehicle ? [
                'id'    => $quote->vehicle->id,
                'plate' => $quote->vehicle->plate,
                'brand' => $quote->vehicle->brand,
                'model' => $quote->vehicle->model,
            ] : null,
            'items'      => $items->map(fn ($i) => [
                'id'          => $i->id,
                'description' => $i->description,
                'quantity'    => $i->quantity,
                'unit_price'  => (float) $i->unit_price,
                'subtotal'    => (float) ($i->quantity * $i->unit_price),
            ]),
            'created_at' => $quote->created_at,
        ];
    }
}
