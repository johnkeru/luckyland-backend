<?php

use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;


Route::get('hi', function () {
    return response()->json([
        'data' => 'aehwfawef',
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'user']);
    Route::post('logout', [UserController::class, 'logout']);
});
