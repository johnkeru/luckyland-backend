<?php

use App\Http\Controllers\Auth\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::post('login', [UserController::class, 'login']);

Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('reset-password', [UserController::class, 'resetPassword']);


Route::get('test', function () {
    // function getToyCars()
    // {
    //     yield 'Car 1';
    //     yield 'Car 2';
    //     yield 'Car 3';
    // }

    // $toyBox = getToyCars();
    // return get_class_methods(Facade::class);

    // return \Illuminate\Support\Str::mask(User::first()->email, '*', -12, 6);
    // return get_class_methods(User::class);

    return view('welcome.index', ['message' => 'message ko eheheh']);
});
