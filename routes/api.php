<?php

use App\Http\Controllers\CaisseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/caisse/dashboard', [CaisseController::class , 'dashboard']);
Route::resource('caisse', CaisseController::class);