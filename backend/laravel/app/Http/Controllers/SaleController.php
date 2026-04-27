<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\VoidSaleItemRequest;
use App\Http\Requests\PostVoidRequest;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function store(StoreSaleRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $items = $validated['items'];

        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += ($it['price'] ?? 0) * ($it['qty'] ?? 1);
        }

        $discount = $validated['discount'] ?? 'none';
        $discountRate = in_array($discount, ['senior','pwd']) ? 0.2 : (in_array($discount, ['athlete','solo']) ? 0.1 : 0);
        $discountAmount = $subtotal * $discountRate;
        $total = $subtotal - $discountAmount;

        $sale = DB::transaction(function () use ($user, $items, $subtotal, $discount, $discountAmount, $total) {
            $sale = Sale::create([
                'user_id' => optional($user)->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_amount' => $discountAmount,
                'total' => $total,
            ]);

            foreach ($items as $it) {
                $product = Product::find($it['id'] ?? $it['product_id']);
                $qty = intval($it['qty'] ?? 1);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => optional($product)->id,
                    'qty' => $qty,
                            'price' => $it['price'] ?? (optional($product)->price ?? 0),
                ]);

                if ($product) {
                    $product->stock = max(0, $product->stock - $qty);
                    $product->save();
                }
            }

            AuditLog::create([
                'action' => 'Sale Completed',
                'user' => optional($user)->name ?? 'system',
                'user_id' => optional($user)->id,
                'details' => "TXN-{$sale->id} completed",
                'level' => 'High',
            ]);

            return $sale;
        });

        // return sale with items
        $sale->load('items.product');

        return response()->json($sale, 201);
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $user = $request->user();

        AuditLog::create([
            'action' => 'Sale Cancelled',
            'user' => optional($user)->name ?? 'system',
            'user_id' => optional($user)->id,
            'details' => $request->input('reason'),
            'level' => 'Medium',
        ]);

        return response()->json(['message' => 'Sale cancellation recorded']);
    }

    public function voidItem(VoidSaleItemRequest $request)
    {
        $user = $request->user();

        $saleItem = SaleItem::findOrFail($request->validated()['sale_item_id']);
        /** @var \App\Models\SaleItem $saleItem */
        if ($saleItem->isVoided()) {
            return response()->json(['message' => 'Item already voided']);
        }

        DB::transaction(function () use ($saleItem, $user) {
            $saleItem->markVoided();

            if ($saleItem->product) {
                $saleItem->product->stock += $saleItem->getQty();
                $saleItem->product->save();
            }

            AuditLog::create([
                'action' => 'Item Voided',
                'user' => optional($user)->name ?? 'system',
                'user_id' => optional($user)->id,
                'details' => "Voided item {$saleItem->id} from sale {$saleItem->sale_id}",
                'level' => 'Medium',
            ]);
        });

        return response()->json(['message' => 'Item voided']);
    }

    public function postVoid(PostVoidRequest $request)
    {
        $user = $request->user();
        if (! in_array(optional($user)->role, ['Supervisor', 'Administrator'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        /** @var \App\Models\Sale $sale */
        $sale = Sale::with('items')->findOrFail($request->validated()['sale_id']);
        if ($sale->canceled) {
            return response()->json(['message' => 'Sale already canceled'], 400);
        }

        DB::transaction(function () use ($sale, $request, $user) {
            /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\SaleItem[] $saleItems */
            $saleItems = $sale->items;

            foreach ($saleItems as $it) {
                /** @var \App\Models\SaleItem $it */
                if (! $it->isVoided() && $it->product) {
                    $it->product->stock += $it->getQty();
                    $it->product->save();
                }
            }

            $sale->canceled = true;
            $sale->canceled_reason = $request->validated()['reason'];
            $sale->save();

            AuditLog::create([
                'action' => 'Post-void Approved',
                'user' => optional($user)->name ?? 'system',
                'user_id' => optional($user)->id,
                'details' => "TXN-{$sale->id} reversed. Reason: {$request->validated()['reason']}",
                'level' => 'High',
            ]);
        });

        return response()->json(['message' => 'Post-void completed']);
    }
}
