<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\UserApiController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user', [UserController::class, 'index']);

Route::get('/api/user', [UserApiController::class, 'index']);
