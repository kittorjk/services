<?php

use Illuminate\Database\Seeder;
use App\CiteCode;

class CiteCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // array('code'=>'', 'area'=>'', 'branch_id'=>, 'status'=>, 'usuario_creacion'=>, 'usuario_modificacion'=>, 'created_at'=>, 'updated_at'),
        $data = array(
            array('code'=>'AB-GG', 'area'=>'Gerencia General', 'branch_id'=>3, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-GTEC', 'area'=>'Gerencia Tecnica', 'branch_id'=>3, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-GADM', 'area'=>'Gerencia Administrativa', 'branch_id'=>3, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-GRES', 'area'=>'Gerencia General', 'branch_id'=>4, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-ADMS', 'area'=>'Gerencia Administrativa', 'branch_id'=>4, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-TECS', 'area'=>'Gerencia Tecnica', 'branch_id'=>4, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-GREC', 'area'=>'Gerencia General', 'branch_id'=>5, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-ADMC', 'area'=>'Gerencia Administrativa', 'branch_id'=>5, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1),
            array('code'=>'AB-TECC', 'area'=>'Gerencia Tecnica', 'branch_id'=>5, 'status'=>1, 'usuario_creacion'=>1, 'usuario_modificacion'=>1)
        );
        
        CiteCode::insert($data); // Eloquent approach
    }
}
