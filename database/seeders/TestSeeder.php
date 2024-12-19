<?php

namespace Database\Seeders;

use App\Models\Caisse;
use App\Models\Commercial;
use App\Models\Price;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create();
        Commercial::factory()->create();
        Price::factory()->create();
        Caisse::factory()->create();
    }
}
