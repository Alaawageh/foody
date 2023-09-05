<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'=> 'kitchen',
            'email'=> 'kitchen@gmail.com',
            'password' => bcrypt('123456789'),
            'type' => 'Kitchen',
            'branch_id' => 1
        ]);

        User::create([
            'name'=> 'casher',
            'email'=> 'casher@gmail.com',
            'password' => bcrypt('123456789'),
            'type' => 'Casher',
            'branch_id' => 1
        ]);
        
    }
}
