<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'unit_cost'  => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $product = Product::create([
            ...$validated,
            'user_id'  => auth()->id(),
            'quantity' => 0,
        ]);

        return response()->json($product, 201);
    }

    public function destroy(Product $product): JsonResponse
    {
        abort_if($product->user_id !== auth()->id(), 403);

        $product->delete();

        return response()->json(null, 204);
    }

    public function registerEntrada(Request $request, Product $product): JsonResponse
    {
        abort_if($product->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'quantity'      => ['required', 'integer', 'min:1'],
            'unit_cost'     => ['required', 'numeric', 'min:0'],
            'movement_date' => ['required', 'date'],
            'notes'         => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $product) {
            $product->movements()->create([
                'type'          => 'entrada',
                'quantity'      => $validated['quantity'],
                'unit_cost'     => $validated['unit_cost'],
                'movement_date' => $validated['movement_date'],
                'notes'         => $validated['notes'] ?? null,
            ]);

            $product->update([
                'quantity'  => $product->quantity + $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
            ]);
        });

        return response()->json($product->refresh());
    }

    public function registerSaida(Request $request, Product $product): JsonResponse
    {
        abort_if($product->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'quantity'            => ['required', 'integer', 'min:1', 'max:' . $product->quantity],
            'unit_price'          => ['nullable', 'numeric', 'min:0'],
            'movement_date'       => ['required', 'date'],
            'service_history_id'  => ['nullable', 'integer', 'exists:service_histories,id'],
            'notes'               => ['nullable', 'string', 'max:255'],
        ], [
            'quantity.max' => 'Quantidade indisponível em estoque.',
        ]);

        DB::transaction(function () use ($validated, $product) {
            $product->movements()->create([
                'type'                => 'saida',
                'quantity'            => $validated['quantity'],
                'unit_price'          => $validated['unit_price'] ?? $product->unit_price,
                'movement_date'       => $validated['movement_date'],
                'service_history_id'  => $validated['service_history_id'] ?? null,
                'notes'               => $validated['notes'] ?? null,
            ]);

            $product->update(['quantity' => $product->quantity - $validated['quantity']]);
        });

        return response()->json($product->refresh());
    }
}
