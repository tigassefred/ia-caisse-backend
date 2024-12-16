<?php

namespace App\Services\refacto;

use App\Models\Invoice;

class PaymentService
{
    public string $ID;
    public Invoice $invoice;

    public function __construct(?string $ID)
    {
        if ($ID != null) {
            $this->ID = $ID;
            $this->fetchInvoiceFromDatabase($ID);
        }
    }

    private function fetchInvoiceFromDatabase(string $ID): void
    {
        $this->invoice = Invoice::query()->where('id', $ID)->firstOrFail();
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function createInvoice(Invoice $invoice): void
    {

    }


}

