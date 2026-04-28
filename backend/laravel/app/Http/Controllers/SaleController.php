<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index()
    {
        return response()->json(
            Sale::query()
                ->with(['cashier:id,name', 'items'])
                ->latest()
                ->limit(20)
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'discountType' => ['nullable', 'string', Rule::in(['none', 'senior', 'pwd', 'athlete', 'solo'])],
            'discount' => ['nullable', 'string', Rule::in(['none', 'senior', 'pwd', 'athlete', 'solo'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $items = collect($validated['items'])->map(function ($item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $quantity = $item['quantity'] ?? $item['qty'] ?? null;

            if (! $productId || ! $quantity) {
                abort(422, 'Each sale item must include a product and quantity.');
            }

            return [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        })->values()->all();

        $discountType = $validated['discountType'] ?? $validated['discount'] ?? 'none';
        $discountRate = match ($discountType) {
            'senior', 'pwd' => 0.20,
            'athlete', 'solo' => 0.10,
            default => 0,
        };

        $sale = DB::transaction(function () use ($items, $discountType, $discountRate, $request) {
            $productIds = collect($items)->pluck('product_id');
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lineItems = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (! $product || ! $product->active) {
                    abort(422, 'One or more selected products are inactive.');
                }

                if ($product->stock < $item['quantity']) {
                    abort(422, "Insufficient stock for {$product->name}.");
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ];
            }

            $discountAmount = round($subtotal * $discountRate, 2);
            $taxableTotal = round($subtotal - $discountAmount, 2);
            $vatableSales = round($taxableTotal / 1.12, 2);
            $vatAmount = round($taxableTotal - $vatableSales, 2);

            $sale = Sale::create([
                'cashier_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_rate' => $discountRate,
                'discount_amount' => $discountAmount,
                'vatable_sales' => $vatableSales,
                'vat_amount' => $vatAmount,
                'total' => $taxableTotal,
                'status' => 'completed',
            ]);

            foreach ($lineItems as $lineItem) {
                $sale->items()->create($lineItem);
                $products[$lineItem['product_id']]->decrement('stock', $lineItem['quantity']);
            }

            return $sale->load(['cashier:id,name', 'items']);
        });

        return response()->json([
            'message' => 'Sale completed successfully',
            'sale' => $sale,
            'subtotal' => (float) $sale->subtotal,
            'discount_amount' => (float) $sale->discount_amount,
            'vatable_sales' => (float) $sale->vatable_sales,
            'vat_amount' => (float) $sale->vat_amount,
            'total' => (float) $sale->total,
        ], 201);
    }
}
