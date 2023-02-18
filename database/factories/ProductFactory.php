<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'quantity' => $this->faker->numberBetween(1, 50),
            'price' => $this->faker->randomFloat('2',10,100),
            'user_id' => rand(1, 100),
            'created_at' => Carbon::now()->subMonth(rand(0, 2)),
        ];
    }
}
