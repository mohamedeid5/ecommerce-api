<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\ProductImageController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::prefix('cart')->middleware('cart')->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::post('/items', [CartController::class, 'addItem']);
        Route::patch('/items/{item}', [CartController::class, 'updateItem']);
        Route::delete('/items/{item}', [CartController::class, 'removeItem']);
        Route::delete('/', [CartController::class, 'clear']);
    });

    Route::prefix('addresses')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/{address}', [AddressController::class, 'show']);
        Route::patch('/{address}', [AddressController::class, 'update']);
        Route::delete('/{address}', [AddressController::class, 'destroy']);
        Route::post('/{address}/set-default', [AddressController::class, 'setDefault']);
    });

    Route::prefix('orders')->middleware(['auth:sanctum', 'cart'])->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);

    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::apiResource('categories', CategoryController::class);

            // product images
            Route::post('products/{product}/images/primary', [ProductImageController::class, 'uploadPrimary']);
            Route::post('products/{product}/images/gallery', [ProductImageController::class, 'uploadGallery']);

            Route::delete('products/images/{image}', [ProductImageController::class, 'destroy']);
            Route::patch('products/{product}/images/reorder', [ProductImageController::class, 'reorder']);

            Route::get('products/trashed', [ProductController::class, 'trashed']);
            Route::post('products/{id}/restore', [ProductController::class, 'restore']);
            Route::delete('products/{id}/force-delete', [ProductController::class, 'forceDelete']);
            Route::apiResource('products', ProductController::class);
        });

    });
});
