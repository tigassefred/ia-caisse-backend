<?php

namespace Database\Seeders;

use App\Models\Caisse;
use App\Models\CashSession;
use App\Models\Price;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         Artisan::call('migrate:fresh');
         User::factory(10)->create();
         Caisse::factory()->create();

        $this->call(CommercialSeeder::class);
        Price::create([
            'balle' => 340000,
            'colis' => 250000,
        ]);
        
    }
}
