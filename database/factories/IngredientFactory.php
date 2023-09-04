<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // DB::table('ingredients')->truncate();
        return [
            'name' => $this->faker->sentence(),
            'price_by_piece' => $this->faker->numerify(),
            'branch_id'=> \App\Models\Branch::all()->random()->id,
        ];
    }
}
