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
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
    }

    /**
     * A basic unit test example.
     */
    public function test_it_can_create_an_invoice(): void
    {
        User::factory()->create();
        Commercial::factory()->create();
        Price::factory()->create();
        Caisse::factory()->create();
    }

    public function test_it_can_create_an_invoice_white_debit_status(): void
    {
        User::factory()->create();
        Commercial::factory()->create();
        Price::factory()->create();
        Caisse::factory()->create();
    }

    public function test_it_can_delete_an_invoice(): void
    {
        User::factory()->create();
        Commercial::factory()->create();
        Price::factory()->create();
        Caisse::factory()->create();
    }
}
