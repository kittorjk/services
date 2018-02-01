<?php

use Illuminate\Database\Seeder;
use App\User;

class AdminBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            "name" => "Nestor",
            "login" => "rat",
            "password" => Hash::make('rattar')
        ]);

    }
}
