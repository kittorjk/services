<?php

use User;

use Illuminate\Database\Seeder;

class BaseStructureSeeder extends Seeder {

	public function run(){
		$user = User::create([
					"name" => "Adolfo",
					"login" => "adolfo",
					"password" => Hash::make('arildo10')
				]);
	}
}
