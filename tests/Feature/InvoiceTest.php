<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\Price;
use App\Models\User;
use App\Services\refacto\InvoiceServices;
use App\Services\refacto\PaymentService;
use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestSeeder::class);
    }

    /**
     * A basic unit test example.
     */
    public function test_it_can_create_simple_an_invoice(): void
    {
        $data = $this->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        
    }
    public function test_it_can_create_an_invoice(): void
    {
        $data = $this->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser']=10000;
        $data['valeur_facture']=100000;
        $data['valeur_reduction']=0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $countInvoice = Invoice::query()->count();
        $this->assertEquals(1, $countInvoice);
        $invoice = Invoice::query()->first();
        $this->assertEquals($data['valeur_reduction'], $invoice->discount);
        $this->assertEquals($data['valeur_facture'], $invoice->amount);
        $this->assertEquals($data['is10Yaars'], $invoice->is_10Yaar);
        $this->assertTrue(strtoupper($data['name']) == $invoice->name);
        $this->assertEquals(0, $invoice->is_deleted);
        $this->assertEquals(0, $invoice->is_sold);

        $this->assertEquals(90000, InvoiceServices::GET_RELIQUAT($invoice->id));
        $this->assertEquals(1 , count($invoice->Payments));
        $this->assertEquals($data['somme_verser'], $invoice->Payments->first()->amount);
        $this->assertEquals($data['comments'], $invoice->Payments->first()->comment);
        $this->assertEquals(1, $invoice->Payments->first()->type);
        $this->assertEquals(90000, $invoice->Payments->first()->reliquat);
        $this->assertEquals(0, $invoice->Payments->first()->cash_in);
        $this->assertEquals(0, $invoice->Payments->first()->deleted);

    }

    public function test_it_can_create_an_invoice_white_debit_status(): void {
        $data = $this->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser']=0;
        $data['valeur_facture']=100000;
        $data['valeur_reduction']=0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $countInvoice = Invoice::query()->count();
        $this->assertEquals(1, $countInvoice);
        $invoice = Invoice::query()->first();
        $this->assertEquals(0, $invoice->is_sold);
        $this->assertEquals(0, $invoice->discount);
        $this->assertEquals(100000, $invoice->amount);

        $this->assertEquals(0, $invoice->Payments->first()->amount);
        $this->assertEquals(100000, $invoice->Payments->first()->reliquat);
    }

    public function test_it_can_create_an_invoice_whitout_debit_status(): void {
        $data = $this->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser']=100000;
        $data['valeur_facture']=100000;
        $data['valeur_reduction']=0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $countInvoice = Invoice::query()->count();
        $this->assertEquals(1, $countInvoice);
        $invoice = Invoice::query()->first();
        $this->assertEquals(1, $invoice->is_sold);
        $this->assertEquals(0, $invoice->discount);
        $this->assertEquals(100000, $invoice->amount);

        $this->assertEquals(100000, $invoice->Payments->first()->amount);
        $this->assertEquals(0, $invoice->Payments->first()->reliquat);
        
    }

    public function test_it_can_create_an_invoice_white_debit_status2(): void {
        $data = $this->generateInvoice();
        $data['commercial'] = Commercial::query()->first()->id;
        $data['somme_verser']=80000;
        $data['valeur_facture']=100000;
        $data['valeur_reduction']=0;

        $response = $this->postJson('api/invoices', $data);
        $response->assertStatus(200);
        $countInvoice = Invoice::query()->count();
        $this->assertEquals(1, $countInvoice);
        $invoice = Invoice::query()->first();
        $this->assertEquals(0, $invoice->is_sold);
        $this->assertEquals(0, $invoice->discount);
        $this->assertEquals(100000, $invoice->amount);

        $this->assertEquals(80000, $invoice->Payments->first()->amount);
        $this->assertEquals(20000, $invoice->Payments->first()->reliquat);

    }

    private function generateInvoice()
    {
        return [
            'name' => 'john doe',
            'client_id' => NULL,
            'valeur_reduction' => 200000,
            'reliquat' => 0,
            'comments' => 'testte',
            'is10Yaars' => true,
            'valeur_facture' => 1020000,
            'valeur_apres_reduction' => 820000,
            'somme_verser' => 820000,
            'commercial' => '88b34fc4-6a41-4779-ba07-6ae96baedf22',
            'Paniers' => [
                [
                    'uuid' => 7006,
                    'qte' => '1',
                    'name' => 'GRP2022341',
                    'designation' => 'HAND BALLE No.74',
                    'type' => 'BALLE',
                    'cbm' => '1',
                ],
                [
                    'uuid' => 7007,
                    'qte' => '1',
                    'name' => 'GRP2022341',
                    'designation' => 'GRD BALLE No.72(1200PCS)',
                    'type' => 'BALLE',
                    'cbm' => '1',
                ],
                [
                    'uuid' => 7011,
                    'qte' => '1',
                    'name' => 'GRP2022341',
                    'designation' => 'HAND BALLE No.120',
                    'type' => 'BALLE',
                    'cbm' => '1',
                ],
            ],
        ];
    }
}
