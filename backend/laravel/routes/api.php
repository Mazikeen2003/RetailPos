<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working!',
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/barcode/{barcode}', [ProductController::class, 'barcode']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role');
    });

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);

    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/sales', [SaleController::class, 'store']);

    Route::get('/receipts/{sale}', [ReceiptController::class, 'show']);
    Route::post('/receipts/{sale}/reprint', [ReceiptController::class, 'reprint']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::post('/audit-logs', [AuditLogController::class, 'store']);

    Route::get('/users/meta', [UserController::class, 'meta']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
});
