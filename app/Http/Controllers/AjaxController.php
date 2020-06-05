<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
//use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use App\Activity;
use App\Assignment;
use App\Bill;
use App\Contact;
use App\CorpLineRequirement;
use App\Device;
use App\Driver;
use App\Employee;
use App\Event;
use App\File;
use App\Item;
use App\Maintenance;
use App\Material;
use App\OC;
use App\OcCertification;
use App\Operator;
use App\Order;
use App\Project;
use App\Provider;
use App\Site;
use App\Task;
use App\TechGroup;
use App\User;
use App\Vehicle;
use App\Warehouse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;
use App\Http\Traits\ActiveTrait;

class AjaxController extends Controller
{
    use ActiveTrait;
    use FilesTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function check_existence(Request $request)
    {
        $data = Request::input('value');
        $message = "";
        $status = "";

        if($data){
            $user = User::where('name', $data)->where('status', 'Activo')->first(); //only active users

            if(!empty($user)){
                $message = '<br><label class="control-label"><i class="glyphicon glyphicon-ok"></i>'.
                    ' Nombre de responsable verificado</label>';
                $status = 'success';
            }
            else{
                $message = '<br><label class="control-label"><i class="glyphicon glyphicon-warning-sign"></i>'.
                    ' La persona especificada no está registrada en el sistema.<br>Este valor no se guardará</label>';
                $status = 'warning';
            }
        }

        if (Request::ajax()) {
            return response()->json(['message' => $message, 'status' => $status]);
        }
        return redirect()->back();
    }

    public function check_material_existence(Request $request)
    {
        $data = Request::input('name');
        $message = "";
        $status = "";
        $units = "";

        if($data){
            $material = Material::where('name', $data)->first();

            if(!empty($material)){
                $message = '<br><label class="control-label"><i class="glyphicon glyphicon-ok"></i>'.
                    ' Material verificado en sistema</label>';
                $status = 'success';
                $units = $material->units;
            }
            else{
                $message = '<br><label class="control-label"><i class="glyphicon glyphicon-warning-sign"></i>'.
                    ' El material indicado no está registrado en el sistema.<br>Este registro no se guardará</label>';
                $status = 'warning';
            }
        }

        if (Request::ajax()) {
            return response()->json(['message' => $message, 'status' => $status, 'units' => $units]);
        }
        return redirect()->back();
    }

    public function check_email_address(Request $request)
    {
        $data = Request::input('name');
        $message = "";
        $status = "warning";
        $email = "";

        if($data){
            $user = User::where('name', $data)->where('status', 'Activo')->first(); // only active users

            if(!empty($user)){
                if($user->email!=''){
                    $status = 'success';
                    $email = $user->email;
                }
                else{
                    $message = '<br><label class="control-label"><i class="glyphicon glyphicon-warning-sign"></i>'.
                        ' El usuario especificado no tiene registrada una dirección de correo electrónico</label>';
                }
            }
            else{
                $message = '<br><label class="control-label"><i class="glyphicon glyphicon-warning-sign"></i>'.
                    ' El usuario especificado no está registrado en el sistema</label>';
            }
        }

        if (Request::ajax()) {
            return response()->json(['message' => $message, 'status' => $status, 'email' => $email]);
        }
        return redirect()->back();
    }
    /*
    public function verify()
    {
        if(Request::ajax()){
            return 'Hola';
        }
    }
    */
    public function dynamic_sites(Request $request)
    {
        $data = Request::input('assignment_id');
        $message = "<option value=\"\" hidden>Seleccione un sitio</option>";

        if($data){
            $assignment = Assignment::find($data);
            $sites = Site::where('assignment_id', $assignment->id)/*->where('name','<>','Main')*/->get();
            foreach($sites as $site){
                $message = $message.'<option value="'.$site->id.'">'.$site->name.'</option>';
            }
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function dynamic_actives(Request $request)
    {
        $data = Request::input('active_type');
        $message = "<option value=\"\" hidden>Seleccione un activo</option>";

        if($data=='vehicle'){
            $vehicles = Vehicle::where('status','<>','En mantenimiento')->get();
            foreach($vehicles as $vehicle){
                $message = $message.'<option value="'.$vehicle->license_plate.'">'.$vehicle->type.' - '.
                    $vehicle->license_plate.'</option>';
            }
        }
        elseif($data=='device'){
            $devices = Device::where('status','<>','En mantenimiento')->get();
            foreach($devices as $device){
                $message = $message.'<option value="'.$device->serial.'">'.$device->type.' - '.$device->serial.
                    '</option>';
            }
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function dynamic_items(Request $request)
    {
        $data = Request::input('category');
        $message = "";

        if($data){
            $items = Item::where('category',$data)->get();

            $i = 0;

            foreach($items as $item){
                $message = $message.
                '<tr>
                    <td align="center">
                        <input type="checkbox" name="item_'.$i.'" value="'.$item->id.
                        '" class="checkbox" onclick="enable_field(this,i=\''.$i.'\')">
                    </td>
                    <td>'.$item->number.'</td>
                    <td>'.$item->client_code.'</td>
                    <td>'.$item->subcategory.'</td>
                    <td>'.$item->description.'</td>
                    <td>'.$item->units.'</td>'.
                    //<td>'.$item->cost_unit_central.'</td>
                    '<td>
                        <input required="required" type="number" class="form-control quantity" name="quantity_'.$i.
                        '" id="quantity_'.$i.'" step="1" min="1" placeholder="Cant. proyectada" disabled="disabled">
                    </td>
                </tr>';
                $i++;
            }

            $message = $message.'<input type="hidden" name="listed_items" value="'.$i.'">';
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function dynamic_guaranteeable(Request $request)
    {
        $type = Request::input('type');
        $selected_id = Request::input('selected_id');
        $message = "";

        if($type){
            if($type=='Garantía (proyectos)'){

                $message = "<option value=\"\" hidden>Seleccione un Proyecto</option>";
                $projects = Project::where('id', '>', 0)->where('created_at', '>' ,Carbon::now()->subYears(2))
                    ->orderBy('name')->get();

                foreach($projects as $project){
                    $message .= '<option value="'.$project->id.'"'.
                        ($selected_id!=0&&$selected_id==$project->id ? ' selected="selected"' : '').
                        ' title="'.$project->name.'">'.str_limit($project->name,100).'</option>';
                }
            }
            if($type=='Garantía (asignaciones)'){

                $message = "<option value=\"\" hidden>Seleccione una asignación</option>";
                $last_stat = Assignment::first()->last_stat();
                $assignments = Assignment::where('id', '>', 0)
                    ->whereNotIn('status', [$last_stat/*'Concluído'*/,0/*'No asignado'*/])
                    ->orderBy('name')->get();

                foreach($assignments as $assignment){
                    $message .= '<option value="'.$assignment->id.'"'.
                        ($selected_id!=0&&$selected_id==$assignment->id ? 'selected="selected"' : '').
                        ' title="'.$assignment->name.'">'.str_limit($assignment->name,100).'</option>';
                }
            }
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function dynamic_assignment_sub_type(Request $request)
    {
        $type = Request::input('type');
        $option = Request::input('option');
        $sub_type = Request::input('sub_type');
        $message = "";
        $entries = array();

        if($type){
            if($type=='project_id'){
                $project = Project::find($option);
                $option = $project->type;
            }

            $message = "<option value=\"\" hidden>Seleccione el tipo de trabajo</option>";

            if($option=='Fibra óptica'){
                $entries = array('Tendido', 'Instalación', 'Mantenimiento');
                /*
                $message .= '<option value="Tendido">Tendido</option>';
                $message .= '<option value="Instalación">Instalación</option>';
                $message .= '<option value="Mantenimiento">Mantenimiento</option>';
                */
            }
            elseif($option=='Radiobases'){
                $entries = array('Instalación RBS', 'Swap RBS', 'Instalación MW', 'Swap MW', 'Instalación de gabinete',
                    'Instalación de energía', 'Survey', 'ATP', 'COLT', 'Obras civiles', 'Otro');

                /*
                $message .= '<option value="Instalación RBS"'.($sub_type=="Instalación RBS" ? ' selected="selected"' : '').
                    '>Instalación RBS</option>';
                $message .= '<option value="Swap RBS"'.($sub_type=="Swap RBS" ? ' selected="selected"' : '').
                    '>Swap RBS</option>';
                $message .= '<option value="Instalación MW"'.($sub_type=="Instalación MW" ? ' selected="selected"' : '').
                    '>Instalación MW</option>';
                $message .= '<option value="Swap MW"'.($sub_type=="Swap MW" ? 'selected="selected"' : '').
                    '>Swap MW</option>';
                */
            }
            elseif($option=='Instalación de energía'){
                $entries = array('Instalación', 'Mantenimiento');
            }
            elseif($option=='Obras Civiles'){
                $entries = array('Construcción');
            }
            elseif($option=='Venta de material'){
                $entries = array('Venta');
            }

            foreach($entries as $entry){
                $message .= '<option value="'.$entry.'"'.($sub_type==$entry ? ' selected="selected"' : '').
                    '>'.$entry.'</option>';
            }
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function dynamic_requirement(Request $request, $active)
    {
        $req_type = Request::input('req_type');
        $active_type = Request::input('active_type');
        $prev_value = Request::input('prev_value');
        $branch = Request::input('branch');
        $message = '';

        if($active=='device'){
            $devices = 0;
            
            if($req_type=='borrow'||$req_type=='transfer_wh'){
                $devices = Device::join('branches', 'devices.branch_id', '=', 'branches.id')
                    ->select('devices.*')
                    ->where('devices.flags','0001')
                    ->where('devices.type',$active_type)
                    ->where('branches.city',$branch)
                    ->get();
                //$devices = Device::where('flags', '0001')->where('type', $active_type)->where('branch', $branch)->get();
                // Get all available devices from the specified branch
            }
            elseif($req_type=='transfer_tech'||$req_type=='devolution'){
                $devices = Device::where('flags', '0010')->where('type', $active_type)->get(); // Get all active devices
            }

            $message = "<option value=\"\" hidden>Seleccione un equipo</option>";

            if($devices){
                foreach($devices as $device){
                    $message .= '<option value="'.$device->id.'"'.($prev_value==$device->id ? ' selected="selected"' :
                            old('device_id')).'>'.$device->model.' - '.$device->serial.'</option>';
                }
            }
        }
        if($active=='vehicle'){
            $vehicles = 0;

            if($req_type=='borrow'||$req_type=='transfer_branch'){
                $vehicles = Vehicle::join('branches', 'vehicles.branch_id', '=', 'branches.id')
                    ->select('vehicles.*')
                    ->where('vehicles.flags','0001')
                    ->where('vehicles.type',$active_type)
                    ->where('branches.city',$branch)
                    ->get();
                //$vehicles = Vehicle::where('flags', '0001')->where('type', $active_type)->where('branch', $branch)->get();
                // Get all available vehicles from the specified branch
            }
            elseif($req_type=='transfer_tech'||$req_type=='devolution'){
                $vehicles = Vehicle::where('flags','like','0%10')->where('type', $active_type)->get(); // Get all active vehicles
            }

            $message = "<option value=\"\" hidden>Seleccione un vehículo</option>";

            if($vehicles){
                foreach($vehicles as $vehicle){
                    $message .= '<option value="'.$vehicle->id.'"'.($prev_value==$vehicle->id ? ' selected="selected"' :
                            old('vehicle_id')).'>'.$vehicle->model.' - '.$vehicle->license_plate.'</option>';
                }
            }
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function load_oc_values(Request $request)
    {
      $oc_id = Request::input('oc_id');
      $amount = Request::input('amount');
      $concept = Request::input('concept');
      $cert = Request::input('cert');
      $message = "$oc_id";

      if ($oc_id) {
        $oc = OC::find($oc_id);
        $certificate = OcCertification::find($cert);

        $part0 = "<tr><td>Monto asignado:</td><td style='color:green;'>".number_format($oc->oc_amount,2)." Bs</td></tr>";

        if ($oc->executed_amount != 0) {
          $executed = number_format($oc->executed_amount,2).' Bs';
          $part1 = "<td width='30%' style='color:green;'>$executed</td>";
          $balance = number_format($oc->executed_amount - $oc->payed_amount - $amount,2).' Bs';
        } else {
          $assigned = number_format($oc->oc_amount,2).' Bs';
          $part1 = "<td width='30%' style='color:green;'>$assigned</td>";
          $balance = number_format($oc->oc_amount - $oc->payed_amount - $amount,2).' Bs';
        }

        if ($balance >= 0)
          $part2 = "<td style='color:green;'>$balance</td>";
        else
          $part2 = "<td style='color:red;'>$balance</td>";

        $reference = $oc->executed_amount != 0 ? 'Monto ejecutado:' : 'Monto asignado:';

        $message = "<br><table width='90%'>";

        if ($oc->executed_amount != 0) {
          $message .= $part0;
        }

        //$message .= "<tr><td width='25%'>".$reference."</td>".$part1."<td width='15%' >Saldo:</td>".$part2."</tr>";

        if ($concept) {
          /*
          $percentages = explode('-', $oc->percentages);

          if ($concept == 'Adelanto')
            $concept_percentage = $percentages[0];
          elseif ($concept == 'Avance')
            $concept_percentage = $percentages[1];
          elseif ($concept == 'Entrega')
            $concept_percentage = $percentages[2];
          else
            $concept_percentage = 0;
          */

          $certificate_percentage = number_format(($certificate->amount/$oc->oc_amount) * 100 , 2);

          $part3 = "<td style='color:green;'>$certificate_percentage %</td>";

          $current_percentage = number_format(($amount/$oc->oc_amount)*100,2);

          $used = 0;
          foreach ($certificate->invoices as $inv) {
              $used = $used + $inv->amount;
          }

          $used_percentage = ($used / $certificate->amount) * 100;
          $used_str = number_format($used_percentage, 2);

          /*
          $current_percentage = number_format(($amount/($oc->executed_amount!=0 ? $oc->executed_amount :
                      $oc->oc_amount))*100,2);
          */

          if ($certificate->amount < ($amount + $used)) {
            $part4 = "<td style='color:red;'>$current_percentage %</td>";
            $part5 = "<td style='color:red;'>$used_percentage %</td>";
          } else {
            $part4 = "<td style='color:green;'>$current_percentage %</td>";
            $part5 = "<td style='color:green;'>$used_percentage %</td>";
          }

          $message = $message."<tr><td>% de certificado</td>".$part3."<td></td><td></td></tr>";
          $message = $message."<tr><td>% usado</td>".$part5."<td>% actual</td>".$part4."</tr>";
        }

        $message = $message."</table><p></p>";
      }

      if (Request::ajax()) {
        return response()->json($message);
      }
      return redirect()->back();
    }

    public function load_oc_amount_values(Request $request)
    {
        $oc_id = Request::input('oc_id');
        $amount = Request::input('amount') ?: 0;
        $concept = Request::input('concept');
        $message = "Display content here";

        if ($oc_id) {
            $oc = OC::find($oc_id);

            $oc_amount = number_format($oc->oc_amount,2).' Bs';
            $certified_to_date = number_format($oc->executed_amount + $amount,2).' Bs';
            $pending_certification = number_format($oc->oc_amount - $oc->executed_amount - $amount,2).' Bs';
            $paid_to_date = number_format($oc->payed_amount,2).' Bs';
            $balance = number_format($oc->executed_amount + $amount - $oc->payed_amount,2).' Bs';
            //$current_percentage = $amount / ($oc->oc_amount != 0 ? $oc->oc_amount : 1);
            $current_percentage_str = number_format(($amount / ($oc->oc_amount != 0 ? $oc->oc_amount : 1)) * 100, 2).' %';
            $used_per_concept = 0;

            $part1 = "<td width='30%' style='color:green;'>$oc_amount</td>";
            
            if (($oc->executed_amount + $amount) <= $oc->oc_amount) {
                $color23 = 'green';
            } else {
                $color23 = 'red';
            }

            $part2 = "<td width='30%' style='color:$color23;'>$certified_to_date</td>";
            $part3 = "<td width='30%' style='color:$color23;'>$pending_certification</td>";

            foreach ($oc->certificates as $certification) {
                if ($certification->type_reception == $concept) {
                    $used_per_concept = $used_per_concept + $certification->amount;
                }
            }

            $used_percentage = (($used_per_concept + $amount) / ($oc->oc_amount != 0 ? $oc->oc_amount : 1)) * 100;
            $used_percentage_str = number_format($used_percentage, 2).' %';

             // Validación de porcentajes de pago según OC
            $percentages = explode('-', $oc->percentages);
            
            if (($concept == 'Adelanto' && $percentages[0] >= $used_percentage) ||
                ($concept == 'Parcial' && $percentages[1] >= $used_percentage) ||
                ($concept == 'Total' && $percentages[2] >= $used_percentage)) {
                $color67 = 'green';
            } else {
                $color67 = 'red';
            }

            $part6 = "<td width='30%' style='color:$color67;'>$current_percentage_str</td>";
            $part7 = "<td width='30%' style='color:$color67;'>$used_percentage_str</td>";

            if ($balance >= 0) {
                $color45 = 'green';
            } else {
                $color45 = 'red';    
            }

            $part4 = "<td width='30%' style='color:$color45;'>$paid_to_date</td>";
            // $part5 = "<td style='color:$color45;'>$balance</td>";

            $message = "<br><table width='90%'>".
                "<tr><td width='70%'>Monto asignado a OC</td>".$part1."</tr>".
                "<tr><td width='70%'>Monto certificado a la fecha</td>".$part2."</tr>".
                "<tr><td width='70%'>% actual</td>".$part6."</tr>".
                "<tr><td width='70%'>% ".$concept." certificado</td>".$part7."</tr>".
                "<tr><td width='70%'>Monto pendiente de certificación</td>".$part3."</tr>".
                "<tr><td width='70%'>Monto cancelado a la fecha</td>".$part4."</tr>".
                //"<tr><td width='70%'>Saldo por pagar</td>".$part5."</tr>".
                "</table><p></p>";
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function load_task_values(Request $request)
    {
        $task_id = Request::input('task_id');
        $progress = Request::input('progress');
        $message = "$task_id";

        if($task_id){
            $task = Task::find($task_id);

            $total_expected = number_format($task->total_expected,2).' '.$task->units;
            $part1 = "<td width='30%' style='color:green;'>$total_expected</td>";

            $total_done = number_format($task->progress + $progress,2).' '.$task->units;

            if(($task->progress + $progress)<=$task->total_expected)
                $part2 = "<td style='color:green;'>$total_done</td>";
            else
                $part2 = '<td style="color:red;">'.$total_done.'</td><tr><td colspan="4"><div class="has-error">'.
                         '<label class="control-label"><i class="glyphicon glyphicon-warning-sign"></i>'.
                         ' La cantidad indicada excede el limite proyectado para este item!</label></div></td></tr>';

            $message = "<br><table width='90%'><tr><td width='20%'>Proyectado:</td>".$part1.
                    "<td width='20%' >Completado:</td>".$part2."</tr>";

            $message = $message."</table><p></p>";
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function load_material_available(Request $request)
    {
        $warehouse_id = Request::input('warehouse_id');
        $qty = Request::input('qty');
        $message = "";

        $material = Material::where('name', Request::input('material_name'))->first();
        $warehouse = Warehouse::find($warehouse_id);

        if($material&&$warehouse&&$qty){

            $exists = $warehouse->materials->contains($material->id); //Determine if a relation material-warehouse exists

            $total_available = $warehouse->materials()->where('material_id', $material->id)->first();

            $new_total = $exists ? $total_available->pivot->qty - $qty : '';
            $new_total_string = number_format($new_total,2).' '.$material->units;
            $available_string = number_format($total_available->pivot->qty,2).' '.$material->units;

            if($new_total>=0){
                $part1 = "<td style='color:green;'>$available_string</td>";
                $part2 = "<td width='30%' style='color:green;'>$new_total_string</td>";
            }
            else{
                $part1 = '<td style="color:red;">'.$available_string.'</td><tr><td colspan="4"><div class="has-error">'.
                    '<label class="control-label"><i class="glyphicon glyphicon-warning-sign"></i>'.
                    ' Este almacén no cuenta con la cantidad de material requerido!</label></div></td></tr>';
                $part2 = "<td width='30%' style='color:red;'>$new_total_string</td>";
            }

            $message = "<br><table width='90%'><tr><td width='20%'>En almacén:</td>".$part1.
                "<td width='20%' >Disponible:</td>".$part2."</tr>";

            $message = $message."</table><p></p>";
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function load_activity_info(Request $request)
    {
        $activity_id = Request::input('activity_id');
        $message = "";
        $user = Session::get('user');

        if($activity_id){
            $activity = Activity::find($activity_id);

            $message = $message."<table class=\"table table_green\">
                                    <tr>
                                        <th>Detalle de la actividad</th>
                                        <th colspan=\"2\" width=\"35%\">Archivos de respaldo:</th>
                                    </tr>
                                    <tr>
                                        <td rowspan=\"3\" style=\"background-color: white\">
                                        <p>".$activity->observations."</p>";

            if($activity->progress!=0){
                $message = $message."<p>Avance: ".$activity->progress." ".$activity->task->units."</p>";
            }

            $message = $message."
                                </td>
                                <td style=\"text-align: center;background-color: white\" colspan=\"2\">";

            $remaining=0;
            foreach($activity->files as $file){
                $message = $message."<a href=\"/download/".$file->id."\" style=\"text-decoration:none\">";

                if($file->type=="pdf")
                    $message = $message."<img src=\"/imagenes/pdf-icon.png\" alt=\"PDF\" />";
                elseif($file->type=="docx"||$file->type=="doc")
                    $message = $message."<img src=\"/imagenes/word-icon.png\" alt=\"WORD\" />";
                elseif($file->type=="xlsx"||$file->type=="xls")
                    $message = $message."<img src=\"/imagenes/excel-icon.png\" alt=\"EXCEL\" />";
                elseif($file->type=="jpg"||$file->type=="jpeg"||$file->type=="png")
                    $message = $message."<img src=\"/imagenes/image-icon.png\" alt=\"IMAGE\" />";

                $message = $message."</a>";
                $remaining++;
            }

            if($remaining<5){
                if($activity->task){
                    if(($activity->task->status!=$activity->task->last_stat()/*'Concluído'*/&&
                            $activity->task->status!=0/*'No asignado'*/)||$user->priv_level==4){
                        $message = $message."<a href=\"/files/activity/".$activity->id.
                            "\"><i class=\"fa fa-upload\"></i> Subir archivo</a>";
                    }
                }
            }

            $message = $message."</td>
                            </tr>
                            <tr>
                                <th width=\"17 % \">Responsable:</th>
                                <td style=\"background-color: white\">".
                                    ($activity->responsible ? $activity->responsible->name : 'N/A')."
                                </td>
                            </tr>
                            <tr>
                                <th>Evento agregado por:</th>
                                <td style=\"background-color: white\">".
                                    ($activity->user ? $activity->user->name : 'N/A')."
                                </td>
                            </tr>
                            </table>";
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function load_event_info(Request $request)
    {
        $event_id = Request::input('event_id');
        $message = "";
        $user = Session::get('user');

        if($event_id){
            $event = Event::find($event_id);

            $message = $message."<table class=\"table table_green\">
                                    <tr>
                                        <th>".$event->description."</th>
                                        <th colspan=\"2\" width=\"35%\">Archivos de respaldo:</th>
                                    </tr>
                                    <tr>
                                        <td rowspan=\"3\" style=\"background-color: white\">
                                            <p>".$event->detail."</p>
                                        </td>
                                        <td style=\"text-align: center;background-color: white\" colspan=\"2\">";

            foreach($event->files as $file){
                $message = $message."<a href=\"/download/".$file->id."\" style=\"text-decoration:none\">";

                if($file->type=="pdf")
                    $message = $message."<img src=\"/imagenes/pdf-icon.png\" alt=\"PDF\" />";
                elseif($file->type=="docx"||$file->type=="doc")
                    $message = $message."<img src=\"/imagenes/word-icon.png\" alt=\"WORD\" />";
                elseif($file->type=="xlsx"||$file->type=="xls")
                    $message = $message."<img src=\"/imagenes/excel-icon.png\" alt=\"EXCEL\" />";
                elseif($file->type=="jpg"||$file->type=="jpeg"||$file->type=="png")
                    $message = $message."<img src=\"/imagenes/image-icon.png\" alt=\"IMAGE\" />";

                $message = $message."</a>";
            }

            if($event->files->count()<=5){
                if($event->eventable){
                    if(($event->eventable->status!=$event->eventable->last_stat()/*'Concluído'*/&&
                            $event->eventable->status!=0/*'No asignado'*/)||$user->priv_level==4){
                        $message = $message."<a href=\"/files/event/".$event->id.
                            "\"><i class=\"fa fa-upload\"></i> Subir archivo</a>";
                    }
                }
            }

            $message = $message."</td>
                            </tr>
                            <tr>
                                <th width=\"15%\">Responsable:</th>
                                <td style=\"background-color: white\">".
                                    ($event->responsible ? $event->responsible->name : 'N/A')."
                                </td>
                            </tr>
                            <tr>
                                <th>Registrado por:</th>
                                <td style=\"background-color: white\">".
                                    ($event->user ? $event->user->name : 'N/A')."
                                </td>
                            </tr>
                            </table>";
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function load_name(Request $request, $form)
    {
        $query_id = Request::input('query_id');
        $message = "";

        if($query_id){
            if($form=='device_requirement_form'){
                $device = Device::find($query_id);

                if($device){
                    $message = $device->user ? $device->user->name : '';
                }
            }
            if($form=='vehicle_requirement_form'){
                $vehicle = Vehicle::find($query_id);

                if($vehicle){
                    $message = $vehicle->user ? $vehicle->user->name : '';
                }
            }
            if($form=='line_assignation_form'){
                $requirement = CorpLineRequirement::find($query_id);

                if($requirement){
                    $message = $requirement->person_for ? $requirement->person_for->name : '';
                }
            }
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function autocomplete(Request $request, $table)
    {
        $term = Request::input('query');
        $suggestions = Array(); //$suggestions[] = '';

        if ($table == 'users') {
            $results = User::where('name','like','%'.$term.'%')->where('status', 'Activo')->get(); // only active users
            
            foreach ($results as $result) {
                //$suggestions[] = array('‌​value' => $result->name);
                $suggestions[] = $result->name;
                //$suggestions = json_encode(array('suggestions'=>array(array('value'=>$result->name))));
            }
        } else if ($table == 'contacts') {
            $results = Contact::where('name','like','%'.$term.'%')->get();

            foreach ($results as $result) {
                $suggestions[] = $result->name;
            }
        } else if ($table == 'materials') {
            $results = Material::where('name','like','%'.$term.'%')->get();

            foreach ($results as $result) {
                $suggestions[] = $result->name;
            }
        } else if ($table == 'technicians') {
            $results = User::where('name','like','%'.$term.'%')->where('role', 'Técnico')
                ->where('status', 'Activo')->get(); //only active users

            foreach ($results as $result) {
                $suggestions[] = $result->name;
            }
        } else if ($table == 'rbs_sites') {
            $last_stat = Site::first()->last_stat();
            
            $results = Site::join('assignments', 'sites.assignment_id', '=', 'assignments.id')
                ->select('sites.*')
                ->where('sites.name', 'like', "%$term%")
                ->whereNotIn('sites.status',[$last_stat/*'Concluído'*/,0/*'No asignado'*/])
                ->where('assignments.type', 'Radiobases')
                ->get();

            foreach($results as $result){
                $suggestions[] = $result->name.' - '.$result->code;
            }
        } else if ($table == 'employees') {
            $results = Employee::where(function ($query) use($term){
                    $query->where('first_name','like','%'.$term.'%')->orwhere('last_name', 'like', "%$term%");
                })->where('active', 1)
                ->get(); //only active employees

            foreach ($results as $result) {
                $suggestions[] = $result->first_name.' '.$result->last_name;
            }
        } else if ($table == 'orders') {
            $results = Order::where('code','like','%'.$term.'%')->get();

            foreach ($results as $result) {
                $suggestions[] = $result->code;
            }
        } else if ($table == 'providers') {
            $results = Provider::where('prov_name','like','%'.$term.'%')->get();

            foreach ($results as $result) {
                $suggestions[] = $result->prov_name;
            }
        } else {
            $suggestions[] = 'No se han encontrado coincidencias';
        }

        if (Request::ajax()) {
            //return response()->json($suggestions);
            return json_encode(array('suggestions' => $suggestions));
        }
        return redirect()->back();
    }
    
    public function flag_change(Request $request, $table)
    {
        $flag = Request::input('flag');
        $id = Request::input('id');
        $message = "";
        
        if ($table == 'vehicle') {
            $vehicle = Vehicle::find($id);
            if ($flag == 'maintenance') {
                if ($vehicle->flags != '1000') {
                    $vehicle = $this->registrarMantenimiento($id);
                }
            }
            /*
            elseif($flag=='req_maintenance'){
                if($vehicle->flags[1]==0)
                    $vehicle->flags = str_pad($vehicle->flags+100, 4, "0", STR_PAD_LEFT);
                $vehicle->status = 'Requiere mantenimiento';
            }
            /*
            elseif($flag=='available'){
                $vehicle->flags = '0001';
                $vehicle->status = 'Disponible';

                foreach($vehicle->maintenances as $maintenance){
                    if($maintenance->completed==0){
                        $maintenance->date = Carbon::now();
                        $maintenance->completed = 1;
                        $maintenance->save();

                        foreach($maintenance->files as $file){
                            $file->status = 1;
                            $file->save();
                        }
                    }
                }
            }
            */

            $message = ['estado' => $vehicle->status, 'responsable' => $vehicle->user->name];
        }
        elseif($table=='device'){
            $device = Device::find($id);
            if($flag=='maintenance'){
                if($device->flags!='1000'){
                    $device->flags = '1000';
                    $device->status = 'En mantenimiento';

                    $user = Session::get('user');

                    $maintenance = new Maintenance();
                    $maintenance->user_id = $user->id;
                    $maintenance->active = $device->serial;
                    $maintenance->device_id = $device->id;
                    $maintenance->type = 'Correctivo';

                    $maintenance->save();
                }
            }
            /*
            elseif($flag=='req_maintenance'){
                if($device->flags[1]==0)
                    $device->flags = str_pad($device->flags+100, 4, "0", STR_PAD_LEFT);
                $device->status = 'Requiere mantenimiento';
            }
            /*
            elseif($flag=='available'){
                $device->flags = '0001';
                $device->status = 'Disponible';

                foreach($device->maintenances as $maintenance){
                    if($maintenance->completed==0){
                        $maintenance->date = Carbon::now();
                        $maintenance->completed = 1;
                        $maintenance->save();
                        
                        foreach($maintenance->files as $file){
                            $file->status = 1;
                            $file->save();
                        }
                    }
                }
            }
            */
            
            $device->save();
            $message = $device->status;
        }
        elseif($table=='driver'){
            /*
            $driver = Driver::find($id);
            //$user = Session::get('user');
            
            if($flag=='confirm_delivery'){
                if($driver->confirmation_flags[2]==0)
                    $driver->confirmation_flags = str_pad($driver->confirmation_flags+10, 4, "0", STR_PAD_LEFT);
            }
            elseif($flag=='confirm_reception'){
                if($driver->confirmation_flags[3]==0){
                    $driver->confirmation_flags = str_pad($driver->confirmation_flags+1, 4, "0", STR_PAD_LEFT);

                    foreach($driver->files as $file){
                        $this->blockFile($file);
                    }
                }

                //$vehicle = Vehicle::find($driver->vehicle_id);
                //$vehicle->responsible = $user->id;
                //$vehicle->save();
            }

            $driver->save();
            */
            $message = 'confirmed';
        }
        elseif($table=='operator'){
            /*
            $operator = Operator::find($id);
            //$user = Session::get('user');

            if($flag=='confirm_delivery'){
                if($operator->confirmation_flags[2]==0)
                    $operator->confirmation_flags = str_pad($operator->confirmation_flags+10, 4, "0", STR_PAD_LEFT);
            }
            elseif($flag=='confirm_reception'){
                if($operator->confirmation_flags[3]==0){
                    $operator->confirmation_flags = str_pad($operator->confirmation_flags+1, 4, "0", STR_PAD_LEFT);

                    foreach($operator->files as $file){
                        $this->blockFile($file);
                    }
                }

                //$device = Device::find($operator->device_id);
                //$device->responsible = $user->id;
                //$device->save();
            }

            $operator->save();
            */
            $message = 'confirmed';
        }

        if (Request::ajax()) {
            // return response()->json([$message]);
            return response()->json($message);
        }
        return redirect()->back();
    }

    public function status_update(Request $request, $option)
    {
        $flag = Request::input('flag');
        $master_id = Request::input('master_id');
        $id = Request::input('id');
        $message = "";

        if($option=='charge'){
            if($flag=='order-to-site'||$flag=='site-to-order'){
                $order = Order::find($master_id);

                foreach($order->sites as $site){
                    if($site->id == $id){
                        $site->charged_price = $site->charged_price + $site->pivot->assigned_amount;
                        $site->save();

                        $order->sites()->updateExistingPivot($site->id, ['status' => 1]);
                    }
                }
            }
            elseif($flag=='order-to-bill'||$flag=='bill-to-order'){
                $bill = Bill::find($id);

                foreach($bill->orders as $order){
                    if($order->id == $master_id){
                        $order->charged_price = $order->charged_price + $order->pivot->charged_amount;
                        $order->save();

                        $bill->orders()->updateExistingPivot($order->id, ['status' => 1]);
                    }
                }
            }
            
            $message = 'success';
        }

        if (Request::ajax()) {
            return response()->json([$message]);
        }
        return redirect()->back();
    }

    public function set_item_unit_cost(Request $request)
    {
        $amount = Request::input('amount');
        $id = Request::input('id');
        $unit_cost = 0;

        if($amount!=0&&$id){
            $item = Item::find($id);

            if($item){
                $item->cost_unit_central = $amount;

                $item->save();

                $this->update_tasks($item);

                $unit_cost = number_format($item->cost_unit_central,2);
                //$balance = number_format($oc->executed_amount-$oc->payed_amount,2).' Bs';
            }
            else{
                $unit_cost = '0.00';
                //$balance = '0.00 Bs';
            }
        }

        if (Request::ajax()) {
            return response()->json(['unit_cost' => $unit_cost]);
        }
        return redirect()->back();
    }
    
    public function set_oc_executed_amount(Request $request)
    {
        $amount = Request::input('amount');
        $id = Request::input('id');
        $executed_amount = 0;
        $balance = 0;

        if($amount!=0&&$id){
            $oc = OC::find($id);

            if(!empty($oc)){

                // Restriction to executed amount not bigger than oc amount.
                if($oc->oc_amount>=$amount)
                    $oc->executed_amount = $amount;
                else
                    $oc->executed_amount = $oc->oc_amount;

                $oc->save();
                /* used to store certificate records on ajax change of executed_amount, now through controller
                $user = Session::get('user');

                $oc_certification = OcCertification::where('oc_id',$oc->id)->first();

                if(empty($oc_certification)){
                    $oc_certification = new OcCertification();
                    $oc_certification->user_id = $user->id;
                    $oc_certification->oc_id = $oc->id;
                    $oc_certification->amount = $oc->executed_amount;
                }
                else{
                    $oc_certification->amount = $oc->executed_amount;
                }

                $oc_certification->save();
                */
                $executed_amount = number_format($oc->executed_amount,2).' Bs';
                $balance = number_format($oc->executed_amount-$oc->payed_amount,2).' Bs';
            }
            else{
                $executed_amount = '0.00 Bs';
                $balance = '0.00 Bs';
            }
        }

        if (Request::ajax()) {
            return response()->json(['executed_amount' => $executed_amount, 'balance' => $balance]);
        }
        return redirect()->back();
    }

    public function retrieve_column(Request $request, $column)
    {
        $hint = Request::input('hint');
        $option = Request::input('option');
        $value = '';

        if($column=='group_number'){
            if($hint=='area'){

                $active_groups = TechGroup::where('group_area', $option)->where('status', 0)->orderBy('group_number')
                    ->get();

                $i = 1;

                while($active_groups->contains('group_number', $i)){
                    $i++;
                }
                
                $value = $i;

            }
            
        }

        if (Request::ajax()) {
            return response()->json(['value' => $value]);
        }
        return redirect()->back();
    }

    function update_tasks($item)
    {
        $tasks = Task::where('item_id', $item->id)->get();

        foreach($tasks as $task){
            $task->quote_price = $item->cost_unit_central;
            $task->assigned_price = $task->total_expected*$task->quote_price;
            $task->save();
        }
    }

    public function set_current_url(Request $request)
    {
        $url = Request::input('url');

        if(Session::has('url'))
            Session::forget('url');

        Session::put('url', $url);

        if (Request::ajax()) {
            return response()->json(['url' => $url]);
        }
        return redirect()->back();
    }

    function registrarMantenimiento ($idVehiculo) {
        $user = Session::get('user');
        $vehicle = Vehicle::find($idVehiculo);
        
        $last_asg = Driver::where('who_receives', $vehicle->responsible)->where('vehicle_id', $vehicle->id)
            ->orderBy('created_at','desc')->first();

        if($last_asg){
            // Se registra cambio de usuario asignado a vehiculo a persona que registra el mantenimiento
            $driver = new Driver();
            $driver->user_id = $user->id;
            $driver->vehicle_id = $last_asg->vehicle_id;
            $driver->who_delivers = $last_asg->who_receives;
            $driver->who_receives = $user->id;
            $driver->date = Carbon::now();
            $driver->project_id = $last_asg->project_id;
            $driver->project_type = $last_asg->project_type;
            $driver->destination = $last_asg->destination;
            $driver->reason = 'Mantenimiento del vehículo';
            $driver->mileage_before = $last_asg->mileage_before > $vehicle->mileage ? $last_asg->mileage_before : $vehicle->mileage;
            $driver->observations = 'Cambio por mantenimiento de vehículo';
            $driver->confirmation_flags = '0011'; // Preconfirmed
            $driver->date_confirmed = Carbon::now();
            $driver->save();

            // Se actualiza km recorrido en ultima asignacion
            $last_asg->mileage_traveled = $driver->mileage_before - $last_asg->mileage_before;
            $last_asg->mileage_after = $driver->mileage_before;
            $last_asg->save();
        }

        // Registrar mantenimiento
        $maintenance = new Maintenance();
        $maintenance->user_id = $user->id;
        $maintenance->active = $vehicle->license_plate;
        $maintenance->vehicle_id = $vehicle->id;
        $maintenance->usage = $vehicle->mileage;
        $maintenance->type = 'Correctivo';
        $maintenance->save();

        // Actualizar requerimientos pendientes
        foreach ($vehicle->requirements as $requirement) {
            if ($requirement->status === 1) {
                $requirement->status = 0; // Rejected
                $requirement->stat_change = Carbon::now();
                $requirement->stat_obs = 'Se rechaza el requerimiento porque el vehículo es puesto en mantenimiento';
                $requirement->save();
            }
        }
        
        // Actualizar datos de vehiculo
        $vehicle->flags = '1000';
        $vehicle->status = 'En mantenimiento';
        $vehicle->responsible = $user->id;
        $vehicle->save();

        // Agregar un nuevo registro en la tabla de historial de vehiculo
        $this->add_vhc_history_record($vehicle, $maintenance, 'move', $user, 'maintenance');
        
        return $vehicle;
    }

    public function load_oc_certificates(Request $request) {
        $data = Request::input('oc_id');
        $message = "<option value=\"\" hidden>Seleccione un certificado</option>";

        if ($data) {
            $oc_certifications = OcCertification::where('oc_id', $data)->get();
            foreach ($oc_certifications as $oc_certification) {
                $message = $message.'<option value="'.$oc_certification->id.'">'.$oc_certification->code.' - Bs '.$oc_certification->amount.'</option>';
            }
        }

        if (Request::ajax()) {
            return response()->json($message);
        }
        return redirect()->back();
    }
}
