<?php

use App\Http\Controllers\CaisseController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::resource("caisse-transactions", CashTransactionController::class);

Route::get('/caisse/dashboard', [InvoiceController::class , 'dashboard']);
Route::resource('invoices', InvoiceController::class)->only(['index','store']);
