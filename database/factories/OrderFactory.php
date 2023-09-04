<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    
    public function definition()
    {
        // DB::table('orders')->truncate();
        $status = ['Befor_Preparing','Preparing','Done'];
        $start_date = $this->faker->dateTimeBetween('2020-01-01');
        return [
            'status'=>$this->faker->randomElement($status),
            'table_num'=>$this->faker->sentence(),
            'is_paid'=>$this->faker->boolean(),
            'total_price' =>$this->faker->numerify(),
            'time'=>$this->faker->dateTime(),
            'time_end'=>$this->faker->dateTime(),
            'branch_id'=>Branch::all()->random()->id,
            'created_at'=> $this->faker->dateTimeBetween($start_date , now())
        ];
    }
}
