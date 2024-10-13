<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::post('/login', [AuthController::class, 'login']);

Route::group(
    [
        'middleware' => ['throttle:api', 'auth:sanctum'],
        'prefix' => 'v1',
        'as' => 'api',
    ],
    function () {

        // --- Orders --- //
        Route::get('/order/user/{user}', [OrderController::class, 'getOrdersByUser']);//->middleware('throttle:60,1');
        Route::get('/order/{order}', [OrderController::class, 'show']);//->middleware('throttle:60,1');
        Route::post('/order', [OrderController::class, 'store']);//->middleware('throttle:10,1');
        Route::put('/order/{order}', [OrderController::class, 'update']);//->middleware('throttle:30,1');
        Route::delete('/order/{order}', [OrderController::class, 'destroy']);//->middleware('throttle:30,1');
    }
);