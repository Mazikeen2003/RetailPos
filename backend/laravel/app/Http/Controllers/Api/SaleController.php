<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller {
    
    
    public function index() {
        // Kukunin lahat ng sales kasama ang items nito, pinakabago ang una
        $sales = Sale::with('items')->orderBy('created_at', 'desc')->get();
        return response()->json($sales, 200);
    }

    
    public function store(Request $request) {
        // Validation (Optional pero safe)
        if (!$request->items || count($request->items) == 0) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        return DB::transaction(function () use ($request) {
            // 1. Calculate Subtotal
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            // 2. Apply Discount (Core Task - ONLY ONE per sale)
            $discountAmount = 0;
            $type = $request->discount_type;

            if ($type === 'Senior' || $type === 'PWD') {
                $discountAmount = $subtotal * 0.20; // 20%
            } elseif ($type === 'Solo Parent' || $type === 'Athlete') {
                $discountAmount = $subtotal * 0.10; // 10%
            }

            $totalAmount = $subtotal - $discountAmount;

            // 3. Create Sale Record
            $sale = Sale::create([
                'transaction_number' => 'TXN-' . strtoupper(uniqid()),
                'user_id' => $request->user_id ?? 1,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $type,
                'total_amount' => $totalAmount,
                'cash_received' => $request->cash_received,
                'change_amount' => $request->cash_received - $totalAmount,
            ]);

            // 4. Save Items and Deduct Inventory (Auto-deduct)
            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

            
                DB::table('products')->where('id', $item['id'])->decrement('stock', $item['quantity']);
            }

            return response()->json([
                'message' => 'Sale Completed Successfully!',
                'transaction_no' => $sale->transaction_number,
                'data' => $sale->load('items') // I-return pati yung items list
            ], 201);
        });
    }
}