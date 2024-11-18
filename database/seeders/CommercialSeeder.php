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
        Commercial::insert([
            [
                'name' => "SALIF ILBOUDO",
                "short" => "S.I",
                "id" => Str::uuid(),
            ],
            [
                'name' => "SOULEYMANE OUEDRAOGO",
                'id' => Str::uuid(),
                'short' => 'S.O',
            ],
            [
                'name' => "YACOUBA ILBOUDO",
                'short' => 'Y.I',
                'id' => Str::uuid(),
            ]
        ]);


    }
}
