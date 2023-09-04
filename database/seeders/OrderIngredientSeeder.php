<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderIngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ing = Ingredient::all();
        $orders = Order::all();
        foreach ($orders as $order) {   
            $order->ingredients()->attach($ing);
        }
    }
}
