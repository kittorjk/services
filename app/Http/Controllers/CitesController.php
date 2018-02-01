<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\Cite;
use App\File;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Http\Traits\FilesTrait;

class CitesController extends Controller
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
        if ((is_null($user))||(!$user->id)) {
            return View('app.index', ['service'=>'cite', 'user'=>null]);
            //return redirect()->route('root');
        }
        if($user->acc_cite==0)
            return redirect()->action('LoginController@logout', ['service' => 'cite']);

        $service = Session::get('service');

        if($user->action->ct_vw_all /*($user->priv_level==3&&$user->area=='Gerencia General')*/||$user->priv_level==4){

            $prefix = Input::get('prefix');

            if($prefix=='gg')
                $cites = Cite::where('title','AB-GG'); //where('num_cite', '>', 0)
            elseif($prefix=='adm')
                $cites = Cite::where('title','AB-ADM');
            elseif($prefix=='tec')
                $cites = Cite::where('title','AB-GTEC');
            else
                $cites = Cite::where('num_cite', '>', 0);

            //$files = File::where('imageable_type', 'App\Cite')->get();
        }
        else{
            $cites = Cite::where('area', $user->area);
            /*
            $files = File::join('cites', 'files.imageable_id', '=', 'cites.id')
                ->select('files.id', 'files.name', 'files.path', 'files.type', 'files.size', 'files.imageable_id')
                ->where('area', $user->area)
                ->where('num_cite', '>', '0')
                ->where('imageable_type', 'App\Cite')
                ->get();
            */
        }

        if($user->priv_level!=4)
            $cites = $cites->where('asunto', 'not like', "%Néstor Romero%"); // Filter records referring to ADM

        $cites = $cites->orderBy('created_at', 'desc')->paginate(20);

        return View::make('app.cite_brief', ['cites' => $cites, 'service' => $service, 'user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $cod_cite = new Cite;
        
        //$session_user = User::where('id', $user->id)->first();
        $cod_cite->area = $user->area;

        if($user->area=='Gerencia Tecnica')
            $cod_cite->title = 'AB-GTEC';
        elseif($user->area=='Gerencia Administrativa')
            $cod_cite->title = 'AB-ADM';
        elseif($user->area=='Gerencia General')
            $cod_cite->title = 'AB-GG';
        else{
            Session::flash('message', "Ocurrió un error al recuperar sus datos de sesión, intente de nuevo por favor");
            return redirect()->route('cite.index');
        }

        $date = Carbon::now()->format('Y');

        $last_cite = Cite::where('area' , $cod_cite->area)
            ->whereYear('created_at', '=', $date)
            ->orderBy('num_cite', 'desc')->first();

        $cod_cite->num_cite = $last_cite ? $last_cite->num_cite+1 : 1;

        $cod_cite->created_at = Carbon::now();

        $prefixes = Cite::select('title')->where('title','<>','')->groupBy('title')->get();

        return View::make('app.cite_form', ['cite' => 0, 'user' => $user, 'service' => $service, 'cod_cite' => $cod_cite,
            'prefixes' => $prefixes]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');

        $v = \Validator::make(Request::all(), [
                'responsable'    => 'required|regex:/^[\pL\s\-]+$/u',
                'para_empresa'   => 'required',
                'destino'        => 'required|regex:/^[\pL\s\-]+$/u',
                'asunto'         => 'required',
            ],
            [
                'regex'          => 'El campo :attribute solo puede contener letras y espacios!',
                'required'       => 'Debe llenar el formulario!',
            ]
        );

        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back();
        }

        $cite = new Cite(Request::all());

        $cite->area = $user->area;

        if($user->priv_level==4){
            $cite->title = Request::input('cite_prefix');

            if($cite->title=='AB-GTEC')
                $cite->area = 'Gerencia Tecnica';
            elseif($cite->title=='AB-ADM')
                $cite->area = 'Gerencia Administrativa';
            elseif($cite->title=='AB-GG')
                $cite->area = 'Gerencia General';
            else{
                Session::flash('message', "Debe seleccionar un prefijo");
                return redirect()->route('cite.index');
            }
        }
        elseif($user->area=='Gerencia Tecnica'){
            $cite->title = 'AB-GTEC';
        }
        elseif($user->area=='Gerencia Administrativa') {
            $cite->title = 'AB-ADM';
        }
        elseif($user->area=='Gerencia General'){
            $cite->title = 'AB-GG';
        }
        else{
            Session::flash('message', "Ocurrió un error al recuperar sus datos de sesión, intente de nuevo por favor");
            return redirect()->route('cite.index');
        }

        $date = Carbon::now()->format('Y');

        $last_cite = Cite::where('area' , $cite->area)
            ->whereYear('created_at', '=', $date)
            ->orderBy('num_cite', 'desc')->first();

        $cite->num_cite = $last_cite ? $last_cite->num_cite+1 : 1;

        $cite->code = $cite->title.'-'.str_pad($cite->num_cite, 3, "0", STR_PAD_LEFT).date('-Y');
        //$cite->title.'-'.str_pad($cite->num_cite, 3, "0", STR_PAD_LEFT).date_format($cite->created_at,'-Y');

        $cite->user_id = $user->id;
        $cite->save();

        return View::make('app.cite_success', ['service' => $service, 'cite' => $cite]);
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
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $cite = Cite::find($id);

        $prefixes = Cite::select('title')->where('title','<>','')->groupBy('title')->get();

        return View::make('app.cite_form', ['cite' => $cite, 'user' => $user, 'service' => $service,
            'prefixes' => $prefixes]);
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
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $cite = Cite::find($id);
        $cite->fill(Request::all());
        //$cite->user_id = $user->id;

        if(empty($cite->responsable)||empty($cite->para_empresa)||empty($cite->destino)||empty($cite->asunto)){
            Session::flash('message', "Debe llenar el formulario!");
            return redirect()->back();
        }
        
        if($user->priv_level==4){
            $prefix = Request::input('cite_prefix');
            if($prefix!=$cite->title){
                $cite->title = $prefix;

                if($cite->title=='AB-GTEC')
                    $cite->area = 'Gerencia Tecnica';
                elseif($cite->title=='AB-ADM')
                    $cite->area = 'Gerencia Administrativa';
                elseif($cite->title=='AB-GG')
                    $cite->area = 'Gerencia General';
                else{
                    Session::flash('message', "Debe seleccione un prefijo");
                    return redirect()->route('cite.index');
                }

                $date = Carbon::now()->format('Y');

                $last_cite = Cite::where('area' , $cite->area)
                    ->whereYear('created_at', '=', $date)
                    ->orderBy('num_cite', 'desc')->first();

                $cite->num_cite = $last_cite ? $last_cite->num_cite+1 : 1;

                $cite->code = $cite->title.'-'.str_pad($cite->num_cite, 3, "0", STR_PAD_LEFT).date('-Y');
            }
        }

        $cite->save();

        Session::flash('message', "Datos actualizados correctamente");
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('cite.index');
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

        $cite = Cite::find($id);
        //$files = File::where('imageable_id', $id)->where('imageable_type','App\Cite')->get();

        $file_error = false;

        foreach($cite->files as $file){
            /*
            $success = true;

            try {
                \Storage::disk('local')->delete($file->name);
            } catch (ModelNotFoundException $ex) {
                $success = false;
                $file_error = true;
            }

            if($success)
                $file->delete();
            */
            $file_error = $this->removeFile($file);
            if($file_error)
                break;
        }

        /*
        try {
            $delete_word = File::where('imageable_id',$id)->where('imageable_type','App\Cite')
                ->wherein('type', ['doc','docx'])->firstOrFail()->name;
            \Storage::disk('local')->delete($delete_word);
            $exito=$exito+1;
        } catch (ModelNotFoundException $ex) {
            $exito++;
        }
        */

        if (!$file_error) {
            $cite->delete();

            Session::flash('message', "El registro fue eliminado del sistema");
            if(Session::has('url'))
                return redirect(Session::get('url'));
            else
                return redirect()->route('cite.index');
        }
        else {
            Session::flash('message', "Error al borrar el registro, por favor consulte al administrador. $file_error");
            return redirect()->back();
        }
    }

    //Search function moved to a separate Controller
    /*
    public function buscar()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        return View::make('app.buscar', ['user' => $user]);
    }

    public function resultado(Request $request)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $parametro = Request::input('parametro');
        $buscar = Request::input('buscar');
        $fecha_desde = Request::input('fecha_desde');
        $fecha_hasta = Request::input('fecha_hasta');
        $fecha_hasta = $fecha_hasta.' 23:59:59';

        $current_user = User::find($user->id);

        if($current_user->priv_level == 4){
            if (Request::has('fecha_desde')){
                $cites = Cite::whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                    ->where('num_cite', '>', '0')
                    ->orderBy('created_at', 'desc')->paginate(20);
            }
            else{
                if($parametro=='codigo_cite')
                {
                    $v = \Validator::make(Request::all(), [
                        'buscar'    => 'min:14|max:16|alpha_dash',
                    ]);

                    if ($v->fails())
                    {
                        Session::flash('message', 'Introduzca un codigo de CITE válido!');
                        return redirect()->back();
                    }

                    $array_buscar = explode('-',$buscar);
                    $area_cite = $array_buscar[0].'-'.$array_buscar[1];
                    $cites = Cite::where('num_cite', $array_buscar[2])->whereYear('created_at','=',$array_buscar[3])
                        ->where('title',$area_cite)
                        ->paginate(20);
                }
                else
                {
                    $cites = Cite::where('num_cite', '>', '0')->where("$parametro", 'like', "%$buscar%")
                        ->orderBy('created_at', 'desc')->paginate(20);
                }
            }
            $files = File::all();
        }
        else{
            if (Request::has('fecha_desde')) {
                $cites = Cite::where('area', $user->area)->whereBetween('created_at',[$fecha_desde,$fecha_hasta])
                    ->where('num_cite', '>', '0')
                    ->orderBy('created_at', 'desc')->paginate(20);
            }
            else{
                if($parametro=='codigo_cite')
                {
                    $v = \Validator::make(Request::all(), [
                        'buscar'    => 'min:14|max:16|alpha_dash',
                    ]);

                    if ($v->fails())
                    {
                        Session::flash('message', 'Introduzca un codigo de CITE válido!');
                        return redirect()->back();
                    }

                    $array_buscar = explode('-',$buscar);
                    $area_cite = $array_buscar[0].'-'.$array_buscar[1];
                    $cites = Cite::where('num_cite', $array_buscar[2])->whereYear('created_at','=',$array_buscar[3])
                        ->where('title',$area_cite)->where('area', $user->area)
                        ->paginate(20);
                }
                else
                {
                    $cites = Cite::where('area', $user->area)->where("$parametro", 'like', "%$buscar%")
                        ->where('num_cite', '>', '0')
                        ->orderBy('created_at', 'desc')->paginate(20);
                }
            }
            $files = File::join('cites', 'files.imageable_id', '=', 'cites.id')
                ->select('files.id', 'files.name', 'files.path', 'files.type', 'files.size', 'files.imageable_id')
                ->where('area', $user->area)
                ->where('num_cite', '>', '0')
                ->get();
        }

        return View::make('app.list', ['cites' => $cites, 'files' => $files, 'user' => $user]);
    }
    */
}
