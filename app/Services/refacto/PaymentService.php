<?php

namespace App\Services\refacto;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class PaymentService
{
    public $newPayment = [
        'amount'=>0,
        'user_id'=>null,
        'type'=>1,
        'comment'=>null,
        'cash_in'=>false,
        'deleted'=>true,
        'reliquat'=>0,
        'comment'=>null,
        'discount'=>0,
        "cash_in_date"=> null,
    ];

    public ?string $id = null;

    public function __construct(?string $id)
    {
        $this->id = $id;
    }

    public function setAmount(int $amount)
    {
        $this->newPayment['amount'] = $amount;
    }

    public function setUser(string $id)
    {
        $this->newPayment['user_id'] = $id;
    }
    public function setReliquat(int $reliquat)
    {
        $this->newPayment['reliquat'] = $reliquat;
    }
    public function setType(int $type)
    {
        $this->newPayment['type'] = $type;
    }

    public function setComment(?string $comment=null)
    {
        $this->newPayment['comment'] = $comment ;
    }

    public function getPayment(){
        return Payment::query()->where('id',$this->id)->first();
    }
    public function getPaymentById($id){
        return Payment::query()->where('id',$id)->first();
    }
    public function getPaymentsByInvoice($id){
        return Payment::query()->where('invoice_id',$id)->get();
    }
    public function getNewPay(){

        return $this->newPayment;
    }

    public static function CASH_IN($id  , string $date){
            Payment::query()->where('id',$id)->update(['cash_in'=>true,'cash_in_date'=>Carbon::parse($date)]);
    }   
   
    public static function UN_CASH_IN($id){
        Payment::query()->where('id',$id)->update(['cash_in'=>false]);
    }

    public static function DELETE_PAYMENT($id){
        Payment::query()->where('id',$id)->update(['deleted'=>true]);
    }

    public static function RESTORE_PAYMENT($id){
        Payment::query()->where('id',$id)->update(['deleted'=>false]);
    }

}

