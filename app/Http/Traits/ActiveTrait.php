<?php
/**
 * User: Admininstrador
 * Date: 18/08/2018
 * Time: 22:13 PM
 */

namespace App\Http\Traits;

//use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\VehicleHistory;
use App\VehicleRequirement;
use Exception;

trait ActiveTrait {
    
    public function add_vhc_history_record($vehicle, $model, $mode, $user, $used_in) {
        $vehicle_history = new VehicleHistory;
        $vehicle_history->vehicle_id = $vehicle->id;

        // Datos por defecto si no se aplica ninguna condicion
        $vehicle_history->type = 'Cambio no detallado';
        $vehicle_history->contents = $user->name.' realizó un cambio en algún parámetro relacionado al vehículo (no se tiene detalles del cambio)';

        if ($used_in === 'requirement') {
            $requirement = $model;

            $vehicle_history->type = 'Requerimiento ('.VehicleRequirement::$types[$requirement->type].')';

            if ($mode === 'store') {    
                $vehicle_history->contents = $user->name.' elaboró un requerimiento para el vehículo '.$vehicle->type.' '.
                    $vehicle->model.' con placa '.$vehicle->license_plate.' con el siguiente motivo: '.$requirement->reason;
            } elseif ($mode === 'update') {
                $vehicle_history->type .= ' modificado';
                $vehicle_history->contents = $user->name.' modificó el requerimiento '.$requirement->code.' del vehículo '.
                    $vehicle->type.' '.$vehicle->model.' con placa '.$vehicle->license_plate.' con el siguiente detalle: '.
                    $requirement->reason;
            } elseif ($mode === 'reject') {
                $vehicle_history->type .= ' rechazado';
                $vehicle_history->contents = $user->name.' rechazó el requerimiento '.$requirement->code.' del vehículo '.
                    $vehicle->type.' '.$vehicle->model.' con placa '.$vehicle->licnese_plate.' debido a: '.$requirement->stat_obs;
            }
        } elseif ($used_in === 'vehicle') {
            if ($mode === 'store') {
                $vehicle_history->type = 'Nuevo registro';
                $vehicle_history->contents = 'El vehículo '.$vehicle->type.' '.$vehicle->model.' con placa '.
                    $vehicle->license_plate.' es registrado en el sistema de seguimiento de activos';
            } elseif ($mode === 'malfunction') {
                $vehicle_history->type = 'Reporte de falla';
                $vehicle_history->contents = ($user ? $user->name : 'Se').
                    ' reportó las siguientes condiciones en el vehículo: '.$vehicle->condition;
            } elseif ($mode === 'disable') {
                $vehicle_history->type = 'Baja de vehículo';
                $vehicle_history->contents = ($user ? $user->name : 'Se').
                    ' da de baja este vehículo por el siguiente motivo: '.$vehicle->condition;
            }
        } elseif ($used_in === 'maintenance') {
            $maintenance = $model;

            if ($mode === 'store' || $mode === 'move') {
                $vehicle_history->type = 'Mantenimiento';
                $vehicle_history->contents = 'El vehículo es puesto en mantenimiento '.$maintenance->type.' por '.$user->name;
            } elseif ($mode === 'close') {
                $vehicle_history->type = 'Fin de mantenimiento';
                $vehicle_history->contents = 'El vehículo sale de mantenimiento con el siguiente detalle de trabajos: '.
                    $maintenance->detail;
            }
        } elseif ($used_in === 'driver') {
            $driver = $model;
            
            $vehicle_history->type = $mode;

            if ($mode === 'Confirmación de recepción') {
                $vehicle_history->contents = ($driver->receiver ? $driver->receiver->name : '').' confirmó que recibió el vehículo '.
                    $vehicle->type.' '.$vehicle->model.' con placa '.$vehicle->license_plate.($driver->confirmation_obs ?
                        ' con las siguientes observaciones: '.$driver->confirmation_obs : '');
            } else {
                $vehicle_history->contents = 'Se entrega el vehículo '.$vehicle->type.' '.$vehicle->model.' con placa '.
                    $vehicle->license_plate.' a '.$driver->receiver->name;
            }
        } elseif ($used_in === 'file') {
            if ($mode === 'vehicle_file') {
                $file = $model;
                
                $vehicle_history->type = 'Carga de archivo';
                $vehicle_history->contents = 'El archivo "'.$file->description.'" es cargado al sistema por '.$user->name;
            }
        }

        $vehicle_history->status = $vehicle->status;
        $vehicle_history->historyable()->associate($model);
        $vehicle_history->save();
    }
}
