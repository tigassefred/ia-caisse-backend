<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    protected ?Invoice $Invoice = null;

    public function __construct(?string $id = null)
    {
        if ($id != null) {
            $Invoice = Invoice::query()->where('id', $id)->firstOrFail();
            $this->setInvoice($Invoice);
        }
    }

    public function createInvoice(array $data): void
    {
        try {
            $newInvoice = new Invoice();
            $newInvoice->fill($data);
            $newInvoice->save();
            $this->setInvoice($newInvoice);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new \Exception("Echec de creation la nouvelle facture");
        }
    }

    /**
     * @throws \Exception
     */
    public function addInvoiceItem(array $data): void
    {
        try {
            if ($this->Invoice != null) {
                $items = new InvoiceItem($data);
                $items->invoice()->associate($this->getInvoice());
                $items->save();
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new \Exception("Echec de creation de la nouvelle facture");
        }
    }

    public function setInvoice(Invoice $Invoice): void
    {
        $this->Invoice = $Invoice;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->Invoice;
    }


}
