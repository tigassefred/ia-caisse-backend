<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\Price;
use App\Models\User;
use App\Services\refacto\InvoiceServices;
use App\Services\refacto\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_create_invoice(): void
    {
        User::factory()->create();
        Commercial::factory()->create();
        Price::factory()->create();
        Caisse::factory()->create();

        $invoice = new InvoiceServices(null);

        $invoice->setNewInvoice("john doe", 1000, 0, Caisse::first()->id, true);
        $this->assertEquals($invoice->getPaiements()['name'] , strtoupper("john doe"));
        $this->assertEquals($invoice->getPaiements()['amount'] , 1000);
        $this->assertEquals($invoice->getPaiements()['discount'] , 0);
        $this->assertEquals($invoice->getPaiements()['is_10Yaar'] , true);
        $this->assertEquals($invoice->getPaiements()['caisse_id'] , Caisse::first()->id);
        $this->assertEquals($invoice->getPaiements()['deleted'] , true);

        $invoice->setCommercial(Commercial::first()->id);
        $this->assertEquals($invoice->getPaiements()['commercial_id'] , Commercial::first()->id);

        $invoice->setPrice(Price::first()->id);
        $this->assertEquals($invoice->getPaiements()['price_id'] , Price::first()->id);

        $invoice->createInvoice();
        $this->assertEquals($invoice->getInvoice()->name , strtoupper("john doe"),"La facture a ete creer");
        $this->assertEquals(Invoice::query()->first()->is_deleted , false);

        $invoice2 = new InvoiceServices(null);
        $invoice2->setNewInvoice("john doe", 1000, 0, Caisse::first()->id, false);
        $this->assertEquals($invoice2->getPaiements()['is_10Yaar'] , false);

        $paiement = new PaymentService(null);
        $paiement->setAmount(1000);
        $paiement->setUser(User::first()->id);
        $paiement->setReliquat(0);
        $paiement->setType(1);
        $paiement->setComment("paiement");
        $paiement->getPayment();
        $this->assertEquals($paiement->getPayment()['amount'] , 1000);
        $this->assertEquals($paiement->getPayment()['user_id'] , User::first()->id);
        $this->assertEquals($paiement->getPayment()['reliquat'] , 0);
        $this->assertEquals($paiement->getPayment()['type'] , 1);
        $this->assertEquals($paiement->getPayment()['comment'] , "paiement");

        InvoiceServices::ATTACHE_PAIEMENT(Invoice::first()->id , $paiement->getPayment());
        $this->assertEquals(Invoice::first()->payments->first()->deleted , 0);
        $this->assertEquals(Invoice::first()->payments->first()->amount , 1000);
        $this->assertEquals(Invoice::first()->payments->first()->user_id , User::first()->id);
        $this->assertEquals(Invoice::first()->payments->first()->reliquat , 0);
        $this->assertEquals(Invoice::first()->payments->first()->type , 1);
        $this->assertEquals(Invoice::first()->payments->first()->comment , "paiement");

        InvoiceServices::SOLDED(Invoice::first()->id);
        $this->assertEquals(Invoice::first()->is_sold , true);

        InvoiceServices::UNSOLDED(Invoice::first()->id);    
        $this->assertEquals(Invoice::first()->is_sold , false);

        PaymentService::CASH_IN(Invoice::first()->payments->first()->id);
        $this->assertEquals(Invoice::first()->payments->first()->cash_in , true);

        PaymentService::UN_CASH_IN(Invoice::first()->payments->first()->id);
        $this->assertEquals(Invoice::first()->payments->first()->cash_in , false);
    }

    public function test_create_invoice_to_pay_cash(): void{
        User::factory()->create();
        Commercial::factory()->create();
        Price::factory()->create();
        Caisse::factory()->create();

        
    }
}
