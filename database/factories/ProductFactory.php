<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    
    public function definition()
    {
        // DB::table('products')->truncate();
        return [
            'name' => $this->faker->sentence(),
            'price' => $this->faker->numerify(),
            'ingredient' => $this->faker->text(),
            'estimated_time'=>$this->faker->dateTime(),
            'status'=>$this->faker->boolean(),
            'category_id' => \App\Models\Category::all()->random()->id,
            'branch_id'=> \App\Models\Branch::all()->random()->id,
        ];



    }
}
