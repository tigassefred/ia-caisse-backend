<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Carbon\Carbon;
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

    public function payDebit(string $amount , string $date): void
    {
        $newAmount = intval($amount);
        $outstanding = $this->getCreance();
        $isFullyPaid = $outstanding <= $newAmount;

        $payData = [
            'amount' => $amount,
            'reliquat' => $isFullyPaid ? 0 : $outstanding - $newAmount,
            'cash_in' => true,
            'user_id' => User::query()->first()->id,
            'type' => '2',
            'cash_in_date' => Carbon::parse($date)->setTime(now()->hour, now()->minute, now()->second)->toDateTimeString()
        ];

        $paymentService = new PaymentService(null);
        $paymentService->makePayment($payData, $this->getInvoice()->id);

        $this->setSolded($isFullyPaid);
    }


    public function setInvoice(Invoice $Invoice): void
    {
        $this->Invoice = $Invoice;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->Invoice;
    }

    public function getCreance()
    {
        if ($this->Invoice) {
            $pays = $this->getInvoice()->Payments->where('deleted', 0);
            $versement = $pays->sum('amount');
            return $this->getInvoice()->montant_net - $versement;
        }
        return null;
    }

    public  function setSolded(bool $status){
        $this->getInvoice()->is_sold = $status;
        $this->getInvoice()->save();
    }

}
