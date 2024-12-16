<?php

namespace Tests\Feature;

use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\Price;
use App\Models\User;
use App\Services\refacto\InvoiceServices;
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
        User::query()->get();
        $name = 'jonh yale';
        $inv = new InvoiceServices(null);
        $this->assertNull($inv->id, "l'objet est null");
        $inv->setNewInvoice($name, 25000, 5000 , false);
        $inv->setcasher(User::query()->first()->id);
        $inv->setCommercial(Commercial::query()->first()->id);
        $inv->setPrice(Price::query()->first()->id);
        $inv->create();
        $this->assertNotNull($inv->id, "les affection on ete effectuer");
        $this->assertNotNull(Invoice::query()->where('id', $inv->id)->first()->id, "l'enregistrement c'est bien passee");
        $invoice = Invoice::query()->where('id', $inv->invoice->id)->first();
        $this->assertNotNull($invoice, "On as recupere l'objet");
        $this->assertEquals(strtoupper($name), $invoice->name);
        $this->assertEquals(25000, $invoice->price);
        $this->assertFalse($invoice->is_sold);
        $invoice->solded();
        $this->assertTrue($invoice->is_sold);
        $invoice->unsolded();
        $this->assertFalse($invoice->is_sold);
    }
}
