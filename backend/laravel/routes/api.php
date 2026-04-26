<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SaleController;

Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working!'
    ]);
});

Route::get('/sales', [SaleController::class, 'index']);
Route::post('/sales/checkout', [SaleController::class, 'store']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});