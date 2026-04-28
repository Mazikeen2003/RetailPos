<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show($id)
    {
        /** @var \App\Models\Sale $sale */
        $sale = Sale::with('items.product')->findOrFail($id);
        return response()->json($sale);
    }

    public function reprint(Request $request, $id)
    {
        /** @var \App\Models\Sale $sale */
        $sale = Sale::findOrFail($id);

        if ($sale->status === 'voided') {
            return response()->json([
                'message' => 'Voided receipts cannot be reprinted.',
            ], 422);
        }

        $user = $request->user();
        $sale->reprinted = true;
        $sale->save();

        AuditLog::create([
            'user_id' => optional($user)->id,
            'action' => 'Receipt Reprinted',
            'details' => "TXN-{$sale->id} reprinted",
            'logged_at' => now(),
        ]);

        return response()->json($sale->load('items.product'));
    }
}
