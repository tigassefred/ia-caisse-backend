<?php

namespace Database\Factories;

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
        return [
            'start_date' => fake()->dateTime(),
            'end_date' => fake()->dateTime(),
            'transaction' => fake()->sentence(),
            'encaissement' => fake()->sentence(),
            'creance' => fake()->sentence(),
            'remboursement' => fake()->sentence(),
            '_10yaar' => fake()->sentence(),
            'magazin' => fake()->sentence(),
            'versement_magasin' => fake()->sentence(),
            'versement_10yaar' => fake()->sentence(),
            'status' => true,
        ];
    }
}
