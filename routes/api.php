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
Route::get("/invoices/statistics" , [InvoiceController::class , 'statistics']);
Route::resource('invoices', InvoiceController::class)->only(['index','store' , 'show']);

Route::get('/verify/users', [\App\Http\Controllers\CustomerController::class , 'index']);
Route::get("/invoice/unpaid", [\App\Http\Controllers\InvoiceController::class , 'unpaid_list']);
Route::put("/invoice/rembourssement/{id}" , [\App\Http\Controllers\InvoiceController::class , 'rembourssement']);

Route::put("payment/versement/{id}" , [\App\Http\Controllers\PaymentController::class , 'versement']);
Route::resource('/payments', \App\Http\Controllers\PaymentController::class)->only(['destroy']);
Route::get('/payment/receipt/{id}', [\App\Http\Controllers\PaymentController::class , 'sendPaymentInvoice']);
