<?php

namespace App\Services\refacto;

use App\Models\Commercial;
use App\Models\Invoice;


class InvoiceServices
{
    public ?Invoice $invoice = null;
    public ?string $id = null;

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

    public function setNewInvoice(string $name, int $amount, int $discount, ?bool $zone):void
    {
        $this->invoice = new Invoice();
        $this->invoice->name = strtoupper($name);
        $this->invoice->amount = $amount;
        $this->invoice->discount = $discount;
        $this->invoice->is_10Yaar = $zone ?? false;
    }

    public function setCommercial(string $id)
    {
        $count_commercial = Commercial::query()->where('id', $id)->count();
        if ($count_commercial > 0) {
            $this->invoice->commercial_id = $id;
        } else {
            throw new \Exception("Le commercial est introuvable");
        }

    }

    public function getInvoice(){
        return $this->invoice;
    }

    public function solded()
    {
        $this->invoice->is_sold = true;
    }

    public function unsolded()
    {
        $this->invoice->is_sold =false;
    }

    public function setPrice($price_id){
        $this->invoice->price_id = $price_id;
    }
    public function setcasher($id){
        $this->invoice->caisse_id = $id;
    }

}
/**
 * 1. creer un invoice
 *         ->payer cashs
 */
