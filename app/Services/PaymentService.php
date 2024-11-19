<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use PharIo\Version\Exception;

class PaymentService
{
    public ?string $id = null;
    private ?Payment $payment = null;

    public function __construct(string|null $payment_id)
    {
        if ($payment_id) {
            $this->id = $payment_id;
            $this->payment = Payment::query()->find($payment_id);
        }
    }

    public function makePayment(array $paymentData, $invoice_id)
    {
        try {
            $pay = new Payment($paymentData);
            $invoice = Invoice::find($invoice_id);
            $pay->invoice()->associate($invoice);
            $pay->save();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new \Exception("Error saving payment");
        }
    }

    public function makeVersement()
    {
        try {
            $this->payment->cash_in = true;
            $this->payment->save();
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            throw new \Exception("Error saving payment");
        }
    }
}
