<?php

use App\Http\Controllers\CaisseController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PriceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/caisse/dashboard', [InvoiceController::class , 'dashboard']);
Route::get("/invoices/statistics" , [InvoiceController::class , 'statistics']);

Route::resource('/invoices', InvoiceController::class)->only(['index','store' , 'show']);
Route::post("/invoice/{id}/payment" , [\App\Http\Controllers\PaymentController::class , 'store']);
Route::put('/invoice/{id}/payment/versement' , [\App\Http\Controllers\PaymentController::class , 'versement']);
Route::put('/invoice/{id}/payment/debit' , [\App\Http\Controllers\PaymentController::class , 'debite']);

Route::resource('/price' , PriceController::class);









Route::get('/verify/users', [\App\Http\Controllers\CustomerController::class , 'index']);
Route::get("/invoice/unpaid", [\App\Http\Controllers\InvoiceController::class , 'unpaid_list']);


Route::resource('/payments', \App\Http\Controllers\PaymentController::class)->only(['destroy' , 'update']);
Route::get('/payment/receipt/{id}', [\App\Http\Controllers\PaymentController::class , 'sendPaymentInvoice']);
Route::put('/payment/{id}/cash_in', [\App\Http\Controllers\PaymentController::class , 'versement']);
Route::put('/payment/{id}/debit', [\App\Http\Controllers\PaymentController::class , 'debite']);


Route::resource('invoices-items' , \App\Http\Controllers\InvoiceItemController::class)->only(['show']);

Route::resource('/caisses' , \App\Http\Controllers\CaisseController::class)->only(['index']);
Route::get('/caisses/latest', [\App\Http\Controllers\CaisseController::class , 'latest']);

Route::resource('price' , \App\Http\Controllers\PriceController::class)->only(['index','store', 'update' , 'destroy']);


Route::get('/creance/{period}/general', [\App\Http\Controllers\CreanceController::class , 'creance_mensuelle']);
Route::get('/creance/commercial/{period}/general', [\App\Http\Controllers\CreanceController::class , 'creance_mensuelle_commercial']);
Route::get('/creance/commercial/{id}', [\App\Http\Controllers\CreanceController::class , 'creance-mensuelle']);


