<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API working',
    ]);
});

Route::apiResource('products', ProductController::class);