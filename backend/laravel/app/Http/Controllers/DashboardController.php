<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

class DashboardController extends Controller
{
    public function summary()
    {
        $completedSales = Sale::query()->where('status', 'completed');
        $lowStockProducts = Product::query()
            ->where('active', true)
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->get(['id', 'name', 'barcode', 'stock']);

        return response()->json([
            'metrics' => [
                'totalSales' => (float) $completedSales->sum('total'),
                'transactions' => $completedSales->count(),
                'activeUsers' => User::query()->where('is_active', true)->count(),
                'lowStockAlerts' => $lowStockProducts->count(),
            ],
            'lowStockProducts' => $lowStockProducts,
            'recentTransactions' => Sale::query()
                ->with(['cashier:id,name', 'items'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
