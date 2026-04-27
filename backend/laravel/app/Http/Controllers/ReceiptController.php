<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show($id)
    {
        $sale = Sale::with('items.product')->findOrFail($id);
        return response()->json($sale);
    }

    public function reprint(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $user = $request->user();
        $sale->reprinted = true;
        $sale->save();

        AuditLog::create([
            'action' => 'Receipt Reprinted',
            'user' => $user?->name ?? 'system',
            'user_id' => $user?->id,
            'details' => "TXN-{$sale->id} reprinted",
            'level' => 'Medium',
        ]);

        return response()->json($sale);
    }
}
