<?php

namespace Tests\Feature;

use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\GeneratorClass;
use App\Services\InvoiceService;
use App\Services\refacto\InvoiceServices;
use Carbon\Carbon;
use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestSeeder::class);
    }

    /**
     * A basic feature test example.
     */
    public function test_add_payment(): void
    {
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 100000;
        $data['valeur_facture'] = 1000000;
        $data['valeur_reduction'] = 0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $inv = Invoice::query()->get();
        $this->assertEquals($inv->count(), 1);
        $this->assertEquals(1, Invoice::query()->first()->Payments()->count());
        $payFirst = Payment::query()->where('invoice_id', $inv->first()->id)->first();
        $this->assertEquals(900000, $payFirst->reliquat);
        $this->assertEquals(100000, $payFirst->amount);
        $this->assertDatabaseHas('invoices', ['id' => $inv->first()->id]);

        $response = $this->postJson('api/invoice/' . $inv->first()->id . '/payment', [
            'amount' => 100000,
            'date' => Carbon::now()->addDay()->format('Y-m-d'),
            'discount' => 0,
            'id' => $inv->first()->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(2, Payment::query()->count());
        $this->assertEquals(false, Payment::query()->where('invoice_id', $inv->first()->id)->where('type', 1)->first()->cash_in);
    }

    public function test_add_many_payment(): void
    {
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 0;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $inv = Invoice::query()->get();
        $this->assertEquals($inv->count(), 1);
        $this->assertEquals(1, Invoice::query()->first()->Payments()->count());
        $payFirst = Payment::query()->where('invoice_id', $inv->first()->id)->first();
        $this->assertEquals(10000, $payFirst->reliquat);
        $this->assertEquals(0, $payFirst->amount);
        $this->assertDatabaseHas('invoices', ['id' => $inv->first()->id]);
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);

        $this->postJson('api/invoice/' . $inv->first()->id . '/payment', [
            'amount' => 1000,
            'date' => Carbon::now()->addDay()->format('Y-m-d'),
            'discount' => 0,
            'id' => $inv->first()->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(2, Payment::query()->count());

        $this->assertEquals($inv->count(), 1);
        $this->assertEquals(2, Invoice::query()->first()->Payments()->count());
        $this->assertEquals(9000, InvoiceServices::GET_RELIQUAT(Invoice::query()->find($inv->first()->id)->id, 0));
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);



        $this->postJson('api/invoice/' . $inv->first()->id . '/payment', [
            'amount' => 1000,
            'date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'discount' => 0,
            'id' => $inv->first()->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(3, Payment::query()->count());
        $this->assertEquals(8000, InvoiceServices::GET_RELIQUAT(Invoice::query()->find($inv->first()->id)->id, 0));
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);

        $this->postJson('api/invoice/' . $inv->first()->id . '/payment', [
            'amount' => 8000,
            'date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'discount' => 0,
            'id' => $inv->first()->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(4, Payment::query()->count());
        $this->assertEquals(0, InvoiceServices::GET_RELIQUAT(Invoice::query()->find($inv->first()->id)->id, 0));
        $this->assertEquals(1, Invoice::query()->find($inv->first()->id)->is_sold);
    }

    public function test_solded_payment()
    {
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 10000;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $inv = Invoice::query()->get();
        $this->assertEquals($inv->count(), 1);
        $payFirst = Payment::query()->where('invoice_id', $inv->first()->id)->first();
        $this->assertEquals(0, $payFirst->reliquat);
        $this->assertEquals(10000, $payFirst->amount);
        $this->assertDatabaseHas('invoices', ['id' => $inv->first()->id]);
        $this->assertEquals(1, Invoice::query()->find($inv->first()->id)->is_sold);
    }

    public function test_un_solded_payment()
    {
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 6500;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $inv = Invoice::query()->get();
        $this->assertEquals($inv->count(), 1);
        $payFirst = Payment::query()->where('invoice_id', $inv->first()->id)->first();
        $this->assertEquals(3500, $payFirst->reliquat);
        $this->assertEquals(6500, $payFirst->amount);
        $this->assertDatabaseHas('invoices', ['id' => $inv->first()->id]);
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);
    }
    public function test_debit_and_solded_same_day()
    {
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 0;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;
        $this->postJson('api/invoices', $data);
        $inv = Invoice::query()->first();
        $this->assertEquals(1, Payment::query()->count());
        $this->assertEquals(0, Payment::query()->where('invoice_id', $inv->id)->first()->amount);
        $this->assertEquals(10000, Payment::query()->where('invoice_id', $inv->id)->first()->reliquat);
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);

        $response =  $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 10000,
            'date' => Carbon::now(),
            'discount' => 0,
            'id' => $inv->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, Payment::query()->count());
        $this->assertEquals(10000, Payment::query()->where('invoice_id', $inv->id)->first()->amount);
        $this->assertEquals(0, Payment::query()->where('invoice_id', $inv->id)->first()->reliquat);
        $this->assertEquals(1, Payment::query()->where('invoice_id', $inv->id)->first()->type);
        $this->assertEquals(1, Invoice::query()->find($inv->first()->id)->is_sold);
    }

    public function test_debit_and_pay_but_not_solded_same_day()
    {
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 0;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;
        $this->postJson('api/invoices', $data);
        $inv = Invoice::query()->first();
        $this->assertEquals(1, Payment::query()->count());
        $this->assertEquals(0, Payment::query()->where('invoice_id', $inv->id)->first()->amount);
        $this->assertEquals(10000, Payment::query()->where('invoice_id', $inv->id)->first()->reliquat);
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);

        $response =  $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 6000,
            'date' => Carbon::now(),
            'discount' => 0,
            'id' => $inv->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, Payment::query()->count());
        $this->assertEquals(6000, Payment::query()->where('invoice_id', $inv->id)->first()->amount);
        $this->assertEquals(4000, Payment::query()->where('invoice_id', $inv->id)->first()->reliquat);
        $this->assertEquals(1, Payment::query()->where('invoice_id', $inv->id)->first()->type);
        $this->assertEquals(0, Invoice::query()->find($inv->id)->is_sold);
        $payFirst = Payment::query()->first();

        $response =  $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 4000,
            'date' => Carbon::now()->addDay(),
            'discount' => 0,
            'id' => $inv->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(2, Payment::query()->count());
        $this->assertEquals(1, Invoice::query()->find($inv->id)->is_sold);
        $this->assertEquals(0, Invoice::query()->find($inv->id)->is_deleted);
        $this->assertEquals(10000, Payment::query()->where('invoice_id', $inv->id)->sum('amount'));
        $this->assertEquals(10000, InvoiceServices::GET_PAYMENTS($inv->id));
        $secondPay = Payment::query()->whereNot('id', $payFirst->id)->first();
        $this->assertEquals(4000, $secondPay->amount);
        $this->assertEquals(0, $secondPay->reliquat);
        $this->assertEquals(2, $secondPay->type);
    }
    public function test_pay_with_discount_same_day(){
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 0;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;
        $this->postJson('api/invoices', $data);
        $inv = Invoice::query()->first();
        $this->assertEquals(1, Payment::query()->count());
        $this->assertEquals(0, Payment::query()->where('invoice_id', $inv->id)->first()->amount);
        $this->assertEquals(10000, Payment::query()->where('invoice_id', $inv->id)->first()->reliquat);
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);

        $response =  $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 8000,
            'date' => Carbon::now(),
            'discount' => 2000,
            'id' => $inv->id,
        ]);
        $response->assertStatus(200);
        $payFirst = Payment::query()->first();
        $this->assertEquals(1, Payment::query()->count());
        $this->assertEquals(8000, $payFirst->amount);
        $this->assertEquals(2000, Invoice::query()->first()->discount);
        $this->assertEquals(0, $payFirst->reliquat);
        $this->assertEquals(1, Invoice::query()->find($inv->first()->id)->is_sold);
    }
    public function test_pay_sup_than_remainder(){
        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 1000;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;
        $this->postJson('api/invoices', $data);
        $inv = Invoice::query()->first();
        $this->assertEquals(0, Invoice::query()->find($inv->first()->id)->is_sold);
        $this->assertEquals(1, Payment::query()->count());
        $this->assertEquals(1000, InvoiceServices::GET_PAYMENTS($inv->id));
        $this->assertEquals(9000, InvoiceServices::GET_RELIQUAT($inv->id, 0));
       
        $amount  = 10000;
        $this->assertTrue(floatval(InvoiceServices::GET_RELIQUAT($inv->id, 0)) < floatval($amount));
        $response =  $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => $amount,
            'date' => Carbon::now()->addDay(),
            'discount' => 0,
            'id' => $inv->id,
        ]);
        $response->assertStatus(400);
    }
}
