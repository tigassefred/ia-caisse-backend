<?php

namespace App\Services\refacto;

use App\Models\Commercial;
use App\Models\Invoice;


class InvoiceServices
{
    public ?Invoice $invoice = null;
    public ?string $id = null;
    private $newInvoice = [
          'name'=>'',
          'amount'=>0,
          'discount'=>0,
          'is_10Yaar'=>false,
          'commercial_id'=>null,
          'price_id'=>null,
          'caisse_id'=>null,
          'is_sold'=>false,
          'customer_id'=>null,
          'deleted'=>true,
    ];

    public function __construct(?string $id)
    {
        if ($id != null) {
            $this->id = $id;
            $this->populateInvoice($id);
        }
    }

    private function populateInvoice(string $id)
    {
        $this->invoice = Invoice::query()->where('id', $id)->firstOrFail();
    }

    public function setNewInvoice(string $name, int $amount, int $discount, string $caisse ,?bool $zone):void
    {
         $this->newInvoice['name'] = strtoupper($name);
         $this->newInvoice['amount'] = $amount;
         $this->newInvoice['discount'] = $discount;
         $this->newInvoice['is_10Yaar'] = $zone;
         $this->newInvoice['caisse_id'] = $caisse;
    }

    public function setCommercial(string $id)
    {
        $count_commercial = Commercial::query()->where('id', $id)->count();
        if ($count_commercial > 0) {
            $this->newInvoice['commercial_id'] = $id;
        } else {
            throw new \Exception("Le commercial est introuvable");
        }

    }

    public function setPrice(string $id)
    {
        $this->newInvoice['price_id'] = $id;
    }

    public function createInvoice(){
        $this->invoice = Invoice::create($this->newInvoice);
    }

    public function getInvoice(){
        return $this->invoice;
    }

    public function getPaiements(){
        return $this->newInvoice;
    }

    public static function SOLDED($id)
    {
        Invoice::query()->where('id', $id)->update(['is_sold' => true]);
    }
    public static function UNSOLDED($id)
    {
        Invoice::query()->where('id', $id)->update(['is_sold' => false]);
    }

    public static function ATTACHE_PAIEMENT($id , $paiement){
         Invoice::query()->where('id',$id)->first()->payments()->create($paiement);
         Invoice::query()->where('id',$id)->update(['is_deleted'=>false]);
    }

}
/**
 * 1. creer un invoice
 *         ->payer cashs
 */
