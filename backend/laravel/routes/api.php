<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\AuditLogController;

Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API working'
    ]);
});

// Authentication
Route::post('/login', [AuthController::class, 'login']);

// Public product listing
Route::get('/products', [ProductController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Sales & Void/Cancel/Post-void
    Route::post('/sales', [SaleController::class, 'store']);
    Route::post('/sales/cancel', [SaleController::class, 'cancel']);
    Route::post('/sales/void-item', [SaleController::class, 'voidItem']);
    Route::post('/sales/post-void', [SaleController::class, 'postVoid']);

    // Receipts
    Route::get('/receipts/{id}', [ReceiptController::class, 'show']);
    Route::post('/receipts/{id}/reprint', [ReceiptController::class, 'reprint']);

    // Audit logs
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
});