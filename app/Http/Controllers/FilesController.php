<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Mail;
use Exception;
use App\Cite;
use App\OC;
use App\File;
use App\Project;
use App\Event;
use App\Site;
use App\Assignment;
use App\Order;
use App\Activity;
use App\Guarantee;
use App\Vehicle;
use App\Device;
use App\Operator;
use App\Driver;
use App\Maintenance;
use App\User;
use App\Contract;
use App\Invoice;
use App\Email;
// use App\VehicleHistory;
use App\DeviceHistory;
use App\Material;
use App\Warehouse;
use App\WarehouseEntry;
use App\WarehouseOutlet;
use App\Thumbnail;
use App\Calibration;
use App\CorpLine;
use App\CorpLineAssignation;
use App\DeadInterval;
use App\OcCertification;
use App\VhcFailureReport;
use App\DvcFailureReport;
use App\Employee;
use App\Tender;
use App\RendicionRespaldo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Response;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\ImageManagerStatic as Image;
use Validator;
use App\Http\Traits\ActiveTrait;

class FilesController extends Controller
{
    use ActiveTrait;

    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = session('service');
        $files = File::orderBy('created_at','desc')->paginate(20);
        
        return View::make('app.file_brief', ['files' => $files, 'service' => $service, 'user' => $user]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        $service = Session::get('service');

        $file = File::find($id);

        return View::make('app.file_info', ['file' => $file, 'service' => $service, 'user' => $user]);
    }

    public function download_file($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        if($id=='ct-0')
            $file = File::where('imageable_id', 0)->where('name','like','Formato_CITE%')->first();
        elseif($id=='dr-0')
            $file = File::where('imageable_id', 0)->where('name','like','driver_form%')->first();
        else
            $file = File::find($id);

        if(!empty($file))
            return response()->download($file->path.$file->name);
        else{
            Session::flash('message', "No se ha encontrado el archivo en el servidor. Si el problema persiste
                por favor contáctese con el administrador");
            return redirect()->back();
        }
    }

    public function display($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        if ($id == 0)
            $file = File::where('imageable_id', 0)->where('type', 'pdf')->firstOrFail();
        else
            $file = File::find($id);

        return Response::make(file_get_contents($file->path.$file->name), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$file->name.'"'
        ]);
    }

    public function delete_form($type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        return View::make('app.file_form', ['id' => 0, 'type' => $type, 'options' => '', 'service' => $service,
            'delete_flag' => 1]);
    }

    public function delete_file(Request $request, $type)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $file_name = $request->input('file_name');
        $file = File::where('name', $file_name)->first();

        /*
        if($type=='project'){
            try {
                //$delete_file = File::where('name', 'like', $file_name)->firstOrFail()->name;
                $delete_file = $file->name;
                \Storage::disk('local')->delete($delete_file);
                $exito=$exito+1;
            } catch (ModelNotFoundException $ex) {
                $exito = 0;
            }

            if ($exito>0) {
                $phase_of_file = $request->input('phase_of_file');

                $project = Project::find($file->imageable_id);

                if($phase_of_file==1)
                    $project->asig_file_id = 0;
                elseif($phase_of_file==2)
                    $project->quote_file_id = 0;
                elseif($phase_of_file==3)
                    $project->pc_org_id = 0;
                elseif($phase_of_file==4)
                    $project->pc_sgn_id = 0;
                elseif($phase_of_file==5)
                    $project->matsh_org_id = 0;
                elseif($phase_of_file==6)
                    $project->matsh_sgn_id = 0;
                elseif($phase_of_file==7)
                    $project->costsh_org_id = 0;
                elseif($phase_of_file==8)
                    $project->costsh_sgn_id = 0;
                elseif($phase_of_file==9)
                    $project->qcc_file_id = 0;

                $project->save();
                $file->delete();

                Session::flash('message', " Archivo eliminado del sistema ");
                return redirect()->route($service.'.index');
            }
            else {
                Session::flash('message', " El archivo no existe o no se puede borrar ");
                return redirect()->back();
            }
        }
        */

        if ($file) {
            $success = true;

            try {
                //$delete_file = File::where('name', 'like', $file_name)->firstOrFail()->name;
                \Storage::disk('local')->delete($file->name);
            } catch (Exception $ex) {
                // ModelNotFoundException $ex
                $success = false;
            }

            if ($success) {
                $file->delete();

                Session::flash('message', "Archivo eliminado del sistema");

                /*
                if($type=='event'){
                    $event = Event::find($file_info->imageable_id);
                    return redirect()->action('EventController@show', ['id' => $event->project_id,
                        'name' => str_replace(" ", "_", $event->project_site)]);
                }
                */
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route($service.'.index');
            }
            else {
                Session::flash('message', "El archivo no existe o no se puede borrar!");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "El archivo no existe o no se puede borrar!");
            return redirect()->back();
        }
    }

    public function form_to_upload($id, $type)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $options = '';

        if (is_numeric($type)) {
            $aux = $type;
            $type = $id;
            $id = $aux;
        }

        if ($type == 'format' || $type == 'driver_form') {
            $id = 0;
        }
        if ($type == 'assignment' || $type == 'site') {
            $file_qcc = $file_ctz = $file_sch = $file_asig = $file_qty_org = $file_qty_sgn = $file_cst_org =
            $file_cst_sgn = false;

            if ($type == 'assignment')
                $model = Assignment::find($id);
            else
                $model = Site::find($id); // if type equals 'site'

            foreach($model->files as $file){

                if(strpos($file, 'qcc')!==false)
                    $file_qcc =  true;
                if(strpos($file, 'ctz')!==false)
                    $file_ctz =  true;
                if(strpos($file, 'sch')!==false)
                    $file_sch = true;
                if(strpos($file, 'asig')!==false)
                    $file_asig = true;
                if(strpos($file, 'qty_org')!==false)
                    $file_qty_org = true;
                if(strpos($file, 'qty_sgn')!==false)
                    $file_qty_sgn = true;
                if(strpos($file, 'cst_org')!==false)
                    $file_cst_org = true;
                if(strpos($file, 'cst_sgn')!==false)
                    $file_cst_sgn = true;
            }

            //$file_qcc = File::where('imageable_type','App\Assignment')->where('imageable_id', $id)
            //                ->where('name','like','%qcc%')->first();

            $options .= !$file_qcc ? '<option value="qcc">Certificado de control de calidad</option>' : '';
            $options .= !$file_ctz ? '<option value="ctz">Cotización</option>' : '';
            $options .= !$file_sch ? '<option value="sch">Cronograma</option>' : '';
            $options .= !$file_asig ? '<option value="asig">Documento de asignación</option>' : '';
            $options .= !$file_qty_org ? '<option value="qty_org">Planilla de cantidades original</option>' : '';
            $options .= !$file_qty_sgn ? '<option value="qty_sgn">Planilla de cantidades firmada</option>' : '';
            $options .= !$file_cst_org ? '<option value="cst_org">Planilla económica original</option>' : '';
            $options .= !$file_cst_sgn ? '<option value="cst_sgn">Planilla económica firmada</option>' : '';
            $options .= '<option value="Otro">Otro</option>';
        }
        /*
        if($type=='project'){
            $status = Project::find($id)->status;
            return view::make('app.project_files_form', ['id' => $id, 'status' => $status, 'service' => $service,
                'project' => 0]);
        }
        if($type=='proj_rmp'){
            $project = Project::find($id);

            $project->ini_date = Carbon::parse($project->ini_date)->format('Y-m-d');
            $project->bill_date = Carbon::parse($project->bill_date)->format('Y-m-d');

            $status = $project->status-1;
            return view::make('app.project_files_form', ['id' => $id, 'status' => $status, 'service' => $service,
                'project' => $project]);
        }
        if($type=='schedule'){
            return view::make('app.project_files_form', ['id' => $id, 'status' => 13, 'service' => $service,
                'project' => 0]);
        }
        if($type=='warranty'){
            return view::make('app.project_files_form', ['id' => $id, 'status' => 14, 'service' => $service,
                'project' => 0]);
        }
        if($id=='assignment'){
            $id = $type;
            $type = 'assignment';
        }
        if($id=='site'){
            $id = $type;
            $type = 'site';
        }
        */

        return View::make('app.file_form', ['id' => $id, 'type' => $type, 'options' => $options, 'service' => $service,
            'delete_flag' => 0, 'user' => $user]);
    }

    public function uploader(Request $request, $id, $type)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        if (is_numeric($type)) {
            $aux = $type;
            $type = $id;
            $id = $aux;
        }

        $newFile = $request->file('file');
        $current_date = Carbon::now()->format('ymdhis');

        if (!$request->hasFile('file')) {
            Session::flash('message', "No se seleccionó ningún archivo o tamaño de archivo superior al permitido!");
            return redirect()->back()->withInput();
        }

        if ($type == 'activity') {
            $activity = Activity::find($id);

            $v = $this->check_extension('all', $request->file());
            /*
            $v = \Validator::make($request->file(), [
                'file' => 'mimes:pdf,docx,doc,xls,xlsx,jpg,jpeg,png',
            ]);
            */

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'ACF-'.str_pad($activity->id, 4, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$activity);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('ActivityController@activities_per_task', ['id' => $activity->task_id]);
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'assignment') {
            $assignment = Assignment::find($id);

            $filename_hint = $request->input('name_of_file');

            if($filename_hint==''){
                Session::flash('message', "Seleccione el tipo de archivo que desea subir!");
                return redirect()->back()->withInput();
            }

            if($filename_hint=='asig'||$filename_hint=='qty_sgn'||$filename_hint=='cst_sgn'||$filename_hint=='qcc') {

                $v = $this->check_extension('pdf', $request->file());

                $message = "Tipo de archivo no soportado! el archivo debe ser PDF";
            }
            elseif($filename_hint=='ctz'||$filename_hint=='sch'||$filename_hint=='qty_org'||$filename_hint=='cst_org'){

                $v = $this->check_extension('xls', $request->file());

                $message = "Tipo de archivo no soportado! el archivo debe ser EXCEL";
            }
            else{
                //if($filename_hint=='Otro')
                $v = $this->check_extension('all', $request->file());

                $message = "Tipo de archivo no soportado!";
            }

            if ($v->fails()) {
                Session::flash('message', $message);
                return redirect()->back()->withInput();
            }

            $name = $filename_hint=='Otro' ? 'ASG_'.$id.$current_date : 'ASG_'.$id.'_'.$filename_hint;
            /* old code for deleting previous file with the same name
            $success = 0;
            try {
                $del_prevfile = File::where('imageable_id', $id)->where('imageable_type','App\Assignment')
                    ->where('name', 'like', $name.'%')
                    ->firstOrFail()->name;
                \Storage::disk('local')->delete($del_prevfile);
                $success++;
            } catch (ModelNotFoundException $ex) {
                $success = 0;
            }
            if ($success > 0) {
                $erase_file = File::where('imageable_id', $id)->where('imageable_type','App\Assignment')
                    ->where('name', 'like', $name.'%')
                    ->firstOrFail();
                $erase_file->delete();
            }
            */
            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = $name.'.'.strtolower($FileType);
            $FileDescription = $filename_hint=='Otro' ? $request->input('other_description') : $request->input('description');
            $FileDescription = $FileDescription ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$assignment);

                /*
                if($filename_hint=='wty'){
                    $guarantee = new Guarantee;

                    $guarantee->user_id = $user->id;
                    $guarantee->assignment_id = $id;
                    $guarantee->expiration_date = $request->input('expiration_date');

                    $guarantee->save();
                }
                */
                if ($filename_hint == 'qcc') {
                    /* send email */
                    $this->send_mail('qcc_assignment', $assignment, $user);
                }

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('assignment.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'calibration') {
            $calibration = Calibration::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'CBR-DVF'.str_pad($calibration->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$calibration);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('calibration.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'cite') {
            $cite = Cite::find($id);

            $v = \Validator::make($request->file(), [
                'file' => 'mimes:pdf,doc,docx',
            ]);

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            //$FilePath = public_path().'\files'.'\\';         problem with \ and /
            $FilePath = public_path() . '/files/';            // works well on both cases
            //$FileName = $cite->title.'-'.str_pad($cite->num_cite,3,"0",STR_PAD_LEFT).
            //    date_format($cite->created_at,'-Y').'.'.$FileType;
            $FileName = $cite->code.'.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$cite);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                //en $FilePath el id para DB es $cite_id
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('cite.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'corp_line') {
            $line = CorpLine::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'CLF-'.str_pad($line->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ?: $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$line);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('corporate_line.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        //Contracts merged with Projects by General Manager request (contract functions disabled or merged with project functions)
        /*
        if ($type == 'contract') {
            $contract = Contract::find($id);

            $v = $this->check_extension('all', $request->file());
            /*
            $v = \Validator::make($request->file(), [
                'file' => 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            ]);

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'CTF-'.str_pad($contract->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$contract);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('contract.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }
        */

        if ($type == 'dead_interval') {
            $dead_interval = DeadInterval::find($id);

            $v = $this->check_extension('all', $request->file());
            /*
            $v = \Validator::make($request->file(), [
                'file' => 'mimes:pdf,docx,doc,xls,xlsx,jpg,jpeg,png',
            ]);
            */

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'DIF-'.str_pad($dead_interval->id, 4, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ?: $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if(file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$dead_interval);

                Session::flash('message', "El archivo ha sido almacenado en el sistema");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                elseif($dead_interval->relatable_type=='App\Assignment')
                    return redirect('/dead_interval?assig_id='.$dead_interval->relatable_id);
                elseif($dead_interval->relatable_type=='App\Site')
                    return redirect('/dead_interval?st_id='.$dead_interval->relatable_id);
                else
                    return redirect()->route('assignment.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'device_file') {
            $device = Device::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'DVF-'.str_pad($device->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $file = $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$device);

                /* insert new entry on device history table */
                $this->add_history_record('device_file', $device, $file, $user);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('device.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'device_img') {
            $device = Device::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'DVP-'.str_pad($device->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            /* Fix orientation of the picture */
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                /* Check if the file's size changed with orientation command */
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                /* Create a record for the file storage on DB */
                $file = $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$device);

                /* Create thumbnail of the image */
                $this->create_thumbnail($newFile,$FileName);

                if($request->input('main_pic')==1){
                    $device->main_pic_id = $file->id;
                    $device->save();
                }

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('device.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'driver') {
            $driver = Driver::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'DRP-'.str_pad($driver->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            /* Fix orientation of the picture */
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                /* Check if the file's size changed with orientation command */
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                /* Create a record for the file storage on DB */
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$driver);

                /* Create thumbnail of the image */
                $this->create_thumbnail($newFile,$FileName);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('driver.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'driver_form') {
            /*Store driver receipt form format to print*/

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! el archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            //$deleted = false;
            try {
                $del_prev_file = File::where('imageable_id', 0)->where('name', 'driver_form.pdf')
                    ->firstOrFail()->name;
                \Storage::disk('local')->delete($del_prev_file);
                $deleted = true;
            } catch (ModelNotFoundException $ex) {
                $deleted = false;
            }
            if ($deleted) {
                $erase_file = File::where('imageable_id', 0)->where('name', 'driver_form.pdf');
                $erase_file->delete();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'driver_form.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $file = new File;
                $file->name = $FileName;
                $file->path = $FilePath;
                $file->type = strtolower($FileType);
                $file->size = $FileSize;
                $file->imageable_id = 0;

                $file->user_id = $user->id;
                $file->description = $FileDescription;

                $file->save();

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('driver.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'driver_receipt') {
            $driver = Driver::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'DRFR-'.str_pad($driver->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$driver);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('driver.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'dvc_failure_report') {
            $report = DvcFailureReport::find($id);

            $v = $this->check_extension('all', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = 'DFR-'.str_pad($report->id, 4, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ?: $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$report);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect('/device_failure_report?dvc='.$report->device_id);
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }
        
        if ($type == 'employee_img') {
            $employee = Employee::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'EMP-'.str_pad($employee->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            /* Fix orientation of the picture */
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                /* Check if the file's size changed with orientation command */
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                /* Create a record for the file storage on DB */
                $file = $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$employee);

                /* Create thumbnail of the image */
                $this->create_thumbnail($newFile,$FileName);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if (Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('employee.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'event') {
            $event = Event::find($id);

            $v = $this->check_extension('all', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = 'EV-'.str_pad($event->eventable_id, 4, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ?: $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$event);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                /*return redirect()->action('EventController@show', ['id' => $event->project_id,
                    'name' => str_replace(" ", "_", $event->project_site)]);*/
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                elseif($event->eventable_type=='App\Assignment')
                    return redirect()->action('EventController@events_per_type',
                        ['type'=>'assignment', 'id'=>$event->eventable_id]);
                elseif($event->eventable_type=='App\Site')
                    return redirect()->action('EventController@events_per_type',
                        ['type'=>'site', 'id' => $event->eventable_id]);
                elseif($event->eventable_type=='App\OC')
                    return redirect()->action('EventController@events_per_type',
                        ['type'=>'oc', 'id' => $event->eventable_id]);
                elseif($event->eventable_type=='App\Invoice')
                    return redirect()->action('EventController@events_per_type',
                        ['type'=>'invoice', 'id' => $event->eventable_id]);
                elseif($service=='project')
                    return redirect()->route('assignment.index');
                else
                    return redirect()->route($service.'.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'format') {
            $v = $this->check_extension('doc', $request->file());
            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser WORD");
                return redirect()->back()->withInput();
            }

            //$deleted = false;
            try {
                $del_prev_file = File::where('imageable_id', 0)->where('name', 'like', '%Formato_CITE%')
                    ->firstOrFail()->name;
                \Storage::disk('local')->delete($del_prev_file);
                $deleted = true;
            } catch (ModelNotFoundException $ex) {
                $deleted = false;
            }
            if ($deleted) {
                $erase_file = File::where('imageable_id', 0)->where('name', 'like', '%Formato_CITE%');
                $erase_file->delete();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = 'Formato_CITE.' . strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $file = new File;
                $file->name = $FileName;
                $file->path = $FilePath;
                $file->type = strtolower($FileType);
                $file->size = $FileSize;
                $file->imageable_id = 0;

                $file->user_id = $user->id;
                $file->description = $FileDescription;

                $file->save();

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('cite.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back();
            }
        }

        if ($type == 'guarantee') {
            $guarantee = Guarantee::find($id);

            $v = $this->check_extension('all', $request->file());
            /*
            $v = \Validator::make($request->file(), [
                'file' => 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            ]);
            */

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'WTYF'.str_pad($guarantee->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$guarantee);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('guarantee.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'invoice') {
            $invoice = Invoice::find($id);

            $v = \Validator::make($request->file(), [
                'file' => 'mimes:pdf,jpeg,jpg,png',
            ]);

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! el archivo debe ser PDF o imagen");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'IEF-'.str_pad($invoice->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$invoice);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('invoice.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'line_assignation') {
            $assignation = CorpLineAssignation::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'LAF-'.str_pad($assignation->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ?: $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$assignation);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('line_assignation.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'maintenance') {
            $maintenance = Maintenance::find($id);

            $v = \Validator::make($request->file(), [
                'file' => 'mimes:pdf,jpg,jpeg,png',
            ]);
            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! Seleccione un PDF o una imagen");
                return redirect()->back()->withInput();
            }

            $current_date = Carbon::now()->format('ymd');

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = 'MTR-'.str_pad($maintenance->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            /*
            $exito = 0;
            try {
                $del_prevfile = File::where('imageable_id', $id)->where('imageable_type', 'App\Maintenance')
                    ->where('name', 'like', $FileName.'%')
                    ->firstOrFail()->name;
                \Storage::disk('local')->delete($del_prevfile);
                $exito = $exito + 1;
            } catch (ModelNotFoundException $ex) {
                $exito = 0;
            }
            if ($exito > 0) {
                $erase_file = File::where('imageable_id', $id)->where('imageable_type', 'App\Maintenance')
                    ->where('name', 'like', $FileName.'%')
                    ->firstOrFail();
                $erase_file->delete();
            }
            */
            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$maintenance);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('maintenance.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'oc_certificate') {
            $certificate = OcCertification::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'CTDF-OC'.str_pad($certificate->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$certificate);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('oc_certificate.index');
                //return redirect()->action('OcCertificationController@show', ['id'=>$certificate->id]);
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'oc_certification_backup') {

            $certificate = OcCertification::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'CTD-BK-OC'.str_pad($certificate->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$certificate);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('oc_certificate.index');
                //return redirect()->action('OcCertificationController@show', ['id'=>$certificate->id]);
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'oc_org' || $type == 'oc_sgn') {
            $oc = OC::find($id);

            if ($type == 'oc_org') {
                $v = \Validator::make($request->file(), [
                    'file' => 'mimes:pdf,xls,xlsx',
                ]);
            } else {  //($type=='oc_sgn')
                $v = $this->check_extension('pdf', $request->file());
            }

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }
            /*
            if($type=='oc_org'){
                $exito = 0;
                try {
                    $del_prevfile = File::where('imageable_id', $id)->where('imageable_type', 'App\OC')
                        ->where('name', 'like', 'OC_' . $id . '_org.%')
                        ->firstOrFail()->name;
                    \Storage::disk('local')->delete($del_prevfile);
                    $exito = $exito + 1;
                } catch (ModelNotFoundException $ex) {
                    $exito = 0;
                }
                if ($exito > 0) {
                    $erase_file = File::where('imageable_id', $id)->where('imageable_type', 'App\OC')
                        ->where('name', 'like', 'OC_' . $id . '_org.%')
                        ->firstOrFail();
                    $erase_file->delete();
                }
            }
            */
            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = 'provisional'.$FileType;
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if($type=='oc_org')
                $FileName = 'OC_' . $oc->id . '_org.' . strtolower($FileType);
            elseif($type=='oc_sgn')
                $FileName = 'OC_' . $oc->id . '_sgn.' . strtolower($FileType);

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $file = $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$oc);

                // Store updated amount to OC
                $this->update_oc($file, $oc, $type, $newFile->getRealPath());

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('oc.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'operator') {
            $operator = Operator::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'OPP-'.str_pad($operator->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            /* Fix orientation of the picture */
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                /* Check if the file's size changed with orientation command */
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                /* Create a record for the file storage on DB */
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$operator);

                /* Create thumbnail of the image */
                $this->create_thumbnail($newFile,$FileName);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('operator.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'operator_receipt') {
            $operator = Operator::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'OPFR-'.str_pad($operator->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$operator);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('operator.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'order') {
            $order = Order::find($id);
            $filename_hint = $request->input('name_of_file');

            if($filename_hint==''){
                Session::flash('message', "Seleccione el tipo de archivo que desea subir!");
                return redirect()->back()->withInput();
            }

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $name = 'RDR_'.$id.'_'.$filename_hint;
            /*
            $success = 0;
            try {
                $del_prevfile = File::where('imageable_id', $id)->where('imageable_type', 'App\Order')
                    ->where('name', 'like', $name.'%')
                    ->firstOrFail()->name;
                \Storage::disk('local')->delete($del_prevfile);
                $success = $success + 1;
            } catch (ModelNotFoundException $ex) {
                $success = 0;
            }
            if ($success > 0) {
                $erase_file = File::where('imageable_id', $id)->where('imageable_type', 'App\Order')
                    ->where('name', 'like', $name.'%')
                    ->firstOrFail();
                $erase_file->delete();
            }
            */
            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = $name.'.'.strtolower($FileType);

            if($request->input('description')=='Orden original')
                $FileDescription = $order->type.' original';
            elseif($request->input('description')=='Orden firmada')
                $FileDescription = $order->type.' firmado';
            else
                $FileDescription = $request->input('description');

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$order);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('order.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'project') {
            $project = Project::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'PRJ-'.str_pad($project->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$project);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('project.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }
        
        if ($type == 'rendicion_respaldo') {
            $respaldo = RendicionRespaldo::find($id);

            $v = $this->check_extension('image-pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            // Rendicion Respaldo FIle
            $FileName = 'RRF-'.str_pad($respaldo->id, 5, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$respaldo);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('RendicionRespaldoController@show', ['id' => $respaldo->rendicion->id]);
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'replace') {
            $mime = File::find($id)->type;
            $status = File::find($id)->status;
            $blocked = $status==1 ? true : false;

            if ($blocked && $user->priv_level < 4) {
                Session::flash('message', 'Este archivo ha sido bloqueado y no puede ser modificado!');
                return redirect()->back()->withInput();
            }

            if ($mime=='jpg'||$mime=='jpeg'||$mime=='png') {
                $v = \Validator::make($request->file(), [
                    'file' => 'mimes:jpg,jpeg,png',
                ]);
            }
            else {
                $v = \Validator::make($request->file(), [
                    'file' => 'mimes:'.$mime,
                ]);
            }

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no permitido! 
                        Debe seleccionar un archivo con el mismo formato que el archivo actual");
                return redirect()->back()->withInput();
            }

            try {
                $del_prev_file = File::find($id)->name;
                \Storage::disk('local')->delete($del_prev_file);
                $deleted = true;
            } catch (ModelNotFoundException $ex) {
                $deleted = false;
            }

            if($deleted) {
                $update_file = File::find($id);

                $FileType = $newFile->getClientOriginalExtension();
                $FileSize = $newFile->getClientSize() / 1024;
                $FilePath = public_path() . '/files/';
                $FileName = $update_file->name;
                $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

                if($FileType=='jpg'||$FileType=='jpeg'||$FileType=='png'){
                    /* Fix orientation of the picture */
                    $image = Image::make($newFile->getRealPath());
                    $image->orientate();

                    $upload = $image->save(public_path('files/' .$FileName));

                    /* Check if the file's size changed with orientation command */
                    $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;
                }
                else
                    $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

                if ($upload) {
                    if($mime=='jpg'||$mime=='jpeg'||$mime=='png'){
                        /* Replace thumbnail of the image */
                        $this->replace_thumbnail($newFile, $FileName);
                    }

                    $update_file->name = $FileName;
                    $update_file->path = $FilePath;
                    $update_file->type = strtolower($FileType);
                    $update_file->size = $FileSize;
                    $update_file->description = $FileDescription;

                    $update_file->user_id = $user->id;

                    $update_file->save();

                    // Store updated amount to OC
                    if(($update_file->type=='xls'||$update_file->type=='xlsx')&&
                        $update_file->imageable_type=='App\OC')
                    {
                        $oc = OC::find($update_file->imageable_id);
                        Excel::load($newFile->getRealPath(), function ($reader) use ($oc) {
                            $sheet = $reader->getSheetByName('OC');
                            $oc->oc_amount = $sheet->getCell('AR62')->getCalculatedValue();
                            $oc->save();
                        });
                    }

                    if(strpos($update_file, 'VGI')!==false&&$update_file->imageable_type=='App\Vehicle'){
                        $vehicle = $update_file->imageable;

                        $vehicle->gas_inspection_exp = $request->input('exp_date');
                        $vehicle->save();
                    }

                    Session::flash('message', "El archivo $FileName ha sido reemplazado");

                    if(Session::has('url'))
                        return redirect(Session::get('url'));
                    elseif($update_file->imageable_type=='App\Assignment')
                        return redirect()->action('AssignmentController@show', ['id'=>$update_file->imageable_id]);
                    elseif($update_file->imageable_type=='App\Site')
                        return redirect()->action('SiteController@show', ['id' => $update_file->imageable_id]);
                    elseif($update_file->imageable_type=='App\Order')
                        return redirect()->action('OrderController@show', ['id' => $update_file->imageable_id]);
                    elseif($service=='project')
                        return redirect()->route('assignment.index');
                    //return redirect()->action('SiteController@index');
                    else
                        return redirect()->route($service.'.index');

                } else {
                    Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                    return redirect()->back()->withInput();
                }
            }
        }

        if ($type == 'site') {
            $site = Site::find($id);

            $filename_hint = $request->input('name_of_file');

            if($filename_hint==''){
                Session::flash('message', "Seleccione el tipo de archivo que desea subir!");
                return redirect()->back()->withInput();
            }

            if($filename_hint=='asig'||$filename_hint=='qty_sgn'||$filename_hint=='cst_sgn'||$filename_hint=='qcc'){

                $v = $this->check_extension('pdf', $request->file());

                $flash_message = "Tipo de archivo no soportado! el archivo debe ser PDF";
            }
            elseif($filename_hint=='ctz'||$filename_hint=='sch'||$filename_hint=='qty_org'||$filename_hint=='cst_org'){

                $v = $this->check_extension('xls', $request->file());

                $flash_message = "Tipo de archivo no soportado! el archivo debe ser EXCEL";
            }
            else{
                //($filename_hint=='Otro')
                $v = $this->check_extension('all', $request->file());

                $flash_message = "Tipo de archivo no soportado!";
            }

            if ($v->fails()) {
                Session::flash('message', $flash_message);
                return redirect()->back()->withInput();
            }

            $name = $filename_hint=='Otro' ? 'ST_'.$id.$current_date : 'ST_'.$id.'_'.$filename_hint;
            /*
            $success = 0;
            try {
                $del_prevfile = File::where('imageable_id', $id)->where('imageable_type', 'App\Site')
                    ->where('name', 'like', $name.'%')
                    ->firstOrFail()->name;
                \Storage::disk('local')->delete($del_prevfile);
                $success++;
            } catch (ModelNotFoundException $ex) {
                $success = 0;
            }
            if ($success > 0) {
                $erase_file = File::where('imageable_id', $id)->where('imageable_type', 'App\Site')
                    ->where('name', 'like', $name.'%')
                    ->firstOrFail();
                $erase_file->delete();
            }
            */
            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = $name.'.'.strtolower($FileType);
            $FileDescription = $filename_hint=='Otro' ? $request->input('other_description') : $request->input('description');
            $FileDescription = $FileDescription ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$site);

                if($filename_hint=='qcc'){
                    /* send email */
                    $this->send_mail('qcc_site', $site, $user);
                }

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->action('SiteController@sites_per_project', ['id' => $site->assignment_id]);
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'tender') {
            $tender = Tender::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'LCT-'.str_pad($tender->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$tender);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('tender.index');
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'vehicle_file' || $type == 'vhc_gas_inspection') {
            $vehicle = Vehicle::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';

            if ($type == 'vhc_gas_inspection')
                $FileName = 'VGI-'.str_pad($vehicle->id, 3, "0", STR_PAD_LEFT).'-'.
                    $current_date.'.'.strtolower($FileType);
            else
                $FileName = 'VHF-'.str_pad($vehicle->id, 3, "0", STR_PAD_LEFT).'-'.
                    $current_date.'.'.strtolower($FileType);

            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $file = $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$vehicle);

                /* insert new entry on vehicle history table */
                // $this->add_history_record('vehicle_file', $vehicle, $file, $user);
                $this->add_vhc_history_record($vehicle, $file, 'vehicle_file', $user, 'file');

                if($type=='vhc_gas_inspection') {
                    $vehicle->gas_inspection_exp = $request->input('exp_date');
                    $vehicle->save();
                }

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('vehicle.index');
                //return redirect()->action('VehicleController@show', ['id' => $id]);
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'vehicle_img') {
            $vehicle = Vehicle::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'VHP-'.str_pad($vehicle->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            /* Fix orientation of the picture */
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                /* Check if the file's size changed with orientation command */
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                /* Create a record for the file storage on DB */
                $file = $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$vehicle);

                /* Create thumbnail of the image */
                $this->create_thumbnail($newFile,$FileName);

                if($request->input('main_pic')==1){
                    $vehicle->main_pic_id = $file->id;
                    $vehicle->save();
                }

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('vehicle.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'vhc_failure_report') {
            $report = VhcFailureReport::find($id);

            $v = $this->check_extension('all', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path() . '/files/';
            $FileName = 'VFR-'.str_pad($report->id, 4, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ?: $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath . $FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$report);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect('/vehicle_failure_report?vhc='.$report->vehicle_id);
            } else {
                Session::flash('message', "Error al cargar el archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        //Obsolete functions for warehouse module (no longer in use)
        /*
        if ($type == 'warehouse_img') {
            $warehouse = Warehouse::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'WHI-'.str_pad($warehouse->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            // Fix orientation of the picture
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                // Check if the file's size changed with orientation command
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                // Create a record for the file storage on DB
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$warehouse);

                // Create thumbnail of the image
                $this->create_thumbnail($newFile,$FileName);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('warehouse.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'warehouse_file') {
            $warehouse = Warehouse::find($id);

            $v = $this->check_extension('pdf', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'WHD-'.str_pad($warehouse->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if(file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$warehouse);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('warehouse.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'material_img') {
            $material = Material::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'MTI-'.str_pad($material->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if(file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            // Fix orientation of the picture
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if($upload) {
                // Check if the file's size changed with orientation command
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                // Create a record for the file storage on DB
                $file = $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$material);

                // Create thumbnail of the image
                $this->create_thumbnail($newFile,$FileName);

                if($request->input('main_pic')==1){
                    $material->main_pic_id = $file->id;
                    $material->save();
                }

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('material.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'wh_entry_img') {
            $entry = WarehouseEntry::find($id);

            $v = $this->check_extension('image', $request->file());

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no aceptado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'WEI-'.str_pad($entry->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            // Fix orientation of the picture
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                // Check if the file's size changed with orientation command
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                // Create a record for the file storage on DB
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$entry);

                // Create thumbnail of the image
                $this->create_thumbnail($newFile,$FileName);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('wh_entry.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'wh_entry_receipt') {
            $entry = WarehouseEntry::find($id);

            $v = $this->check_extension('pdf', $request->file());
            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'WER-'.str_pad($entry->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$entry);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('wh_entry.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'wh_outlet_img') {
            $outlet = WarehouseOutlet::find($id);

            $v = $this->check_extension('image', $request->file());
            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado!");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'WOI-'.str_pad($outlet->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            // Fix orientation of the picture
            $image = Image::make($newFile->getRealPath());
            $image->orientate();

            $upload = $image->save(public_path('files/' .$FileName));
            //$upload = \Storage::disk('local')->put($FileName, \File::get($newfile));

            if ($upload) {
                // Check if the file's size changed with orientation command
                $FileSize = $FileSize!=($image->filesize()/1024) ? $image->filesize()/1024 : $FileSize;

                // Create a record for the file storage on DB
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$outlet);

                // Create thumbnail of the image
                $this->create_thumbnail($newFile,$FileName);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('wh_outlet.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }

        if ($type == 'wh_outlet_receipt') {
            $outlet = WarehouseOutlet::find($id);

            $v = $this->check_extension('pdf', $request->file());
            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado! El archivo debe ser PDF");
                return redirect()->back()->withInput();
            }

            $FileType = $newFile->getClientOriginalExtension();
            $FileSize = $newFile->getClientSize() / 1024;
            $FilePath = public_path().'/files/';
            $FileName = 'WOR-'.str_pad($outlet->id, 3, "0", STR_PAD_LEFT).'-'.
                $current_date.'.'.strtolower($FileType);
            //$FileDescription = $request->input('description');
            //$FileDescription = $FileDescription ? $FileDescription : $newFile->getClientOriginalName();
            $FileDescription = $request->input('description') ?: $newFile->getClientOriginalName();

            if (file_exists($FilePath.$FileName)) {
                Session::flash('message', "El archivo ya existe!");
                return redirect()->back()->withInput();
            }

            $upload = \Storage::disk('local')->put($FileName, \File::get($newFile));

            if ($upload) {
                $this->store_file_db($FileName,$FilePath,strtolower($FileType),$FileSize,$FileDescription,$outlet);

                Session::flash('message', "Archivo guardado con nombre $FileName");
                return redirect()->route('wh_outlet.index');
            } else {
                Session::flash('message', "Error al cargar archivo, intente de nuevo por favor");
                return redirect()->back()->withInput();
            }
        }
        */

        /* last resource redirect, when no match is found */
        Session::flash('message', 'No se realizó ninguna acción');
        return redirect()->back()->withInput();
    }

    public function check_extension($extension, $file)
    {
        $mime = '';

        if($extension=='pdf')
            $mime = 'pdf';
        if($extension=='image')
            $mime = 'jpg,jpeg,png';
        if($extension=='xls')
            $mime = 'xls,xlsx';
        if($extension=='doc')
            $mime = 'doc,docx';
        if($extension=='image-pdf')
            $mime = 'pdf,jpg,jpeg,png';
        if($extension=='all') //all formats accepted
            $mime = 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png';

        $v = Validator::make($file, [
            'file' => 'mimes:'.$mime,
        ]);

        return $v;
    }

    public function store_file_db($name,$path,$type,$size,$description,$model)
    {
        $user = Session::get('user');

        $file = new File;
        $file->name = $name;
        $file->path = $path;
        $file->type = strtolower($type);
        $file->size = $size;

        $file->user_id = $user->id;
        $file->description = $description;

        $file->imageable()->associate($model);
        $file->save();

        return $file;
    }

    public function create_thumbnail($newFile, $FileName)
    {
        $image = Image::make($newFile->getRealPath());

        if($image->height()>$image->width())
            $image->heighten(200);
        else
            $image->widen(200);

        $image->orientate();
        $success = $image->save(public_path('files/thumbnails/thumb_' .$FileName));

        if($success){
            $thumbnail = new Thumbnail;
            $thumbnail->name = 'thumb_'.$FileName;
            $thumbnail->org_name = $newFile->getClientOriginalName();
            $thumbnail->path = public_path().'/files/thumbnails/';
            $thumbnail->type = $image->mime();
            $thumbnail->size = $image->filesize()/1024; /* Size stored in Kb */
            $thumbnail->save();
        }
    }

    public function replace_thumbnail($newFile, $FileName)
    {
        $old_thumb = Thumbnail::where('name','thumb_'.$FileName)->first();

        if ($old_thumb) {
            try {
                \Storage::disk('local')->delete('thumbnails/'.$old_thumb->name);
                $deleted = true;
            } catch (ModelNotFoundException $ex) {
                $deleted = false;
            }

            if ($deleted) {
                $image = Image::make($newFile->getRealPath());

                if($image->height()>$image->width())
                    $image->heighten(200);
                else
                    $image->widen(200);

                $image->orientate();
                $success = $image->save(public_path('files/thumbnails/thumb_' .$FileName));

                if($success){
                    $old_thumb->name = 'thumb_'.$FileName;
                    $old_thumb->org_name = $newFile->getClientOriginalName();
                    $old_thumb->path = public_path().'/files/thumbnails/';
                    $old_thumb->type = $image->mime();
                    $old_thumb->size = $image->filesize()/1024; /* Size stored in Kb */
                    $old_thumb->save();
                }
            }
        }
    }

    function send_mail($mode, $model, $user)
    {
        if ($mode == 'qcc_assignment') {
            $assignment = $model;
            $recipient = User::where('area','Gerencia General')->where('priv_level',2)->first();
            $pm = $assignment->resp_id!=0 ? User::find($assignment->resp_id) : 0;
            $cc = $user->email;

            $subject = 'Nuevo certificado de control de calidad subido al sistema';

            $data = array('recipient' => $recipient, 'pm' => $pm, 'assignment' => $assignment);
            $mail_structure = 'emails.qcc_uploaded';
        }
        elseif ($mode == 'qcc_site') {
            $site = $model;
            $assignment = $site->assignment;
            $recipient = User::where('area', 'Gerencia General')->where('priv_level', 2)->first();
            $pm = $assignment->resp_id!=0 ? User::find($assignment->resp_id) : 0;
            $cc = $user->email;

            $subject = 'Nuevo certificado de control de calidad subido al sistema';

            $data = array('recipient' => $recipient, 'pm' => $pm, 'assignment' => $assignment);
            $mail_structure = 'emails.qcc_uploaded';
        }

        if ($recipient) {
            $view = View::make($mail_structure, $data);
            $content = (string) $view;
            $success = 1;
            try {
                Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $subject) {
                    $message->to($recipient->email, $recipient->name)
                        ->cc($cc)
                        ->subject($subject)
                        ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                });
            } catch (Exception $ex) {
                $success = 0;
            }

            $email = new Email;
            $email->sent_by = 'postmaster@gerteabros.com';
            $email->sent_to = $recipient->email;
            $email->sent_cc = $cc;
            $email->subject = $subject;
            $email->content = $content;
            $email->success = $success;
            $email->save();
        }
    }

    function add_history_record($mode, $model, $file, $user)
    {
        if ($mode == 'device_file') {
            $device = $model;
            $device_history = new DeviceHistory;
            $device_history->device_id = $device->id;
            $device_history->type = 'Carga de archivo';
            $device_history->contents = 'El archivo "'.$file->description.'" fue cargado al sistema por '.$user->name;
            $device_history->status = $device->status;
            $device_history->historyable()->associate($file);
            $device_history->save();
        }
        /*
        elseif($mode=='vehicle_file'){
            $vehicle = $model;
            $vehicle_history = new VehicleHistory;
            $vehicle_history->vehicle_id = $vehicle->id;
            $vehicle_history->type = 'Carga de archivo';
            $vehicle_history->contents = 'El archivo "'.$file->description.'" es cargado al sistema por '.$user->name;
            $vehicle_history->status = $vehicle->status;
            $vehicle_history->historyable()->associate($file);
            $vehicle_history->save();
        }
        */
    }

    function update_oc($file, $oc, $type, $path)
    {
        if ($type == 'oc_org' && $file->type != 'pdf') {
            Excel::load($path, function ($reader) use ($oc) {
                $sheet = $reader->getSheetByName('OC');
                $oc->oc_amount = $sheet->getCell('AR62')->getCalculatedValue();
                $oc->save();
            });
        }
    }

    // Old code for uploading project files based on levels
    /*
     * if($type=='project') {

            $project = Project::find($id);
            $status = $request->input('status');

            if ($request->hasFile('file')) {

                if($status==0||$status==2||$status==3||$status==5||$status==7||$status==8||$status==14) {
                    $v = \Validator::make($request->file(), [
                        'file' => 'mimes:pdf',
                    ]);
                }
                elseif($status==1||$status==4||$status==6||$status==13){
                    $v = \Validator::make($request->file(), [
                        'file' => 'mimes:xls,xlsx',
                    ]);
                }
                if ($v->fails()) {
                    Session::flash('message', " Tipo de archivo no soportado! ");
                    return redirect()->back();
                }

                $FileType = $newfile->getClientOriginalExtension();
                $FileSize = $newfile->getClientSize() / 1024;
                $Filepath = public_path() . '/files/';
                $FileName = 'provisional'.$FileType;

                if($status==0){
                    $project->asig_num = $request->input('asig_num');
                    $project->asig_deadline = $request->input('asig_deadline');

                    if(empty($project->asig_num)||empty($project->asig_deadline)){
                        if(empty($project->asig_num))
                            Session::flash('message', " Debe especificar el código del documento de asignación! ");
                        elseif(empty($project->asig_deadline))
                            Session::flash('message', " Debe especificar el tiempo restante (deadline)! ");
                        return redirect()->back();
                    }

                    $FileName = 'PR_'.$project->id.'_asig.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_asig.pdf')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_asig.pdf')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==1){
                    $project->quote_amount = $request->input('quote_amount');

                    if(empty($project->quote_amount)){
                        Session::flash('message', " Debe especificar el monto cotizado! ");
                        return redirect()->back();
                    }

                    $FileName = 'PR_'.$project->id.'_ctzcn.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_ctzcn.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_ctzcn.xlsx')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_ctzcn.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_ctzcn.xlsx')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==2){
                    $FileName = 'PR_'.$project->id.'_PC_org.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_PC_org.pdf')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_PC_org.pdf')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==3){
                    $project->pc__amount = $request->input('pc__amount');
                    $project->ini_date = $request->input('ini_date');
                    $project->pc_deadline = $request->input('pc_deadline');
                    $project->ini_obs = $request->input('ini_obs');

                    if(empty($project->pc__amount)||empty($project->ini_date)||empty($project->pc_deadline)){
                        if(empty($project->pc__amount))
                            Session::flash('message', " Debe especificar el monto asignado! ");
                        elseif(empty($project->ini_date))
                            Session::flash('message', " Debe especificar la fecha de inicio! ");
                        elseif(empty($project->pc_deadline))
                            Session::flash('message', " Debe especificar el tiempo restante (deadline)! ");
                        return redirect()->back();
                    }

                    $FileName = 'PR_'.$project->id.'_PC_sgn.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_PC_sgn.pdf')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_PC_sgn.pdf')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==4){
                    $FileName = 'PR_'.$project->id.'_qty_org.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_qty_org.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_qty_org.xlsx')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_qty_org.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_qty_org.xlsx')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==5){
                    $FileName = 'PR_'.$project->id.'_qty_sgn.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_qty_sgn.pdf')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_qty_sgn.pdf')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==6){
                    $project->costsh_amount = $request->input('costsh_amount');

                    if(empty($project->costsh_amount)){
                        Session::flash('message', " Debe especificar el monto ejecutado! ");
                        return redirect()->back();
                    }

                    $FileName = 'PR_'.$project->id.'_cst_org.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_cst_org.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_cst_org.xlsx')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_cst_org.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_cst_org.xlsx')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==7){
                    $FileName = 'PR_'.$project->id.'_cst_sgn.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_cst_sgn.pdf')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_cst_sgn.pdf')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==8){
                    $FileName = 'PR_'.$project->id.'_qcc.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_qcc.pdf')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_qcc.pdf')
                            ->firstOrFail();
                        $erase_file->delete();
                        $project->status = $project->status-1;
                    }
                }
                elseif($status==13){
                    $FileName = 'PR_'.$project->id.'_sch.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_sch.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_sch.xlsx')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id', '=', $id)->where('imageable_type', '=', 'App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_sch.xls')
                            ->orwhere('name', 'like', 'PR_'.$project->id.'_sch.xlsx')
                            ->firstOrFail();
                        $erase_file->delete();
                    }
                }
                elseif($status==14){
                    $FileName = 'PR_'.$project->id.'_wty.'.$FileType;

                    $exito = 0;
                    try {
                        $del_prevfile = File::where('imageable_id','=',$id)->where('imageable_type','=','App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_wty.pdf')
                            ->firstOrFail()->name;
                        \Storage::disk('local')->delete($del_prevfile);
                        $exito = $exito + 1;
                    } catch (ModelNotFoundException $ex) {
                        $exito = 0;
                    }
                    if ($exito > 0) {
                        $erase_file = File::where('imageable_id', '=', $id)->where('imageable_type', '=', 'App\Project')
                            ->where('name', 'like', 'PR_'.$project->id.'_wty.pdf')
                            ->firstOrFail();
                        $erase_file->delete();
                    }
                }

                $subir = \Storage::disk('local')->put($FileName, \File::get($newfile));

                if ($subir) {

                    $file = new File;
                    $file->name = $FileName;
                    $file->path = $Filepath;
                    $file->type = $FileType;
                    $file->size = $FileSize;

                    $file->user_id = $user->id;

                    $file->imageable()->associate(Project::find($id));
                    $file->save();

                    if($status==0){
                        $project->asig_file_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==1){
                        $project->quote_file_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==2){
                        $project->pc_org_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==3){
                        $project->pc_sgn_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==4){
                        $project->matsh_org_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==5){
                        $project->matsh_sgn_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==6){
                        $project->costsh_org_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==7){
                        $project->costsh_sgn_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==8){
                        $project->qcc_file_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==13){
                        $project->sch_file_id = File::where('name','=',$FileName)->first()->id;
                    }
                    if($status==14){
                        $project->wty_file_id = File::where('name','=',$FileName)->first()->id;
                    }

                    if($status<13)
                        $project->status = $project->status+1;

                    $project->save();

                    Session::flash('message', " Actualización exitosa, Archivo guardado con nombre $FileName ");
                    return redirect()->route('project.index');
                } else {
                    Session::flash('message', " Error al cargar archivo, intente de nuevo por favor ");
                    return redirect()->back();
                }

            } else {
                Session::flash('message', "No se seleccionó ningún archivo o tamaño de archivo superior al permitido!");
                return redirect()->back();
            }
        }
    */
}
