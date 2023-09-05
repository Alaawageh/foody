<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Middleware\SuperAdmin;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use IntlCalendar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        
        Category::factory()->count(3)->create();
        Branch::factory()->count(3)->create();
        Product::factory(20)->create();
        Ingredient::factory(20)->create();
        Order::factory(20)->create();
        $this->call([
            ProductIngredientSeeder::class,
            OrderProductSeeder::class,
            OrderIngredientSeeder::class,
            UsersSeeder::class
        ]);

    }
}
