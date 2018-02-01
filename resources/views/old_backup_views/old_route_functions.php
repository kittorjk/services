<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 11/07/2017
 * Time: 12:16 PM
 */
?>

<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
Route::get('/ajax', function(){
    if(Request::ajax()){
        return 'Hola';
    }
});

Route::get('install', function() {
    try {
        Artisan::call('migrate');
        echo 'Migracion completa';
    } catch (Exception $e) {
        response()->make($e->getMessage(), 500);
    }
});

Route::get('seed', function(){
    try{
        Artisan::call('db:seed', ['--class' => 'CiteSeeder']);
        echo "Completado";
    }
    catch (Exception $e){
        response()->make($e->getMessage(), 500);
    }
});
*/

/* Code to add the id of a provider to replace the provider name field in a oc record 
Route::get('prov_update', function() {

    $ocs = App\OC::where('id','>',0)->get();

    foreach($ocs as $oc){
        $provider = App\Provider::where('prov_name','=',$oc->provider)->first();

        if($provider){
            $oc->provider_id = $provider->id;
            $oc->save();
        }
    }

    return 'concluido';
});
*/

/* For testing the generation of an excel file
Route::get('/storexls', function () {

    $data = App\User::all();

    //$data = Item::get()->toArray();
    Maatwebsite\Excel\Facades\Excel::create('new_example', function($excel) use ($data) {
        $excel->sheet('mySheet', function($sheet) use ($data)
        {
            $sheet->fromArray($data);
        });
    })->save("xlsx");

    return 'ended';

    $data = collect();

    foreach($users as $user)
    {
        $data->prepend(
            [   'Nombre'        => $user->name,
                'Login'         => $user->login,
                'Password'      => $user->password,
                'Creado el'     => date_format($user->created_at,'d-m-Y'),
                'Area'          => $user->area,
                'Nivel'         => $user->priv_level,
            ]);
    }

    Maatwebsite\Excel\Facades\Excel::create('my_example', function($excel) use ($data) {
        $excel->sheet('mySheet', function($sheet) use ($data)
        {
            $sheet->fromArray($data);
        });
    })->save("xls");

});
*/

/* Function to fill code column
Route::get('/generate_task_codes', function () {

    $tasks = App\Task::all();

    foreach($tasks as $task)
    {
        $task->code = 'TK-'.str_pad($task->id, 4, "0", STR_PAD_LEFT).'0'.
            $task->number.date_format($task->created_at,'-y');
        $task->save();
    }

    return 'generation successfully completed';
});
*/

/* Complementary code to fill code columns if empty
Route::get('/generate_codes', function () {

    $empty_coded_records = App\Site::where('code','')->get();

    foreach($empty_coded_records as $record)
    {
        $record->code = 'ST-'.str_pad($record->id, 4, "0", STR_PAD_LEFT).
            date_format($record->created_at,'-y');
        $record->save();
    }

    return 'generation successfully completed';
});
*/

/* Function to generate thumbnails of previously uploaded images

use Intervention\Image\ImageManagerStatic as Image;

Route::get('/generate_thumbs', function (){

    $files = App\File::whereIn('type', ['jpg', 'jpeg', 'png'])->get();
    $count = 0;

    foreach($files as $file){

        if (file_exists($file->path.$file->name)) {
            if (!file_exists($file->path.'thumbnails/thumb_'.$file->name)) {

                $image = Image::make($file->path.$file->name);

                if($image->height()>$image->width())
                    $image->heighten(200);
                else
                    $image->widen(200);

                $image->orientate();
                $success = $image->save(public_path('files/thumbnails/thumb_' .$file->name));

                if($success){
                    $thumbnail = new App\Thumbnail;
                    $thumbnail->name = 'thumb_'.$file->name;
                    $thumbnail->org_name = $file->name;
                    $thumbnail->path = public_path().'/files/thumbnails/';
                    $thumbnail->type = $image->mime();
                    $thumbnail->size = $image->filesize()/1024;
                    $thumbnail->save();

                    $count++;
                }
            }
        }
    }

    return 'Se crearon '.$count.' thumbnails';
});
*/

/* Function for filling recently added authorization columns on o_c_s table
Route::get('/fill_auth_ocs', function (){

    $ocs = App\OC::where('auth_tec_code','')->where('auth_ceo_code','')->get();
    $count = 0;
    $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
    $longitudCadena = strlen($cadena);

    foreach($ocs as $oc){

        $creator = App\User::find($oc->user_id);

        if($oc->flags[0]==0){
            if(($creator->priv_level==3&&$creator->area=='Gerencia General')||$creator->priv_level==4){
                $oc->auth_tec_date = $oc->created_at;
                $oc->auth_ceo_date = $oc->created_at;

                $code = "";

                for($i=1 ; $i<=10 ; $i++){
                    $pos = rand(0,$longitudCadena-1);
                    $code .= substr($cadena,$pos,1);
                }

                $oc->auth_tec_code = $code;

                $code = "";

                for($i=1 ; $i<=10 ; $i++){
                    $pos = rand(0,$longitudCadena-1);
                    $code .= substr($cadena,$pos,1);
                }

                $oc->auth_ceo_code = $code;
            }
            elseif($creator->priv_level==3&&$creator->area=='Gerencia Tecnica'){
                $oc->auth_tec_date = $oc->created_at;

                $code = "";

                for($i=1 ; $i<=10 ; $i++){
                    $pos = rand(0,$longitudCadena-1);
                    $code .= substr($cadena,$pos,1);
                }

                $oc->auth_tec_code = $code;

                if($oc->flags[1]==1){
                    $oc->auth_ceo_date = $oc->updated_at;

                    $code = "";

                    for($i=1 ; $i<=10 ; $i++){
                        $pos = rand(0,$longitudCadena-1);
                        $code .= substr($cadena,$pos,1);
                    }

                    $oc->auth_ceo_code = $code;
                }
            }
            else{
                if($oc->flags[2]==1){
                    $oc->auth_tec_date = $oc->created_at;

                    $code = "";

                    for($i=1 ; $i<=10 ; $i++){
                        $pos = rand(0,$longitudCadena-1);
                        $code .= substr($cadena,$pos,1);
                    }

                    $oc->auth_tec_code = $code;
                }
                if($oc->flags[1]==1){
                    $oc->auth_ceo_date = $oc->created_at;

                    $code = "";

                    for($i=1 ; $i<=10 ; $i++){
                        $pos = rand(0,$longitudCadena-1);
                        $code .= substr($cadena,$pos,1);
                    }

                    $oc->auth_ceo_code = $code;
                }
            }

            $oc->save();
            $count++;
        }
    }

    return 'Se completaron '.$count.' registros';
});
*/

/* Function for filling start-line and deadline columns added to sites table
Route::get('fill_sites_interval', function(){
    $sites = App\Site::where('deadline', 0)->get();
    $count = 0;

    foreach($sites as $site){
        $site->start_line = $site->start_date;
        $site->deadline = $site->end_date;

        $site->save();
        $count++;
    }

    return 'Ejecutados '.$count.' cambios';
});
*/

/*
Route::get('assig_stat_to_int', function(){
    $assignments = App\Assignment::all();

    foreach($assignments as $assignment){
        if(!is_numeric($assignment->status)){
            if($assignment->status=='Relevamiento')
                $assignment->status = 1;
            elseif($assignment->status=='Cotizado')
                $assignment->status = 2;
            elseif($assignment->status=='Ejecución')
                $assignment->status = 3;
            elseif($assignment->status=='Revisión')
                $assignment->status = 4;
            elseif($assignment->status=='Cobro')
                $assignment->status = 7;
            elseif($assignment->status=='Concluído')
                $assignment->status = 8;
            else
                $assignment->status = 0;

            $assignment->save();
        }
    }

    echo 'Translation completed';
});

Route::get('site_stat_to_int', function(){
    $sites = App\Site::all();

    foreach($sites as $site){
        if(!is_numeric($site->status)){
            if($site->status=='Relevamiento')
                $site->status = 1;
            elseif($site->status=='Cotizado')
                $site->status = 2;
            elseif($site->status=='Ejecución')
                $site->status = 3;
            elseif($site->status=='Revisión')
                $site->status = 4;
            elseif($site->status=='Cobro')
                $site->status = 7;
            elseif($site->status=='Concluído')
                $site->status = 8;
            else
                $site->status = 0;

            $site->save();
        }
    }

    echo 'Translation completed';
});

Route::get('task_stat_to_int', function(){
    $tasks = App\Task::all();

    foreach($tasks as $task){
        if(!is_numeric($task->status)){
            if($task->status=='En espera')
                $task->status = 1;
            elseif($task->status=='Ejecución')
                $task->status = 3;
            elseif($task->status=='Revisión')
                $task->status = 4;
            elseif($task->status=='Concluído')
                $task->status = 8;
            else
                $task->status = 0;

            $task->save();
        }
    }

    echo 'Translation completed';
});
*/
