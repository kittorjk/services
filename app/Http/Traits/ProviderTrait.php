<?php
/**
 * User: Admininstrador
 * Date: 24/08/2018
 * Time: 03:07 PM
 */

namespace App\Http\Traits;

//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Provider;
use Exception;

trait ProviderTrait {
    
    public function incompleteProviderRecords() {
        // Returns all records missing required fields

        $providers = Provider::whereNull('specialty')
            ->orwhere('prov_name','')
            ->orwhere('nit','=',0)
            ->orwhere('phone_number','=',0)
            ->orwhere('address','=','')
            ->orwhere('bnk_account','=','')
            ->orwhere('bnk_name','=','')
            ->orwhere('contact_name','=','')
            ->orwhere('contact_id','=',0)
            ->orwhere('contact_id_place','=','')
            ->orwhere('contact_phone','=',0)
            ->orderBy('prov_name')
            ->get();

        return $providers;
    }
    
    public function validProviderRecords() {
        // Returns all records with complete information (valid)
        
        $providers = Provider::select('id','prov_name')
            ->where('prov_name','<>','')
            ->where('nit','<>',0)
            ->whereNotNull('specialty')
            ->where('phone_number','<>',0)
            ->where('address','<>','')
            ->where('bnk_account','<>','')
            ->where('bnk_name','<>','')
            ->where('contact_name','<>','')
            ->where('contact_id','<>',0)
            ->where('contact_id_place','<>','')
            ->where('contact_phone','<>',0)
            ->OrderBy('prov_name')
            ->get();

        return $providers;
    }
}
