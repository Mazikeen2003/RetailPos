<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostVoidRequest;
use App\Models\AuditLog;
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
                ->with(['cashier:id,name', 'items', 'voidedBy:id,name'])
                ->latest()
                ->limit(20)
                ->get()
        );
    }

    public function store(Request $request)
    {
        $this->ensureCashier($request);

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

    public function postVoid(PostVoidRequest $request)
    {
        $this->ensureSupervisorAccess($request);

        $validated = $request->validated();
        $sale = Sale::findOrFail($validated['sale_id']);

        return $this->voidSale((int) $sale->id, $request, $validated['reason']);
    }

    public function voidReceipt(Request $request, string $sale)
    {
        $this->ensureSupervisorAccess($request);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        return $this->voidSale((int) $sale, $request, $validated['reason']);
    }

    private function ensureCashier(Request $request): void
    {
        abort_unless(optional($request->user()->role)->name === 'Cashier', 403, 'Cashier access required.');
    }

    private function ensureSupervisorAccess(Request $request): void
    {
        abort_unless(
            in_array(optional($request->user()->role)->name, ['Admin', 'Supervisor'], true),
            403,
            'Supervisor or admin access required.'
        );
    }

    private function voidSale(int|Sale $sale, Request $request, string $reason)
    {
        $voidedSale = DB::transaction(function () use ($sale, $request, $reason) {
            $saleId = $sale instanceof Sale ? $sale->id : $sale;

            $lockedSale = Sale::query()
                ->with('items')
                ->whereKey($saleId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSale->status === 'voided') {
                abort(422, 'This receipt has already been voided.');
            }

            if ($lockedSale->status !== 'completed') {
                abort(422, 'Only completed receipts can be voided.');
            }

            foreach ($lockedSale->items as $item) {
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            $lockedSale->forceFill([
                'status' => 'voided',
                'void_reason' => $reason,
                'voided_by_id' => $request->user()->id,
                'voided_at' => now(),
            ])->save();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'receipt_voided',
                'details' => "Sale #{$lockedSale->id} was voided. Reason: {$reason}",
                'logged_at' => now(),
            ]);

            return $lockedSale->load(['cashier:id,name', 'items.product', 'voidedBy:id,name']);
        });

        return response()->json([
            'message' => "Sale #{$voidedSale->id} receipt voided successfully.",
            'sale' => $voidedSale,
        ]);
    }
}
