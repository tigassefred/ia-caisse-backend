<?php

namespace Tests\Feature;

use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\GeneratorClass;
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
        $payFirst = Payment::query()->where('invoice_id' , $inv->first()->id)->first();
        $this->assertEquals(900000 ,$payFirst->reliquat);
        $this->assertEquals(100000 ,$payFirst->amount);
        $this->assertDatabaseHas('invoices', ['id' => $inv->first()->id]);

        $response = $this->postJson('api/invoice/'.$inv->first()->id.'/payment', [
            'amount' => 100000,
            'date' => '2022-01-01',
            'discount' => 0,
            'id' => $inv->first()->id,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(2, Payment::query()->count());

    }
}
