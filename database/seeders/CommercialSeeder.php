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

        Commercial::factory()->count(10)->create();
        Commercial::create([
            'name' => "SALIFOU ILBOUD",
            "id"=>Str::uuid(),
        ]);

 
    }
}
