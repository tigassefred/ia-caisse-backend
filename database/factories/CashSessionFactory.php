<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashSession>
 */
class CashSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date =  now();

        return [
            'id'=>fake()->uuid(),
            'name'=> $this->faker->sentence(),
            'start_date' => $date->setTime(7,0,0,0),
            'status'=>'waiting',
        ];
    }
}
