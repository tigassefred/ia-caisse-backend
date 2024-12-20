<?php

namespace Tests\Feature;

use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\GeneratorClass;
use App\Services\refacto\InvoiceServices;
use Carbon\Carbon;
use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestSeeder::class);

        $gen = new GeneratorClass();
        $data =  $gen->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser'] = 0;
        $data['valeur_facture'] = 10000;
        $data['valeur_reduction'] = 0;
        $this->postJson('api/invoices', $data);
    }
    
    /**
     * A basic feature test example.
     */
    public function test_delete_refund(): void
    {
        $inv = Invoice::query()->first();

        $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 1000,
            'date' => Carbon::now()->addDay(),
            'discount' => 0,
            'id' => $inv->id,
        ]);

        $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 1000,
            'date' => Carbon::now()->addDays(2),
            'discount' => 0,
            'id' => $inv->id,
        ]);

        $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 1000,
            'date' => Carbon::now()->addDays(3),
            'discount' => 0,
            'id' => $inv->id,
        ]);

        $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 1000,
            'date' => Carbon::now()->addDays(4),
            'discount' => 0,
            'id' => $inv->id,
        ]);

          $this->assertEquals(5, Payment::query()->count());
          $this->assertEquals(4000, InvoiceServices::GET_PAYMENTS($inv->id));
          $this->assertEquals(6000, InvoiceServices::GET_RELIQUAT($inv->id, 0));

          $paySecond = Payment::query()->where('invoice_id', $inv->id)->where('type', 2)
          ->where('deleted', 0)
          ->where('amount', 1000)
          ->first();

          $response = $this->deleteJson('api/payments/' . $paySecond->id );
          $response->assertStatus(200);
          $this->assertEquals(3000, InvoiceServices::GET_PAYMENTS($inv->id));
          $this->assertEquals(7000, InvoiceServices::GET_RELIQUAT($inv->id, 0));

          $this->postJson('api/invoice/' . $inv->id . '/payment', [
            'amount' => 7000,
            'date' => Carbon::now()->addDays(4),
            'discount' => 0,
            'id' => $inv->id,
        ]);

        $inv = Invoice::query()->first();
        $this->assertEquals(1, $inv->is_sold);
        $this->assertEquals(0, $inv->is_deleted);
        $this->assertEquals(10000, InvoiceServices::GET_PAYMENTS($inv->id));
        $this->assertEquals(0, InvoiceServices::GET_RELIQUAT($inv->id, 0));

        $paySecond = Payment::query()->where('invoice_id', $inv->id)->where('type', 2)
        ->where('deleted', 0)
        ->where('amount', 1000)
        ->first();

        $response = $this->deleteJson('api/payments/' . $paySecond->id );
        $response->assertStatus(200);

        $inv = Invoice::query()->first();
        $this->assertEquals(0, $inv->is_sold);
        $this->assertEquals(0, $inv->is_deleted);
        $this->assertEquals(9000, InvoiceServices::GET_PAYMENTS($inv->id));
        $this->assertEquals(1000, InvoiceServices::GET_RELIQUAT($inv->id, 0));

        $paythird = Payment::query()->where('invoice_id', $inv->id)->where('type', 1)->first();
        $response = $this->deleteJson('api/payments/' . $paythird->id );
        $response->assertStatus(200);

        $inv = Invoice::query()->first();
        $this->assertEquals(0, $inv->is_sold);
        $this->assertEquals(1, $inv->is_deleted);
        $this->assertEquals(0, InvoiceServices::GET_PAYMENTS($inv->id));
        $this->assertEquals(0, InvoiceServices::GET_RELIQUAT($inv->id, 0));

        $this->assertEquals(0, Payment::query()->where('invoice_id', $inv->id)
        ->where('deleted', 0)
        ->count());


    }
}
