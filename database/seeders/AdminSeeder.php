<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       User::create([
            'name' => 'food',
            'email' => 'food@gmail.com',
            'password' => bcrypt('123456789'),
            'type' => 'admin',
            'branch_id' => '1'
        ]);
    }
}
