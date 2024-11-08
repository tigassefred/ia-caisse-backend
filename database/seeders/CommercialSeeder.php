<?php

namespace Database\Seeders;

use App\Models\Commercial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommercialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       foreach (range(1, 10) as $index) {
            Commercial::create([
                'name' => fake()->name(),
                "id"=>Str::uuid(),
            ]);
        }
    }
}
