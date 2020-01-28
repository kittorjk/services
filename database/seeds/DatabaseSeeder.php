<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        $user = User::create([
                    "name" => "Adolfo",
                    "login" => "adolfo",
                    "password" => Hash::make('arildo10')
                ]);
                */
        Model::unguard();

        //$this->call(AdminBaseSeeder::class);
        //$this->call(CiteSeeder::class);
        $this->call(CiteCodesSeeder::class);
        
        Model::reguard();
    }
}
