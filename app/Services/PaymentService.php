<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function makePayment(array $paymentData, $invoice_id)
    {
        try {
            $pay = new Payment($paymentData);
            $invoice = Invoice::find($invoice_id);
            $pay->invoice()->associate($invoice);
            $pay->save();
        }catch (\Exception $exception){
            Log::error($exception->getMessage());
            throw new \Exception("Error saving payment");
        }
   }
}
