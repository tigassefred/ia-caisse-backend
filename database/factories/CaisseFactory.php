<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Caisse>
 */
class CaisseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::now();
        $start_date = $date->copy()->startOfDay();
        $date->addDays(1)->setHour(7)->setMinute(30)->setSecond(0);
        $end_date = $date->copy();

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'transaction' => 0,
            'encaissement' => 0,
            'creance' => 0,
            'remboursement' => 0,
            '_10yaar' => 0,
            'magazin' => 0,
            'versement_magasin' => 0,
            'versement_10yaar' => 0,
            'status' => true,
        ];
    }
}
