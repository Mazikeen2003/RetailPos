<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'discount' => 'nullable|string',
        ]);

        $user = $request->user();
        $items = $request->input('items');

        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += ($it['price'] ?? 0) * ($it['qty'] ?? 1);
        }

        $discount = $request->input('discount', 'none');
        $discountRate = in_array($discount, ['senior','pwd']) ? 0.2 : (in_array($discount, ['athlete','solo']) ? 0.1 : 0);
        $discountAmount = $subtotal * $discountRate;
        $total = $subtotal - $discountAmount;

        $sale = null;

        DB::transaction(function () use ($user, $items, $subtotal, $discount, $discountAmount, $total, &$sale) {
            $sale = Sale::create([
                'user_id' => $user?->id,
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
                    'product_id' => $product?->id,
                    'qty' => $qty,
                    'price' => $it['price'] ?? ($product?->price ?? 0),
                ]);

                if ($product) {
                    $product->stock = max(0, $product->stock - $qty);
                    $product->save();
                }
            }

            AuditLog::create([
                'action' => 'Sale Completed',
                'user' => $user?->name ?? 'system',
                'user_id' => $user?->id,
                'details' => "TXN-{$sale->id} completed",
                'level' => 'High',
            ]);
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
            'user' => $user?->name ?? 'system',
            'user_id' => $user?->id,
            'details' => $request->input('reason'),
            'level' => 'Medium',
        ]);

        return response()->json(['message' => 'Sale cancellation recorded']);
    }

    public function voidItem(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'sale_item_id' => 'required|exists:sale_items,id',
        ]);

        $user = $request->user();

        $saleItem = SaleItem::findOrFail($request->sale_item_id);
        if ($saleItem->voided) {
            return response()->json(['message' => 'Item already voided']);
        }

        DB::transaction(function () use ($saleItem, $user) {
            $saleItem->voided = true;
            $saleItem->save();

            if ($saleItem->product) {
                $saleItem->product->stock += $saleItem->qty;
                $saleItem->product->save();
            }

            AuditLog::create([
                'action' => 'Item Voided',
                'user' => $user?->name ?? 'system',
                'user_id' => $user?->id,
                'details' => "Voided item {$saleItem->id} from sale {$saleItem->sale_id}",
                'level' => 'Medium',
            ]);
        });

        return response()->json(['message' => 'Item voided']);
    }

    public function postVoid(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'required|string',
        ]);

        $user = $request->user();
        if (! in_array($user?->role, ['Supervisor', 'Administrator'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sale = Sale::with('items')->findOrFail($request->sale_id);
        if ($sale->canceled) {
            return response()->json(['message' => 'Sale already canceled'], 400);
        }

        DB::transaction(function () use ($sale, $request, $user) {
            foreach ($sale->items as $it) {
                if (! $it->voided && $it->product) {
                    $it->product->stock += $it->qty;
                    $it->product->save();
                }
            }

            $sale->canceled = true;
            $sale->canceled_reason = $request->reason;
            $sale->save();

            AuditLog::create([
                'action' => 'Post-void Approved',
                'user' => $user?->name ?? 'system',
                'user_id' => $user?->id,
                'details' => "TXN-{$sale->id} reversed. Reason: {$request->reason}",
                'level' => 'High',
            ]);
        });

        return response()->json(['message' => 'Post-void completed']);
    }
}
