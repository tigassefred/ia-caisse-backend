<?php

namespace App\Services\refacto;

use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\throwException;

class InvoiceServices
{
    public ?Invoice $invoice = null;
    public ?string $id = null;
    private $newInvoice = [
        'name' => '',
        'amount' => 0,
        'discount' => 0,
        'is_10Yaar' => false,
        'commercial_id' => null,
        'price_id' => null,
        'caisse_id' => null,
        'is_sold' => false,
        'customer_id' => null,
        'deleted' => true,
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

    public function setNewInvoice(string $name, int $amount, int $discount, string $caisse, ?bool $zone): void
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

    public function createInvoice()
    {
        $tmp =  Invoice::create($this->newInvoice);
        $this->populateInvoice($tmp->id);
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function getPaiements()
    {
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

    public function activeInvoice()
    {
        if ($this->getInvoice() != null) {
            $tmp =  Invoice::query()->where('id', $this->getInvoice()->id)->first();
            $tmp->is_deleted = false;
            $tmp->save();
        }
    }

    public static function ATTACHE_PAIEMENT($id, $paiement)
    {
        try {


            $pay = new Payment();
            $pay->amount = $paiement['amount'];
            $pay->user_id = $paiement['user_id'];
            $pay->type = $paiement['type'];
            $pay->comment = $paiement['comment'];
            $pay->deleted = false;
            $pay->invoice_id = $id;

            $pay->reliquat = $paiement['reliquat'];
            $pay->save();
            return $pay->id;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception("Echec de l'enregistrement du paiement");
        }
    }

    /**
     * @throws \Exception
     */
    public function addInvoiceItem(array $data): void
    {
        try {
            if ($this->getInvoice() != null) {
                $items = new InvoiceItem($data);
                $items->invoice()->associate($this->getInvoice());
                $items->save();
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new \Exception("Echec de creation de la nouvelle facture");
        }
    }


    /**
     * @throws \Exception
     */
    public static function GET_RELIQUAT($id, $amount = 0): float|int
    {
        try {
            $invoice = Invoice::query()->where('id', $id)->first();
            if($invoice->is_deleted == 1){
                return 0;
            }
            $totalAmount = (float)$invoice->amount - (float) $invoice->discount;
            $totalPayments = 0.0;
            if (count($invoice->payments) > 0) {
                foreach ($invoice->payments as $payment) {
                    if ($payment->deleted == false) {
                        $totalPayments += (float)$payment->amount;
                    }
                }
            }
            $remainingAmount = $totalAmount - ($totalPayments + floatval($amount));

            return ($remainingAmount > 0) ? $remainingAmount : 0;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public static function GET_PAYMENTS($id): float|int
    {
        try {
            $invoice = Invoice::query()->where('id', $id)->first();
            $totalPayments = 0.0;
            if (count($invoice->payments) > 0) {
                foreach ($invoice->payments as $payment) {
                    if ($payment->deleted == false) {
                        $totalPayments += (float)$payment->amount;
                    }
                }
            }
            return $totalPayments;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public static function PLUS_DISCOUNT($id, $newDiscount)
    {
        $invoice = Invoice::query()->where('id', $id)->first();
        $updatedDiscount = $invoice->discount + $newDiscount;

        $netPay = $invoice->amount + $updatedDiscount;
        if ($netPay - InvoiceServices::GET_PAYMENTS($id) >= 0) {
            $invoice->discount = $updatedDiscount;
            $invoice->save();
        }else{
            throw new Exception("Imposible de faire cette reduction");
        }
    }
}
/**
 * 1. creer un invoice
 *         ->payer cashs
 */
