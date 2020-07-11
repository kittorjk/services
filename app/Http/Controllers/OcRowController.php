<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use View;
use Hash;
use Input;
use Exception;
use App\OC;
use App\OcRow;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class OcRowController extends Controller
{
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
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
          return redirect()->route('root');
      
      $v = \Validator::make(Request::all(), [
          'oc_id'                   => 'required|exists:o_c_s,id',
          'num_order'               => 'required',
          'description'             => 'required|max:1000',
          'qty'                     => 'required',
          'units'                   => 'required',
          'unit_cost'               => 'required'
      ],
          [
              'oc_id.required'                => 'Debe especificar la OC a la que pertenece el certificado!',
              'oc_id.exists'                  => 'El número de OC indicado no existe en este sistema!',
              'num_order.required'            => 'Debe especificar un número de posición',
              'description.required'          => 'Debe especificar la desccripción del item',
              'description.max'               => 'La descripción proporcionada es muy larga!',
              'qty.required'                  => 'Debe especificar la cantidad',
              'units.required'                => 'Debe especificar las unidades',
              'unit_cost.required'            => 'Debe especificar el precio unitario' 
          ]
      );

      if ($v->fails()) {
          Session::flash('message', $v->messages()->first());
          return redirect()->back()->withInput();
      }

      $row = new OcRow(Request::all());

      $oc = OC::find($row->oc_id);

      if ($oc->rows->count() >= 30) {
        Session::flash('message', 'Ya no puede agregar mas items a esta OC! Cree una OC complementaria para el excedente.');
        return redirect()->back()->withInput();
      }
      
      $row->user_id = $user->id;

      $row->save();
      
      Session::flash('message', "El item fue registrado correctamente");
      if (Session::has('url'))
          return redirect(Session::get('url'));
      else
          return redirect()->action('OcController@show', ['id' => $row->oc_id]);

      // return redirect()->route('oc.index');
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
      $user = Session::get('user');
      if ((is_null($user)) || (!$user->id))
          return redirect()->route('root');
      
      $v = \Validator::make(Request::all(), [
          'oc_id'                   => 'required|exists:o_c_s,id',
          'num_order'               => 'required',
          'description'             => 'required|max:1000',
          'qty'                     => 'required',
          'units'                   => 'required',
          'unit_cost'               => 'required'
      ],
          [
              'oc_id.required'                => 'Debe especificar la OC a la que pertenece el certificado!',
              'oc_id.exists'                  => 'El número de OC indicado no existe en este sistema!',
              'num_order.required'            => 'Debe especificar un número de posición',
              'description.required'          => 'Debe especificar la desccripción del item',
              'description.max'               => 'La descripción proporcionada es muy larga!',
              'qty.required'                  => 'Debe especificar la cantidad',
              'units.required'                => 'Debe especificar las unidades',
              'unit_cost.required'            => 'Debe especificar el precio unitario' 
          ]
      );

      if ($v->fails()) {
          Session::flash('message', $v->messages()->first());
          return redirect()->back()->withInput();
      }

      $row = OcRow::find($id);
      $row->fill(Request::all());

      $row->save();
      
      Session::flash('message', "El item fue modificado correctamente");
      if (Session::has('url'))
          return redirect(Session::get('url'));
      else
          return redirect()->action('OcController@show', ['id' => $row->oc_id]);
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

      $row = OcRow::find($id);

      if ($row) {
          $to_return_id = $row->oc_id;
          
          $row->delete();

          Session::flash('message', "El registro fue eliminado del sistema");

          if (Session::has('url'))
              return redirect(Session::get('url'));
          else
              return redirect()->action('OcController@show', ['id' => $to_return_id]);
      } else {
          Session::flash('message', "Error al ejecutar el borrado, no se encontró el registro solicitado.");
          return redirect()->back();
      }
    }
}
