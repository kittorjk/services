<?php

use Illuminate\Database\Seeder;
use App\Cite;

class CiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $cites = [
            ['title' => 'AB-GG', 'user_id' => 1, 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia General', 'asunto' => 'empty', 'num_cite' => 0],
            ['title' => 'AB-GTEC', 'user_id' => 1, 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia Tecnica', 'asunto' => 'empty', 'num_cite' => 0],
            ['title' => 'AB-ADM', 'user_id' => 1, 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia Administrativa', 'asunto' => 'empty', 'num_cite' => 0],
            // Para 2017
            ['title' => 'AB-GG', 'user_id' => 1, 'created_at' => '2017-01-01 00:00:01', 'updated_at' => '2017-01-01 00:00:01', 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia General', 'asunto' => 'empty', 'num_cite' => 0],
            ['title' => 'AB-GTEC', 'user_id' => 1, 'created_at' => '2017-01-01 00:00:01', 'updated_at' => '2017-01-01 00:00:01', 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia Tecnica', 'asunto' => 'empty', 'num_cite' => 0],
            ['title' => 'AB-ADM', 'user_id' => 1, 'created_at' => '2017-01-01 00:00:01', 'updated_at' => '2017-01-01 00:00:01', 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia Administrativa', 'asunto' => 'empty', 'num_cite' => 0],
            // Para 2018
            ['title' => 'AB-GG', 'user_id' => 1, 'created_at' => '2018-01-01 00:00:01', 'updated_at' => '2018-01-01 00:00:01', 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia General', 'asunto' => 'empty', 'num_cite' => 0],
            ['title' => 'AB-GTEC', 'user_id' => 1, 'created_at' => '2018-01-01 00:00:01', 'updated_at' => '2018-01-01 00:00:01', 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia Tecnica', 'asunto' => 'empty', 'num_cite' => 0],
            ['title' => 'AB-ADM', 'user_id' => 1, 'created_at' => '2018-01-01 00:00:01', 'updated_at' => '2018-01-01 00:00:01', 'destino' => 'empty', 'responsable' => 'empty',
                'para_empresa' => 'empty', 'area' => 'Gerencia Administrativa', 'asunto' => 'empty', 'num_cite' => 0],
        ];

        foreach($cites as $cite){
            Cite::create($cite);
        }

    }
}
