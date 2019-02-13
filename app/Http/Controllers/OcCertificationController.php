<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Mail;
use Hash;
use Input;
use Exception;
use App\OC;
use App\OcCertification;
use App\File;
use App\User;
use App\Invoice;
use App\Email;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class OcCertificationController extends Controller
{
    use FilesTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id)) {
            return View('app.index', ['service' => 'oc', 'user' => null]);
        }
        if($user->acc_oc==0)
            return redirect()->action('LoginController@logout', ['service' => 'oc']);

        $service = Session::get('service');
        
        if($user->priv_level>=2)
        {
            $certificates = OcCertification::where('id', '>', 0)->orderBy('id', 'desc')->paginate(20);
            //$db_query = OC::where('id', '>', 0)->orderBy('id', 'desc')->get();
        } else {
            Session::flash('message', "Usted no tiene permiso para acceder a este sitio!");
            return redirect()->back();
        }
        
        //Session::put('db_query', $db_query);

        return View::make('app.oc_certification_brief', ['certificates' => $certificates,
            'service' => $service, 'user' => $user ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $preselected_id = Input::get('id') ?: 0;

        $oc = OC::find($preselected_id);

        if(!$oc){
            Session::flash('message', 'Ocurrió un error al recuperar la información del servidor, intente de nuevo por favor');
            return redirect()->back();
        }
        if($oc->status=='Anulada'){
            Session::flash('message', "No se puede emitir un certificado para esta OC porque ha sido anulada!");
            return redirect()->back();
        }
        /* Executed amount automatically updated by the system
        if($oc->executed_amount==0){
            Session::flash('message', 'Debe especificar el monto ejecutado de la orden antes de emitir un certificado!');
            return redirect()->back();
        }
        */
        foreach($oc->certificates as $certificate){
            if($certificate->type_reception=='Total'){
                Session::flash('message', 'Ya se emitió un certificado de aceptación total de esta OC!');
                return redirect()->back();
            }
        }

        return View::make('app.oc_certification_form', ['certificate' => 0, 'preselected_id' => $preselected_id,
            'service' => $service, 'user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        $v = \Validator::make(Request::all(), [
            'oc_id'                   => 'required|exists:o_c_s,id',
            'amount'                  => 'required',
            'type_reception'          => 'required',
            'date_ack'                => 'required|date',
            'date_acceptance'         => 'required|date|after:date_ack',
        ],
            [
                'oc_id.required'                => 'Debe especificar la OC a la que pertenece el certificado!',
                'oc_id.exists'                  => 'El número de OC indicado no existe en este sistema!',
                'amount.required'               => 'Debe especificar el monto que se certifica!',
                'type_reception.required'       => 'Debe especificar el tipo de aceptación!',
                'date_ack.required'             => 'Debe especificar la fecha en la que se comunicó la entrega!',
                'date_ack.date'                 => 'El formato de la fecha de comunicación de entrega es incorrecto!',
                'date_acceptance.required'      => 'Debe especificar la fecha en la que hizo la aceptación!',
                'date_acceptance.date'          => 'El formato de la fecha de aceptación es incorrecto!',
                'date_acceptance.after'         => 'La fecha de comunicación de entrega debe ser anterior a la aceptación!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $certificate = new OcCertification(Request::all());

        $oc = OC::find($certificate->oc_id);
        $oc->executed_amount += $certificate->amount;
        
        // if(number_format($oc->oc_amount,2)-number_format($oc->executed_amount,2)<0){
        if ($oc->executed_amount > $oc->oc_amount) {
            Session::flash('message', 'El monto total certificado excede el monto asignado a la OC!
                Cree una OC complementaria para el excedente.');
            return redirect()->back()->withInput();
        }
        
        $prev_total = OcCertification::where('oc_id',$certificate->oc_id)->where('type_reception','Total')->get();

        if($prev_total->count()>0){
            Session::flash('message', "Ésta OC ya tiene un certificado de aceptación total!");
            return redirect()->back()->withInput();
        }

        if($certificate->type_reception=='Parcial'){
            $prev_number = OcCertification::where('oc_id', $certificate->oc_id)->where('type_reception','Parcial')
                ->orderBy('num_reception','desc')->first();

            $certificate->num_reception = $prev_number&&$prev_number->num_reception!=0 ? $prev_number->num_reception+1 : 1;
        }
        
        //$certificate->amount = $oc->executed_amount!=0 ? $oc->executed_amount : $oc->oc_amount;
        $certificate->user_id = $user->id;

        $certificate->save();

        $this->fill_code_column();

        /* Update executed amount on OC */
        $oc->save();
        
        Session::flash('message', "El certificado fue agregado al sistema correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('oc_certificate.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $certificate = OcCertification::find($id);

        $certificate->date_ack = Carbon::parse($certificate->date_ack);
        $certificate->date_acceptance = Carbon::parse($certificate->date_acceptance);
        $certificate->created_at = Carbon::parse($certificate->created_at)->hour(0)->minute(0)->second(0);
        $certificate->updated_at = Carbon::parse($certificate->updated_at)->hour(0)->minute(0)->second(0);
        
        return View::make('app.oc_certification_info', ['certificate' => $certificate, 'service' => $service,
            'user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $certificate = OcCertification::find($id);

        $certificate->date_ack = Carbon::parse($certificate->date_ack)->format('Y-m-d');
        $certificate->date_acceptance = Carbon::parse($certificate->date_acceptance)->format('Y-m-d');

        return View::make('app.oc_certification_form', ['certificate' => $certificate, 'preselected_id' => 0,
            'service' =>$service, 'user' => $user]);
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
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        $v = \Validator::make(Request::all(), [
            'oc_id'                   => 'required|exists:o_c_s,id',
            'amount'                  => 'required',
            'type_reception'          => 'required',
            'date_ack'                => 'required|date',
            'date_acceptance'         => 'required|date|after:date_ack',
        ],
            [
                'oc_id.required'                => 'Debe especificar la OC a la que pertenece el certificado!',
                'oc_id.exists'                  => 'El número de OC indicado no existe en este sistema!',
                'amount.required'               => 'Debe especificar el monto ejecutado!',
                'type_reception.required'       => 'Debe especificar el tipo de aceptación!',
                'date_ack.required'             => 'Debe especificar la fecha en la que se comunicó la entrega!',
                'date_ack.date'                 => 'El formato de la fecha de comunicación de entrega es incorrecto!',
                'date_acceptance.required'      => 'Debe especificar la fecha en la que hizo la aceptación!',
                'date_acceptance.date'          => 'El formato de la fecha de aceptación es incorrecto!',
                'date_acceptance.after'         => 'La fecha de comunicación de entrega debe ser anterior a la aceptación!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $certificate = OcCertification::find($id);
        $old_amount = $certificate->amount;

        $certificate->fill(Request::all());

        $oc = OC::find($certificate->oc_id);
        $oc->executed_amount = $oc->executed_amount - $old_amount + $certificate->amount;

        if($oc->executed_amount > $oc->oc_amount){
            Session::flash('message', 'El monto total certificado excede el monto asignado a la OC!
                Cree una OC complementaria para el excedente.');
            return redirect()->back()->withInput();
        }

        if($certificate->type_reception=='Total'){
            $prev_total = OcCertification::where('id','<>',$certificate->id)->where('oc_id',$certificate->oc_id)
                ->where('type_reception','Total')->get();

            if($prev_total->count()>0){
                Session::flash('message', "Ésta OC ya tiene un certificado de aceptación total!");
                return redirect()->back()->withInput();
            }
        }

        if($certificate->type_reception=='Parcial'){
            $prev_number = OcCertification::where('oc_id',$certificate->oc_id)->where('type_reception','Parcial')
                ->orderBy('num_reception','desc')->first();

            $certificate->num_reception = $prev_number&&$prev_number->num_reception!=0 ? $prev_number->num_reception+1 : 1;
        }
        
        $certificate->save();

        /* Update executed amount on OC */
        $oc->save();

        Session::flash('message', "El certificado fue modificado correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('oc_certificate.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $certificate = OcCertification::find($id);

        /*
        foreach($certificate->files as $file){
            $exito = 0;

            try {
                \Storage::disk('local')->delete($file->name);
                $exito++;
            } catch (ModelNotFoundException $ex) {
                $exito++;
            }

            if($exito>0)
                $file->delete();
        }
        */
        if ($certificate) {
            /* Restore OC executed amount to its previous value */
            $oc = $certificate->oc; //OC::find($certificate->oc_id);
            $oc->executed_amount = $oc->executed_amount - $certificate->amount;
            $oc->save();

            $file_error = false;

            foreach($certificate->files as $file){
                $file_error = $this->removeFile($file);
                if($file_error)
                    break;
            }

            if (!$file_error) {
                $certificate->delete();

                Session::flash('message', "El certificado fiue eliminado del sistema correctamente");
                if(Session::has('url'))
                    return redirect(Session::get('url'));
                else
                    return redirect()->route('oc_certificate.index');
                    //return redirect()->route('oc.index');
            }
            else {
                Session::flash('message', "Error al borrar el registro, por favor consulte al administrador. $file_error");
                return redirect()->back();
            }
        }
        else {
            Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado.");
            return redirect()->back();
        }
    }
    
    public function print_ack($code)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $certificate = OcCertification::where('code', $code)->first();

        $certificate->date_print_ack = Carbon::now();
        
        $certificate->save();

        Session::flash('message', "El certificado fue marcado como entregado en formato impreso al encargado administrativo");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('oc_certificate.index');
    }

    public function fill_code_column()
    {
        $certificates = OcCertification::where('code','')->get();

        foreach($certificates as $certificate){
            $certificate->code = 'CFD-'.date_format($certificate->created_at,'ymd').'-'.
                str_pad($certificate->id, 3, "0", STR_PAD_LEFT);

            $certificate->save();
        }
    }
}
