<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use App\Bill;
use App\Calibration;
use App\Cite;
use App\Contact;
use App\Contract;
use App\Driver;
use App\Email;
use App\User;
use App\OC;
use App\Project;
use App\File;
use App\Guarantee;
use App\License;
use App\Maintenance;
use App\Provider;
use App\Task;
use App\Site;
use App\Item;
use App\Assignment;
use App\Invoice;
use App\Device;
use App\Operator;
use App\Order;
use App\Vehicle;
use App\DeadInterval;
use App\DeviceHistory;
use App\OcCertification;
use App\ServiceParameter;
use App\VehicleHistory;
use App\Material;
use App\Warehouse;
use App\WarehouseEntry;
use App\WarehouseOutlet;
use App\ExportedFiles;
use App\RbsViatic;
use App\RbsViaticRequest;
use App\ClientListedMaterial;
use App\RbsSiteCharacteristic;
use App\DeviceRequirement;
use App\VehicleRequirement;
use App\CorpLine;
use App\CorpLineAssignation;
use App\CorpLineRequirement;
use App\ItemCategory;
use App\VhcFailureReport;
use App\DvcFailureReport;
use App\Branch;
use App\Employee;
use App\StipendRequest;
use App\Tender;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Worksheet_Drawing;
use Carbon\Carbon;
use BaconQrCode\Encoder\QrCode;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\DB;

class ExcelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($table)
    {
        $excel_name = 'empty';
        $sheet_name = 'empty';
        $sheet_content = collect();

        if ($table == 'assignments') {
            $excel_name = 'Base de asignaciones';
            $sheet_name = 'Asignaciones';

            $assignments = Assignment::all();

            foreach($assignments as $assignment)
            {
                $sheet_content->prepend(
                    [   'Código'                => $assignment->code,
                        'C.C.'                  => $assignment->cost_center,
                        'Asignación'            => $assignment->name,
                        'Proyecto'              => $assignment->project ? $assignment->project->name : '',
                        'Cliente'               => $assignment->client,
                        'Área de trabajo'       => $assignment->type,
                        'Oficina'               => $assignment->branch,
                        'Estado'                => $assignment->statuses($assignment->status),
                        'porcentaje de avance'  => number_format($assignment->percentage_completed,2).' %',
                        'Project Manager'       => $assignment->responsible ? $assignment->responsible->name : '',
                        'Inicio cotización'     => $assignment->quote_from==0 ? '' :
                            date_format(Carbon::parse($assignment->quote_from),'d-m-Y'),
                        'Fin cotización'        => $assignment->quote_to==0 ? '' :
                            date_format(Carbon::parse($assignment->quote_to),'d-m-Y'),
                        'Inicio ejecución'      => $assignment->start_date==0 ? '' :
                            date_format(Carbon::parse($assignment->start_date),'d-m-Y'),
                        'Fin ejecución'         => $assignment->end_date==0 ? '' :
                            date_format(Carbon::parse($assignment->end_date),'d-m-Y'),
                        'Inicio cobro'          => $assignment->billing_from==0 ? '' :
                            date_format(Carbon::parse($assignment->billing_from),'d-m-Y'),
                        'Fin cobro'             => $assignment->billing_to==0 ? '' :
                            date_format(Carbon::parse($assignment->billing_to),'d-m-Y'),
                        'Creación'              => date_format($assignment->created_at,'d-m-Y'),
                        'Última actualización'  => date_format($assignment->updated_at,'d-m-Y'),
                    ]);
            }

            $this->record_export('/assignment','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'bill_order') {
            $excel_name = 'Base de asociaciones Orden-Factura';
            $sheet_name = 'Asociaciones Orden-Factura';

            $bills = Bill::all();

            foreach($bills as $bill)
            {
                foreach($bill->orders as $order){

                    $sheet_content->prepend(
                        [   'Factura'               => $bill->code,
                            'Orden'                 => $order->type.' '.$order->code,
                            'Monto facturado'       => $order->pivot->charged_amount.' Bs',
                            'Estado'                => $order->pivot->status==0 ? 'Pendiente' : 'Cobrado',
                            'Última modificación'   => date_format($order->pivot->updated_at,'d/m/Y'),
                        ]);
                }
            }

            $this->record_export('/bill','Full pivot table bill_order',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'bills') {
            $excel_name = 'Base de facturas';
            $sheet_name = 'Facturas';

            $bills = Bill::all();

            foreach($bills as $bill)
            {
                $sheet_content->prepend(
                    [   'Número de factura'     => $bill->code,
                        'Fecha de emisión'      => Carbon::parse($bill->date_issued)->format('d/m/Y'),
                        'Monto facturado'       => number_format($bill->billed_price,2).' Bs',
                        'Estado'                => $bill->status==0 ? 'Pendiente' : 'Cobrada' ,
                        'Fecha de cobro'        => $bill->status==1 ?
                            Carbon::parse($bill->date_charged)->format('d/m/Y') : '',
                        'Información adicional' => $bill->detail,
                    ]);
            }

            $this->record_export('/bill','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'branches') {
            $excel_name = 'Tabla de sucursales';
            $sheet_name = 'Sucursales';

            $branches = Branch::all();

            $this->record_export('/branch', 'Full table branches', 0);

            return $this->create_excel($excel_name, $sheet_name, $branches);
        }

        if ($table == 'calibrations') {
            $excel_name = 'Base de calibración de equipos';
            $sheet_name = 'Calibraciones';

            $calibrations = Calibration::all();

            foreach($calibrations as $calibration)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $calibration->id,
                        'ID Usuario'            => $calibration->user_id,
                        'Usuario'               => $calibration->user->name,
                        'ID Equipo'             => $calibration->device_id,
                        'Equipo'                => $calibration->device->type.' '.$calibration->device->model.
                            ' - Serial: '.$calibration->device->serial,
                        'Fecha ingreso calibración' => Carbon::parse($calibration->date_in)->format('d/m/Y'),
                        'Fecha salida calibración' => $calibration->completed==1 ?
                            Carbon::parse($calibration->date_out)->format('d/m/Y') : '',
                        'Detalle'               => wordwrap($calibration->detail, 70, "\n", false),
                        'Estado'                => $calibration->completed==1 ? 'Finalizado' : 'En calibración',
                        'Fecha creación registro' => date_format($calibration->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($calibration->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/calibration','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'cites') {
            $excel_name = 'Base de CITES';
            $sheet_name = 'CITES';

            $cites = Cite::all();

            foreach($cites as $cite)
            {
                $sheet_content->prepend(
                    [   'Nº CITE'       => $cite->code,
                        'Fecha'         => date_format($cite->created_at,'d-m-Y'),
                        'Area'          => $cite->area,
                        'Responsable'   => $cite->responsable,
                        'Para_empresa'  => $cite->para_empresa,
                        'Destinatario'  => $cite->destino,
                        'Asunto'        => $cite->asunto
                    ]);
            }

            $this->record_export('/cite','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'contacts') {
            $excel_name = 'Base de contactos';
            $sheet_name = 'Contactos';

            $contacts = Contact::all();

            $i = 1;

            foreach($contacts as $contact)
            {
                $sheet_content->push(
                    [   '#'                     => $i,
                        'Nombre'                => $contact->name,
                        'Empresa'               => $contact->company,
                        'Cargo'                 => $contact->position,
                        'Teléfono principal'    => $contact->phone_1,
                        'Teléfono alternativo'  => $contact->phone_2,
                        'Correo'                => $contact->email,
                    ]);

                $i++;
            }

            $this->record_export('/contact','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        //Contracts merged with Projects (all contract functions moved or merged with projects' functions)
        /*
        if ($table == 'contracts') {
            $excel_name = 'Base de contratos';
            $sheet_name = 'Contratos';

            $contracts = Contract::all();

            foreach($contracts as $contract)
            {
                $sheet_content->prepend(
                    [   'Código interno'        => $contract->code,
                        'Código de cliente'     => $contract->client_code,
                        'Cliente'               => $contract->client,
                        'Objeto'                => $contract->objective,
                        'Fecha de inicio'       => Carbon::parse($contract->start_date)->format('d-m-Y'),
                        'Fecha de vencimiento'  => Carbon::parse($contract->expiration_date)->format('d-m-Y'),
                        'Condición'             => $contract->closed==1 ? 'No renovable' : 'Renovable',
                    ]);
            }

            $this->record_export('/contract','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        */

        if ($table == 'corp_line_assignations') {
            $excel_name = 'Tabla de asignaciones de líneas corporativas';
            $sheet_name = 'Asignaciones';

            $assignations = CorpLineAssignation::all();

            $this->record_export('/line_assignation','Full table corp_line_assignations', 0);

            return $this->create_excel($excel_name, $sheet_name, $assignations);
        }

        if ($table == 'corp_line_requirements') {
            $excel_name = 'Tabla de requerimientos de líneas corporativas';
            $sheet_name = 'Requerimientos';

            $requirements = CorpLineRequirement::all();

            $this->record_export('/line_requirement','Full table corp_line_requirements', 0);

            return $this->create_excel($excel_name, $sheet_name, $requirements);
        }

        if ($table == 'corp_lines') {
            $excel_name = 'Tabla de líneas corporativas';
            $sheet_name = 'Líneas corporativas';

            $lines = CorpLine::all();

            foreach($lines as $line)
            {
                $sheet_content->prepend(
                    [   'Nro'                   => $line->id,
                        'Número'                => $line->number,
                        'Área de servicio'      => $line->service_area,
                        'Tecnología'            => $line->technology,
                        'Código PIN'            => $line->pin,
                        'Código PUK'            => $line->puk,
                        'Consumo promedio'      => $line->avg_consumption!=0 ? $line->avg_consumption : '',
                        'Crédito asignado'      => $line->credit_assigned!=0 ? $line->credit_assigned : '',
                        'Estado'                => $line->status,
                        'Responsable'           => $line->responsible ? $line->responsible->name : '',
                        'Observaciones'         => $line->observations,
                        'Fecha de registro'     => date_format($line->created_at,'d-m-Y h:i:s'),
                        'Última modificación'   => date_format($line->updated_at,'d-m-Y h:i:s')
                    ]);
            }

            $this->record_export('/corporate_line','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'dead_intervals') {
            $excel_name = 'Base de intervalos de tiempo muerto';
            $sheet_name = 'Intervalos de tiempo muerto';

            $dead_intervals = DeadInterval::all();

            foreach($dead_intervals as $dead_interval)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $dead_interval->id,
                        'Responsable'           => $dead_interval->user ? $dead_interval->user->name : '',
                        'Desde'                 => date_format(Carbon::parse($dead_interval->date_from),'d/m/Y'),
                        'Hasta'                 => date_format(Carbon::parse($dead_interval->date_to),'d/m/Y'),
                        'Total en días'         => $dead_interval->total_days,
                        'Motivo'                => wordwrap($dead_interval->reason, 70, "\n", false),
                        'Estado'                => $dead_interval->closed==0 ? 'Activo' : 'Cerrado',
                        'Morph_id'              => $dead_interval->relatable_id,
                        'Morph_type'            => $dead_interval->relatable_type,
                        'Fecha creación registro' => date_format($dead_interval->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($dead_interval->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/dead_interval?assig_id=id','Full table for dead intervals',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'device_histories') {
            $excel_name = 'Base - historial de equipos';
            $sheet_name = 'Historial de equipos';

            $device_histories = DeviceHistory::all();

            foreach($device_histories as $history)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $history->id,
                        'ID equipo'             => $history->device_id,
                        'Equipo'                => $history->device->type.' '.$history->device->model.
                            ' - Serie: '.$history->device->serial,
                        'Tipo de registro'      => $history->type,
                        'Contenido'             => wordwrap($history->contents, 70, "\n", false),
                        'Estado'                => $history->status,
                        'Morph ID'              => $history->historyable_id,
                        'Morph type'            => $history->historyable_type,
                        'Fecha creación registro' => date_format($history->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($history->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/history/device/id','Full table for device histories',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'device_requirements') {
            $excel_name = 'Tabla de requerimientos de equipos';
            $sheet_name = 'Requerimientos';

            $requirements = DeviceRequirement::all();

            $this->record_export('/device_requirement','Full table device_requirements',0);

            return $this->create_excel($excel_name, $sheet_name, $requirements);
        }

        if ($table == 'devices') {
            $excel_name = 'Base de Equipos';
            $sheet_name = 'Lista de equipos';

            if(Session::has('db_query'))
                $devices = Session::get('db_query');
            else
                $devices = Device::where('status','<>','Baja')->get(); //Device::all();

            $i=1;

            foreach($devices as $device)
            {
                $sheet_content->push(
                    [   'Número'            => $i,
                        'Número de serie'   => $device->serial,
                        'Tipo'              => $device->type,
                        'Modelo'            => $device->model,
                        'Propietario'       => $device->owner,
                        'Sucursal'          => $device->branch,
                        'Estado'            => $device->status,
                        'Responsable actual' => ($device->responsible!=0 ?
                            ($device->last_operator&&$device->last_operator->confirmation_flags[3]==1 ?
                                $device->user->name : $device->user->name.' (Por confirmar)') : 'Sin asignar'),
                        'Destinado a'       => $device->destination,
                        'Condiciones'       => $device->condition,
                    ]);

                $i++;
            }

            $this->record_export('/device','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'drivers') {
            $excel_name = 'Base de asignación de vehículos';
            $sheet_name = 'Asignaciones';

            $drivers = Driver::all();

            foreach($drivers as $driver)
            {
                $sheet_content->prepend(
                    [   'Fecha'                 => Carbon::parse($driver->date)->format('d/m/Y'),
                        'Vehículo'              => $driver->vehicle->type.' '.$driver->vehicle->model,
                        'Placa'                 => $driver->vehicle->license_plate,
                        'Entregado por'         => $driver->confirmation_flags[2]==0 ?
                            $driver->deliverer->name.' (por confirmar)' : $driver->deliverer->name,
                        'Entregado a'           => $driver->confirmation_flags[3]==0 ?
                            $driver->receiver->name.' (por confirmar)' : $driver->receiver->name,
                        'Área de trabajo'       => $driver->project_type,
                        'Destino'               => $driver->destination,
                        'Motivo'                => $driver->reason,
                        'Kilometraje'           => $driver->mileage_before.' Km',
                        'Observaciones'         => $driver->observations,
                    ]);
            }

            $this->record_export('/driver','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'dvc_failure_reports') {
            $excel_name = 'Tabla de reportes de falla - equipos';
            $sheet_name = 'Reportes de falla';

            $reports = DvcFailureReport::all();

            $this->record_export('/device_failure_report', 'Full table dvc_failure_reports', 0);

            return $this->create_excel($excel_name, $sheet_name, $reports);
        }

        if ($table == 'emails') {
            $excel_name = 'Base de correos';
            $sheet_name = 'Emails enviados';

            $emails = Email::all();

            foreach($emails as $email)
            {
                $sheet_content->prepend(
                    [   '#'                     => $email->id,
                        'Enviado por'           => $email->sent_by,
                        'Enviado a'             => $email->sent_to,
                        'Carbon Copy'           => $email->sent_cc,
                        'Asunto'                => $email->subject,
                        'Contenido'             => wordwrap($email->content, 70, "\n", false),
                        'Estado'                => $email->success==0 ? 'Envío fallido' : 'Envío exitoso',
                        'Fecha de envio'        => Carbon::parse($email->created_at)->format('d-m-Y'),
                    ]);
            }

            $this->record_export('/email','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'employees') {
            $excel_name = 'Tabla de personal';
            $sheet_name = 'Personal';

            $employees = Employee::all();

            $this->record_export('/employee', 'Full table employees', 0);

            return $this->create_excel($excel_name, $sheet_name, $employees);
        }

        if ($table == 'files') {
            $excel_name = 'Base de archivos';
            $sheet_name = 'Archivos';

            $files = File::all();

            $i = 1;

            foreach($files as $file)
            {
                $sheet_content->push(
                    [   '#'                     => $i,
                        'Nombre'                => $file->name,
                        'Descripción'           => $file->description,
                        'Ubicación'             => $file->path,
                        'Tipo'                  => $file->type,
                        'Tamaño'                => number_format($file->size,2).' KB',
                        'Morph id'              => $file->imageable_id,
                        'Morph type'            => $file->imageablr_type,
                        'Estado'                => $file->status==0 ? 'Abierto' : 'Bloqueado',
                        'Subido por'            => $file->user->name,
                        'Fecha de subida'       => Carbon::parse($file->created_at)->format('d/m/Y'),
                        'Última modificación'   => Carbon::parse($file->updated_at)->format('d/m/Y'),
                    ]);
                $i++;
            }

            $this->record_export('/file','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'guarantees') {
            $excel_name = 'Base de polizas';
            $sheet_name = 'Polizas';

            $guarantees = Guarantee::all();

            foreach($guarantees as $guarantee)
            {
                $sheet_content->prepend(
                    [   'Poliza'                => $guarantee->code,
                        'Empresa'               => $guarantee->company,
                        'Tipo'                  => $guarantee->type,
                        'Objeto'                => wordwrap($guarantee->applied_to, 50, "\n", false),
                        //$guarantee->guaranteeable&&$guarantee->guaranteeable_type=='App\Assignment' ?
                        //wordwrap('Proyecto: '.$guarantee->guaranteeable->name, 70, "\n", false) : '',
                        'Fecha de inicio'       => Carbon::parse($guarantee->start_date)->format('d-m-Y'),
                        'Fecha de vencimiento'  => Carbon::parse($guarantee->expiration_date)->format('d-m-Y'),
                        'Condición'             => $guarantee->closed==1 ? 'No renovable: archivado' : 'Renovable',
                        'Morph id'              => $guarantee->guaranteeable_id,
                        'Morph type'            => $guarantee->guaranteeable_type,
                    ]);
            }

            $this->record_export('/guarantee','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'invoices') {
            $excel_name = 'Base de Pagos a proveedores';
            $sheet_name = 'Facturas de proveedores';

            //if(Session::has('db_query'))
            //  $invoices = Session::get('db_query');
            //else
            $invoices = Invoice::all();

            foreach($invoices as $invoice)
            {
                $concept_flags = substr($invoice->flags, -3);

                if($concept_flags=='100')
                    $concept = 'Adelanto';
                elseif($concept_flags=='010')
                    $concept = 'Pago contra avance';
                elseif($concept_flags=='001')
                    $concept = 'Pago contra entrega';
                else
                    $concept = '';

                if($invoice->flags[0]==1)
                    $status = 'Pagado';
                elseif($invoice->flags[2]==0)
                    $status = 'Autorización de G. Tecnica pendiente';
                elseif($invoice->flags[1]==0)
                    $status = 'Autorización de G. General pendiente';
                else
                    $status = 'Autorizado, pago pendiente';

                if($invoice->transaction_code)
                    $payment_date = Carbon::parse($invoice->transaction_date)->format('d/m/Y');
                else
                    $payment_date = '';

                $sheet_content->prepend(
                    [   'Fecha de registro' => date_format($invoice->created_at,'d/m/Y'),
                        'Código de OC'      => 'OC-'.str_pad($invoice->oc_id, 5, "0", STR_PAD_LEFT),
                        'Centro de costos'  => $invoice->oc->assignment && $invoice->oc->assignment->cost_center && $invoice->oc->assignment->cost_center > 0 ? $invoice->oc->assignment->cost_center : '',
                        'Proveedor'         => $invoice->oc->provider,
                        'Nº de factura'     => $invoice->number,
                        'Fecha de emisión'  => Carbon::parse($invoice->date_issued)->format('d/m/Y'),
                        'Monto facturado'   => $invoice->amount + 0, // number_format($invoice->amount,2),
                        'Concepto'          => $concept,
                        'Estado'            => $status,
                        'Fecha de pago'     => $payment_date,
                        'Código de transacción' => $invoice->transaction_code,
                        'Información adicional' => $invoice->detail,
                    ]);
            }

            $this->record_export('/invoice','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'item_categories') {
            $excel_name = 'Tabla de categorias de item';
            $sheet_name = 'Categorias';

            $categories = ItemCategory::all();

            $this->record_export('/item_category', 'Full table item_categories', 0);

            return $this->create_excel($excel_name, $sheet_name, $categories);
        }

        if ($table == 'items') {
            $excel_name = 'Base de items';
            $sheet_name = 'Items';

            $items = Item::all();

            foreach($items as $item)
            {
                $sheet_content->prepend(
                    [   'Número'                => $item->number,
                        'Código'                => $item->client_code,
                        'Descripción'           => $item->description,
                        'Unidades'              => $item->units,
                        'Costo por unidad'      => number_format($item->cost_unit_central,2).' Bs',
                        'Detalle'               => $item->detail,
                        'Categoría'             => $item->category,
                        'Área'                  => $item->area,
                        'Fecha de creación'     => Carbon::parse($item->created_at)->format('d-m-Y'),
                        'Última modificación'   => $item->updated_at>'0000-00-00 00:00:00' ?
                            Carbon::parse($item->updated_at)->format('d-m-Y') : '',
                    ]);
            }

            $this->record_export('/task/id','Full table of items',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'licenses') {
            $excel_name = 'Base de licencias de conducir';
            $sheet_name = 'Licencias de conducir';

            $licenses = License::all();

            foreach($licenses as $license)
            {
                $sheet_content->push(
                    [   '#'                     => $license->id,
                        'Usuario'               => $license->user->name,
                        'Número de licencia'    => $license->number,
                        'Categoría'             => $license->category,
                        'Fecha de vencimiento'  => Carbon::parse($license->exp_date)->format('d-m-Y'),
                    ]);
            }

            $this->record_export('/driver','driver licenses',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'maintenances') {
            $excel_name = 'Base de mantenimientos';
            $sheet_name = 'Mantenimiento de activos';

            $maintenances = Maintenance::all();

            foreach($maintenances as $maintenance)
            {
                $sheet_content->prepend(
                    [   'Puesto en mantenimiento' => Carbon::parse($maintenance->created_at)->format('d/m/Y'),
                        'Activo'                => $maintenance->vehicle_id ?
                            $maintenance->vehicle->type.' '.$maintenance->vehicle->license_plate :
                            ($maintenance->device_id ? $maintenance->device->type.' '.$maintenance->device->serial : ''),
                        'Tipo de mantenimiento' => $maintenance->type.($maintenance->parameter_id!=0 ?
                                ' ('.$maintenance->parameter->name.')' : ''),
                        'Detalle de trabajos'   => wordwrap($maintenance->detail, 70, "\n", false),
                        'Costo'                 => number_format($maintenance->cost,2).' Bs',
                        'Responsable'           => $maintenance->user->name,
                        'Estado'                => $maintenance->completed==0 ? 'Trabajos pendientes' : 'Mantenimiento Finalizado',
                        'Fecha de fin de mant.' => $maintenance->completed==1 ?
                            Carbon::parse($maintenance->date)->format('d/m/Y') : '',
                    ]);
            }

            $this->record_export('/maintenance','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'oc_certifications') {
            $excel_name = 'Base de certificaciones de aceptación de OCs';
            $sheet_name = 'Certificaciones';

            $oc_certifications = OcCertification::all();

            foreach($oc_certifications as $oc_certification)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $oc_certification->id,
                        'Código'                => $oc_certification->code,
                        'Código OC'             => $oc_certification->oc->code,
                        'Centro de costos'      => $oc_certification->oc->assignment && $oc_certification->oc->assignment->cost_center && $oc_certification->oc->assignment->cost_center > 0 ? $oc_certification->oc->assignment->cost_center : '',
                        'Monto ejecutado [Bs]'  => $oc_certification->amount + 0, // number_format($oc_certification->amount,2).' Bs',
                        'Certificado por'       => $oc_certification->user->name,
                        'ID usuario'            => $oc_certification->user_id,
                        'Tipo de aceptación'    => $oc_certification->type_reception,
                        '# aceptación parcial'  => $oc_certification->num_reception!=0 ?
                            $oc_certification->num_reception : '',
                        'Fecha comunicación entrega'    => Carbon::parse($oc_certification->date_ack)->format('d/m/Y'),
                        'Fecha aceptación'      => Carbon::parse($oc_certification->date_acceptance)->format('d/m/Y'),
                        'Fecha entrega Administración' => $oc_certification->date_print_ack=='0000-00-00 00:00:00' ? '' :
                            Carbon::parse($oc_certification->date_print_ack)->format('d/m/Y'),
                        'Fecha certificación'   => date_format($oc_certification->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($oc_certification->updated_at,'d/m/Y'),
                        'Observaciones'         => wordwrap($oc_certification->observations, 70, "\n", false),
                    ]);
            }

            $this->record_export('/oc_certificate','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'ocs') {
            $excel_name = 'Base de OCs';
            $sheet_name = 'Ordenes de Compra';

            if(Session::has('db_query'))
                $ocs = Session::get('db_query');
            else
                $ocs = OC::all();

            foreach($ocs as $oc)
            {
                //$user_name = User::where('id',$oc->user_id)->first()->name;
                //$pm_name = empty($oc->pm_id) ? '' : User::where('id',$oc->pm_id)->first()->name;

                $sheet_content->prepend(
                    [   '#'                             => $oc->id,
                        'Código'                        => $oc->code,
                        'Usuario'                       => $oc->user ? $oc->user->name : '',
                        'Fecha'                         => date_format($oc->created_at,'d/m/Y'),
                        'Mes'                           => date_format($oc->created_at,'m'),
                        'Proveedor'                     => $oc->provider,
                        'Concepto'                      => wordwrap($oc->proy_concept, 70, "\n", false),
                        'Monto OC'                      => $oc->oc_amount + 0, // number_format($oc->oc_amount,2),
                        'Monto ejecutado'               => $oc->executed_amount + 0, // number_format($oc->executed_amount,2),
                        'Monto cancelado'               => $oc->payed_amount + 0, // number_format($oc->payed_amount,2),
                        'Saldo'                         => $oc->executed_amount - $oc->payed_amount + 0, // number_format($oc->executed_amount-$oc->payed_amount,2),
                        'Porcentajes de pago'           => str_replace('-','% - ',$oc->percentages).'%',
                        'Proyecto'                      => wordwrap($oc->proy_name, 70, "\n", false),
                        'Centro de costos'              => $oc->assignment && $oc->assignment->cost_center && $oc->assignment->cost_center > 0 ? $oc->assignment->cost_center : '',
                        'Descripción proyecto'          => wordwrap($oc->proy_description, 70, "\n", false),
                        'Cliente'                       => $oc->client,
                        'OC Cliente'                    => $oc->client_oc,
                        'Documento Asignación Cliente'  => $oc->client_ad,
                        'Lugar de entrega'              => $oc->delivery_place,
                        'Plazo de entrega'              => $oc->delivery_term,
                        'Responsable'                   => $oc->responsible ? $oc->responsible->name : '',
                        'Estado'                        => $oc->status,
                        'Observaciones'                 => wordwrap($oc->observations, 70, "\n", false),
                        'Complemento de OC'             => $oc->linked ? $oc->linked->code : '',
                    ]);
            }

            $this->record_export('/oc','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'operators') {
            $excel_name = 'Base de asignación de equipos';
            $sheet_name = 'Asignaciones';

            $operators = Operator::all();

            foreach($operators as $operator)
            {
                $sheet_content->prepend(
                    [   'Fecha'                 => Carbon::parse($operator->date)->format('d/m/Y'),
                        'Equipo'                => $operator->device->type.' '.$operator->device->model,
                        'Número de serie'       => $operator->device->serial,
                        'Entregado por'         => $operator->confirmation_flags[2]==0 ?
                            $operator->deliverer->name.' (por confirmar)' : $operator->deliverer->name,
                        'Entregado a'           => $operator->confirmation_flags[3]==0 ?
                            $operator->receiver->name.' (por confirmar)' : $operator->receiver->name,
                        'Área de trabajo'       => $operator->project_type,
                        'Destino'               => $operator->destination,
                        'Motivo'                => $operator->reason,
                        'Observaciones'         => $operator->observations,
                    ]);
            }

            $this->record_export('/operator','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'order_site') {
            $excel_name = 'Base de asociaciones Orden-Sitio';
            $sheet_name = 'Asociaciones Orden-Sitio';

            $sites = Site::all();

            foreach($sites as $site)
            {
                foreach($site->orders as $order){

                    $sheet_content->prepend(
                        [   'Código de sitio'       => $site->code,
                            'Sitio'                 => wordwrap($site->name, 70, "\n", false),
                            'Orden'                 => $order->type.' '.$order->code,
                            'Monto asignado a sitio' => $order->pivot->assigned_amount.' Bs',
                            'Estado'                => $order->pivot->status==0 ? 'Pendiente' : 'Cobrado',
                            'Última modificación'   => date_format($order->pivot->updated_at,'d/m/Y'),
                        ]);
                }
            }

            $this->record_export('/site/id','Full pivot table order_site',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'orders') {
            $excel_name = 'Base de órdenes de compra de clientes';
            $sheet_name = 'Ordenes de Compra';

            $orders = Order::all();

            foreach($orders as $order)
            {
                $sheet_content->prepend(
                    [   'Número de Orden'       => $order->type.' - '.$order->code,
                        'Cliente'               => $order->client,
                        'Número de sitios'      => $order->number_of_sites,
                        'Fecha de emisión'      => Carbon::parse($order->date_issued)->format('d/m/Y'),
                        'Monto asignado'        => number_format($order->assigned_price,2).' Bs',
                        'Porcentajes de pago'   => str_replace('-','% - ',$order->payment_percentage).'%',
                        'Monto cobrado'         => number_format($order->charged_price,2).' Bs',
                        'Porcentaje cobrado'    => number_format(($order->charged_price/$order->assigned_price)*100,2).'%',
                        'Estado'                => $order->status,
                        'Fecha de cobro'        => $order->status=='Cobrado' ?
                            Carbon::parse($order->date_charged)->format('d/m/Y') : '',
                        'Información adicional' => wordwrap($order->detail, 70, "\n", false),
                    ]);
            }

            $this->record_export('/order','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'projects') {
            $excel_name = 'Base de proyectos (glogal)';
            $sheet_name = 'Proyectos';

            $projects = Project::all();

            foreach($projects as $project)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $project->id,
                        'Código'                => $project->code,
                        'Responsable'           => $project->user ? $project->user->name : '',
                        'Nombre'                => $project->name,
                        'Descripción'           => wordwrap($project->description, 70, "\n", false),
                        'Cliente'               => $project->client,
                        'Tipo de trabajo'       => $project->type,
                        'Tipo de adjudicación'  => $project->award,
                        'Detalle de presentación' => $project->application_details,
                        'Deadline de presentación' => $project->award=='Licitación' ?
                            Carbon::parse($project->application_deadline)->format('d/m/Y') : '',
                        'Estado de Presentación' => $project->award=='Licitación' ?
                            ($project->applied==1 ? 'Presentado' :
                                ($project->status=='No asignado' ? 'Expirado' : 'Pendiente')) : 'n/a',
                        'Vigencia desde'        => date_format(Carbon::parse($project->valid_from),'d/m/Y'),
                        'Vigencia hasta'        => date_format(Carbon::parse($project->valid_to),'d/m/Y'),
                        'Persona de contacto'   => $project->contact ? $project->contact->name : '',
                        'Creación de registro' => date_format($project->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($project->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/project','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'providers') {
            $excel_name = 'Base de proveedores';
            $sheet_name = 'Proveedores';

            $providers = Provider::all();

            foreach($providers as $provider)
            {
                $identification_number = '';
                if ($provider->contact_id != 0)
                    $identification_number = $provider->contact_id.' '.$provider->contact_id_place;

                $sheet_content->prepend(
                    [   'Proveedor'             => wordwrap($provider->prov_name, 70, "\n", false),
                        'NIT'                   => $provider->nit,
                        'Área de especialidad'  => $provider->specialty,
                        'Teléfono principal'    => $provider->phone_number,
                        'Teléfono secundario'   => $provider->alt_phone_number,
                        'Dirección'             => wordwrap($provider->address, 70, "\n", false),
                        'Número de cuenta'      => $provider->bnk_account,
                        'Banco'                 => $provider->bnk_name,
                        'Persona de contacto'   => $provider->contact_name,
                        'Documento de identificación' => $identification_number,
                        'Teléfono de contacto'        => $provider->contact_phone,
                        'Email'                 => $provider->email,
                    ]);
            }

            $this->record_export('/provider', 'Full table', 0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'service_parameters') {
            $excel_name = 'Base de parámetros de sistema';
            $sheet_name = 'Parámetros de sistema';

            $service_parameters = ServiceParameter::all();

            foreach($service_parameters as $service_parameter)
            {
                $sheet_content->push(
                    [   'ID'                    => $service_parameter->id,
                        'Nombre'                => $service_parameter->name,
                        'Grupo'                 => $service_parameter->group,
                        'Descripción'           => $service_parameter->description,
                        'Valor literal'         => wordwrap($service_parameter->literal_content, 70, "\n", false),
                        'Valor numérico'        => $service_parameter->literal_content ? '' : $service_parameter->numeric_content,
                        'Units'                 => $service_parameter->units,
                        'Fecha de creación'     => Carbon::parse($service_parameter->created_at)->format('d/m/Y'),
                        'Ültima modificiación'  => Carbon::parse($service_parameter->updated_at)->format('d/m/Y'),
                    ]);
            }

            $this->record_export('/service_parameter','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'sites') {
            $excel_name = 'Base de sitios';
            $sheet_name = 'Sitios';

            $sites = Site::all();

            foreach($sites as $site)
            {
                $site->start_date = Carbon::parse($site->start_date);
                $site->end_date = Carbon::parse($site->end_date);
                $site->start_line = Carbon::parse($site->start_line);
                $site->deadline = Carbon::parse($site->deadline);

                $sheet_content->prepend(
                    [   'Código'                => $site->code,
                        'Sitio'                 => wordwrap($site->name, 70, "\n", false),
                        'Asignación'            => wordwrap($site->assignment->name, 70, "\n", false),
                        'Cliente'               => $site->assignment->client,
                        'Estado'                => $site->statuses($site->status),
                        'Latitud'               => $site->latitude!=0 ? $site->latitude : '',
                        'Longitud'              => $site->longitude!=0 ? $site->longitude : '',
                        'Departamento'          => $site->department,
                        'Localidad'             => $site->municipality,
                        'Tipo de localidad'     => $site->type_municipality,
                        'Responsable ABROS'     => $site->responsible ? $site->responsible->name : 'Sin asignar',
                        'Contacto del cliente'  => $site->contact->name,
                        'Fecha de inicio asignada' => $site->start_line->year<1 ? '' : $site->start_line->format('d-m-Y'),
                        'Fecha de fin asignada' => $site->deadline->year<1 ? '' : $site->deadline->format('d-m-Y'),
                        //date_format($site->deadline,'d-m-Y'),
                        'Fecha de inicio propia'=> $site->start_date->year<1 ? '' : $site->start_date->format('d-m-Y'),
                        //date_format($site->start_date,'d-m-Y'),
                        'Fecha de fin propia'   => $site->end_date->year<1 ? '' : $site->end_date->format('d-m-Y'),
                        //date_format($site->end_date,'d-m-Y'),
                        '% Avance'              => number_format($site->percentage_completed,2).'%',
                        'Observaciones'         => $site->observations,
                    ]);
            }

            $this->record_export('/site','Full table of sites without filter per assignment',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'stipend_requests') {
            $excel_name = 'Tabla de solicitudes de viaticos';
            $sheet_name = 'Solicitudes de viaticos';

            $stipend_requests = StipendRequest::all();
            
            foreach($stipend_requests as $stipend_request) {
                $stipend_request->cost_center = $stipend_request->assignment && $stipend_request->assignment->cost_center > 0 ? $stipend_request->assignment->cost_center : '';
                
                unset($stipend_request->assignment);
            }

            $this->record_export('/stipend_request', 'Full table stipend_requests', 0);

            return $this->create_excel($excel_name, $sheet_name, $stipend_requests);
        }

        if ($table == 'tenders') {
            $excel_name = 'Tabla de licitaciones';
            $sheet_name = 'Licitaciones';

            $tenders = Tender::all();

            $this->record_export('/tender', 'Full table tenders', 0);

            return $this->create_excel($excel_name, $sheet_name, $tenders);
        }

        if ($table == 'users') {
            $excel_name = 'Base de usuarios';
            $sheet_name = 'Usuarios';

            $users = User::all();

            foreach($users as $user)
            {
                $sheet_content->prepend(
                    [   'Nombre'        => $user->name,
                        'Login'         => $user->login,
                        'Password'      => $user->password,
                        'Creado el'     => date_format($user->created_at,'d-m-Y'),
                        'Area'          => $user->area,
                        'Rango'         => $user->rank,
                        'Nivel'         => $user->priv_level,
                    ]);
            }

            $this->record_export('/user','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'vehicle_histories') {
            $excel_name = 'Base - historial de vehículos';
            $sheet_name = 'Historial de vehículos';

            $vehicle_histories = VehicleHistory::all();

            foreach($vehicle_histories as $history)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $history->id,
                        'ID vehículo'           => $history->vehicle_id,
                        'Vehículo'              => $history->vehicle->type.' '.$history->vehicle->model.
                            ' - Placa: '.$history->vehicle->license_plate,
                        'Tipo de registro'       => $history->type,
                        'Contenido'             => wordwrap($history->contents, 70, "\n", false),
                        'Estado'                => $history->status,
                        'Morph ID'              => $history->historyable_id,
                        'Morph type'            => $history->historyable_type,
                        'Fecha creación registro' => date_format($history->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($history->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/history/vehicle/id','Full table for vehicle histories',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'vehicle_requirements') {
            $excel_name = 'Tabla de requerimientos de vehículos';
            $sheet_name = 'Requerimientos';

            $requirements = VehicleRequirement::all();

            $this->record_export('/vehicle_requirement','Full table vehicle_requirements', 0);

            return $this->create_excel($excel_name, $sheet_name, $requirements);
        }

        if ($table == 'vehicles') {
            $excel_name = 'Base de Vehículos';
            $sheet_name = 'Lista de vehículos';

            if(Session::has('db_query'))
                $vehicles = Session::get('db_query');
            else
                $vehicles = Vehicle::where('status', '<>', 'Baja')->get(); //Vehicle::all();

            $i=1;

            foreach($vehicles as $vehicle)
            {
                $documents = '';
                foreach($vehicle->files as $file){
                    if($file->type=='pdf'){
                        if(strlen($documents)==0)
                            $documents = $file->description;
                        else
                            $documents = $documents.', '.$file->description;
                    }
                }

                $sheet_content->push(
                    [   'Número'            => $i,
                        'Número de placa'   => $vehicle->license_plate,
                        'Tipo'              => $vehicle->type,
                        'Modelo'            => $vehicle->model,
                        'Propietario'       => $vehicle->owner,
                        'Sucursal'          => $vehicle->branch,
                        'kilometraje'       => $vehicle->mileage + 0, // number_format($vehicle->mileage,2),
                        'Capacidad de combustible' => $vehicle->gas_capacity + 0, // number_format($vehicle->gas_capacity,2),
                        'Estado'            => $vehicle->status,
                        'Responsable actual' => ($vehicle->responsible!=0 ?
                            ($vehicle->last_driver&&$vehicle->last_driver->confirmation_flags[3]==1 ?
                                $vehicle->user->name : $vehicle->user->name.' (Por confirmar)') : 'Sin asignar'),
                        'Destinado a'       => $vehicle->destination,
                        'Condiciones'       => $vehicle->condition,
                        'Documentos'        => $documents,
                    ]);

                $i++;
            }

            $this->record_export('/vehicle','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'vhc_failure_reports') {
            $excel_name = 'Tabla de reportes de falla - vehiculos';
            $sheet_name = 'Reportes de falla';

            $reports = VhcFailureReport::all();

            $this->record_export('/vehicle_failure_report', 'Full table vhc_failure_reports', 0);

            return $this->create_excel($excel_name, $sheet_name, $reports);
        }

        //Obsolete function for projects export
        /*
        if ($table == 'projects') {
            $excel_name = 'Base de Proyectos';
            $sheet_name = 'Proyectos';

            $etapa = ['Proyecto nuevo',
                'Documento de asignación recibido',
                'Cotización enviada',
                'Pedido de compra recibido',
                'Pedido de compra firmado',
                'Planilla de cantidades enviada',
                'Planilla de cantidades firmada',
                'Planilla económica enviada',
                'Planilla económica firmada',
                'Certificado de Control de calidad recibido',
                'Facturado',
                'Concluido',
                'Proyecto no asignado'
            ];

            $projects = Project::all();

            foreach($projects as $project)
            {
                $user_name = User::where('id','=',$project->user_id)->first()->name;

                $asig_file_date = '';
                $quote_file_date = '';
                $pc_org_date = '';
                $pc_sgn_date = '';
                $matsh_org_date = '';
                $matsh_sgn_date = '';
                $costsh_org_date = '';
                $costsh_sgn_date = '';
                $qcc_file_date = '';
                $bill_date = '';

                if($project->asig_file_id!=0)
                    $asig_file_date = date_format(File::find($project->asig_file_id)->created_at,'d/m/Y');
                if($project->quote_file_id!=0)
                    $quote_file_date = date_format(File::find($project->quote_file_id)->created_at,'d/m/Y');
                if($project->pc_org_id!=0)
                    $pc_org_date = date_format(File::find($project->pc_org_id)->created_at,'d/m/Y');
                if($project->pc_sgn_id!=0)
                    $pc_sgn_date = date_format(File::find($project->pc_sgn_id)->created_at,'d/m/Y');
                if($project->matsh_org_id!=0)
                    $matsh_org_date = date_format(File::find($project->matsh_org_id)->created_at,'d/m/Y');
                if($project->matsh_sgn_id!=0)
                    $matsh_sgn_date = date_format(File::find($project->matsh_sgn_id)->created_at,'d/m/Y');
                if($project->costsh_org_id!=0)
                    $costsh_org_date = date_format(File::find($project->costsh_org_id)->created_at,'d/m/Y');
                if($project->costsh_sgn_id!=0)
                    $costsh_sgn_date = date_format(File::find($project->costsh_sgn_id)->created_at,'d/m/Y');
                if($project->qcc_file_id!=0)
                    $qcc_file_date = date_format(File::find($project->qcc_file_id)->created_at,'d/m/Y');

                if($project->bill_date!='0000-00-00 00:00:00')
                    $bill_date = date_format(Carbon::parse($project->bill_date),'d/m/Y');

                $sheet_content->prepend(
                    [   'Código'       => 'PR-'.str_pad($project->id, 4, "0", STR_PAD_LEFT).
                                                date_format($project->created_at,'-y'),
                        'Creado por'   => $user_name,
                        'Proyecto'     => $project->name,
                        'Cliente'      => $project->client,
                        'Etapa'        => $etapa[$project->status],
                        'Fecha de asignación'       => $asig_file_date,
                        'Código de asignación'      => $project->asig_num,
                        'Deadline de asignación'    => $project->asig_deadline,
                        'Fecha de cotización'       => $quote_file_date,
                        'Monto cotizado'            => $project->quote_amount,
                        'Fecha de recepción de pedido de compra'   => $pc_org_date,
                        'Fecha de firma de pedido de compra' => $pc_sgn_date,
                        'Deadline de pedido de compra'   => $project->pc_deadline,
                        'Monto asignado en PC'           => $project->pc__amount,
                        'Fecha de inicio de trabajos'    => $project->ini_date,
                        'Planilla de cantidades enviada' => $matsh_org_date,
                        'Planilla de cantidades firmada' => $matsh_sgn_date,
                        'Planilla económica enviada'     => $costsh_org_date,
                        'Planilla económica firmada'     => $costsh_sgn_date,
                        'Monto ejecutado'                => $project->costsh_amount,
                        'Certificado de Control de calidad recibido' => $qcc_file_date,
                        'Número de factura'           => $project->bill_number,
                        'Fecha de emisión'            => $bill_date,
                        'Observaciones'               => $project->ini_obs,
                    ]);
            }

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        */

        //Obsolete functions part of Warehouse module (no longer in use)
        /*
        if ($table == 'warehouses') {
            $excel_name = 'Base de almacenes';
            $sheet_name = 'Almacenes';

            $warehouses = Warehouse::all();

            foreach($warehouses as $warehouse)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $warehouse->id,
                        'Nombre'                => $warehouse->name,
                        'Dirección'             => $warehouse->location,
                        'Fecha de creación de registro' => date_format($warehouse->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($warehouse->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/warehouse','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'materials') {
            $excel_name = 'Base de materiales';
            $sheet_name = 'Materiales';

            $materials = Material::all();

            foreach($materials as $material)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $material->id,
                        'Código'                => $material->code,
                        'Nombre'                => $material->name,
                        'Tipo'                  => $material->type,
                        'Descripción'           => $material->description,
                        'Unidades'              => $material->units,
                        'Costo por unidad'      => $material->cost_unit!=0 ? number_format($material->cost_unit,2).' Bs' : '',
                        'Marca'                 => $material->brand,
                        'Proveedor'             => $material->supplier,
                        'Categoría'             => $material->category,
                        'ID de imagen principal'    => $material->main_pic_id!=0 ? $material->main_pic_id : '',
                        'Fecha de creación de registro' => date_format($material->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($material->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/material','Full table',0);
            
            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'wh_entries') {
            $excel_name = 'Base de ingresos de material';
            $sheet_name = 'Ingresos de material';

            $wh_entries = WarehouseEntry::all();

            foreach($wh_entries as $wh_entry)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $wh_entry->id,
                        'Usuario'               => $wh_entry->user ? $wh_entry->user->name : '',
                        'Almacén'               => $wh_entry->warehouse ? $wh_entry->warehouse->name : '',
                        'Material'              => $wh_entry->material ? $wh_entry->material->name : '',
                        'Fecha'                 => Carbon::parse($wh_entry->date)->format('d/m/Y'),
                        'Cantidad'              => number_format($wh_entry->qty,2),
                        'Persona que recibe'    => $wh_entry->received_by,
                        'ID receptor'           => $wh_entry->received_id!=0 ? $wh_entry->received_id : '',
                        'Persona que entrega'   => $wh_entry->delivered_by,
                        'ID entrega'            => $wh_entry->delivered_id!=0 ? $wh_entry->delivered_id : '',
                        'Motivo'                => $wh_entry->reason,
                        'Tipo de ingreso'      => $wh_entry->entry_type,
                        'Fecha de creación de registro' => date_format($wh_entry->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($wh_entry->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/wh_entry','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'wh_outlets') {
            $excel_name = 'Base de salidas de material';
            $sheet_name = 'Salidas de material';

            $wh_outlets = WarehouseOutlet::all();

            foreach($wh_outlets as $wh_outlet)
            {
                $sheet_content->prepend(
                    [   'ID'                    => $wh_outlet->id,
                        'Usuario'               => $wh_outlet->user ? $wh_outlet->user->name : '',
                        'Almacén'               => $wh_outlet->warehouse ? $wh_outlet->warehouse->name : '',
                        'Material'              => $wh_outlet->material ? $wh_outlet->material->name : '',
                        'Fecha'                 => Carbon::parse($wh_outlet->date)->format('d/m/Y'),
                        'Cantidad'              => number_format($wh_outlet->qty,2),
                        'Persona que recibe'    => $wh_outlet->received_by,
                        'ID receptor'           => $wh_outlet->received_id!=0 ? $wh_outlet->received_id : '',
                        'Persona que entrega'   => $wh_outlet->delivered_by,
                        'ID entrega'            => $wh_outlet->delivered_id!=0 ? $wh_outlet->delivered_id : '',
                        'Motivo'                => $wh_outlet->reason,
                        'Tipo de salida'        => $wh_outlet->outlet_type,
                        'Fecha de creación de registro' => date_format($wh_outlet->created_at,'d/m/Y'),
                        'Última modificación'   => date_format($wh_outlet->updated_at,'d/m/Y'),
                    ]);
            }

            $this->record_export('/wh_outlet','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }

        if ($table == 'material_warehouse') {
            $excel_name = 'Base de asociaciones Material-Almacén';
            $sheet_name = 'Asociaciones Material-Almacén';

            $warehouses = Warehouse::all();

            foreach($warehouses as $warehouse)
            {
                foreach($warehouse->materials as $material){

                    $sheet_content->prepend(
                        [   'ID almacén'            => $warehouse->id,
                            'Almacén'               => $warehouse->name,
                            'ID material'           => $material->id,
                            'Material'              => $material->name,
                            'Cantidad'              => $material->pivot->qty,
                            'Última modificación'   => date_format($material->pivot->updated_at,'d/m/Y'),
                        ]);
                }
            }

            $this->record_export('/warehouse','Full pivot table material_warehouse',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        */

        //Rbs_viatics replaced with stipend_requests
        /*
        if ($table == 'rbs_viatics') {
            $excel_name = 'Base de solicitudes de viáticos RBS';
            $sheet_name = 'Solicitudes';

            $rbs_viatics = RbsViatic::all();

            $this->record_export('/rbs_viatic','Full table',0);

            return $this->create_excel($excel_name, $sheet_name, $rbs_viatics);
        }

        if ($table == 'rbs_viatic_requests') {
            $excel_name = 'Base de viáticos asignados a técnicos RBS';
            $sheet_name = 'Viáticos';

            $rbs_viatic_requests = RbsViaticRequest::all();

            $this->record_export('/rbs_viatic','Full table rbs_viatic_requests',0);

            return $this->create_excel($excel_name, $sheet_name, $rbs_viatic_requests);
        }

        if ($table == 'rbs_viatic_site') {
            $excel_name = 'Base de asociaciones Viático-Sitio';
            $sheet_name = 'Asociaciones Viático-Sitio';

            $rbs_viatics = RbsViatic::all();

            foreach($rbs_viatics as $rbs_viatic)
            {
                foreach($rbs_viatic->sites as $site){

                    $sheet_content->prepend(
                        [   'Solicitud'             => $rbs_viatic->id,
                            'Sitio'                 => $site->id,
                            'Costo'                 => $site->pivot->cost_applied.' Bs',
                            'Estado'                => $site->pivot->status,
                            'Creación'              => date_format($site->pivot->created_at, 'd-m-Y'),
                            'Última modificación'   => date_format($site->pivot->updated_at,'d-m-Y'),
                        ]);
                }
            }

            $this->record_export('/rbs_viatic','Full pivot table rbs_viatic_site',0);

            return $this->create_excel($excel_name, $sheet_name, $sheet_content);
        }
        */

        return redirect()->back(); // default redirection if no match is found
    }

    public function summary($table, $id)
    {
        $excel_name = 'empty';
        $sheet_name = 'empty';
        $sheet_content = collect();

        // Old code for projects table (obsolete)
        /*
        if ($table == 'project') {
            $project = Project::find($id);

            $excel_name = 'Reporte de Proyecto - '.$project->name;
            $sheet_name = 'Resumen';

            Excel::create($excel_name, function($excel) use($project) {

                $excel->sheet('Resumen', function($sheet) use($project) {

                    $sheet_content = collect();

                    $etapa = ['Proyecto nuevo',
                        'Documento de asignación recibido',
                        'Cotización enviada',
                        'Pedido de compra recibido',
                        'Pedido de compra firmado',
                        'Planilla de cantidades enviada',
                        'Planilla de cantidades firmada',
                        'Planilla económica enviada',
                        'Planilla económica firmada',
                        'Certificado de Control de calidad recibido',
                        'Facturado',
                        'Concluido',
                        'Proyecto no asignado'
                    ];

                    $user_name = User::where('id', $project->user_id)->first()->name;

                    $asig_file_date = '';
                    $quote_file_date = '';
                    $pc_org_date = '';
                    $pc_sgn_date = '';
                    $matsh_org_date = '';
                    $matsh_sgn_date = '';
                    $costsh_org_date = '';
                    $costsh_sgn_date = '';
                    $qcc_file_date = '';
                    $bill_date = '';

                    if($project->asig_file_id!=0)
                        $asig_file_date = date_format(File::find($project->asig_file_id)->created_at,'d/m/Y');
                    if($project->quote_file_id!=0)
                        $quote_file_date = date_format(File::find($project->quote_file_id)->created_at,'d/m/Y');
                    if($project->pc_org_id!=0)
                        $pc_org_date = date_format(File::find($project->pc_org_id)->created_at,'d/m/Y');
                    if($project->pc_sgn_id!=0)
                        $pc_sgn_date = date_format(File::find($project->pc_sgn_id)->created_at,'d/m/Y');
                    if($project->matsh_org_id!=0)
                        $matsh_org_date = date_format(File::find($project->matsh_org_id)->created_at,'d/m/Y');
                    if($project->matsh_sgn_id!=0)
                        $matsh_sgn_date = date_format(File::find($project->matsh_sgn_id)->created_at,'d/m/Y');
                    if($project->costsh_org_id!=0)
                        $costsh_org_date = date_format(File::find($project->costsh_org_id)->created_at,'d/m/Y');
                    if($project->costsh_sgn_id!=0)
                        $costsh_sgn_date = date_format(File::find($project->costsh_sgn_id)->created_at,'d/m/Y');
                    if($project->qcc_file_id!=0)
                        $qcc_file_date = date_format(File::find($project->qcc_file_id)->created_at,'d/m/Y');

                    if($project->bill_date!='0000-00-00 00:00:00')
                        $bill_date = date_format(Carbon::parse($project->bill_date),'d/m/Y');

                    $sheet_content = array(
                        array('Código', 'PR-'.str_pad($project->id, 4, "0", STR_PAD_LEFT).
                            date_format($project->created_at,'-y')),
                        array('Proyecto', $project->name),
                        array('Cliente', $project->client),
                        array('Etapa', $etapa[$project->status]),
                        array('Fecha de asignación', $asig_file_date),
                        array('Código de documento de asignación', $project->asig_num),
                        array('Plazo para envio de cotización', $project->asig_deadline),
                        array('Fecha de cotización', $quote_file_date),
                        array('Monto cotizado', number_format($project->quote_amount,2).' Bs'),
                        array('Fecha de recepción de pedido de compra', $pc_org_date),
                        array('Fecha de firma de pedido de compra', $pc_sgn_date),
                        array('Deadline de pedido de compra', $project->pc_deadline),
                        array('Monto asignado en PC', number_format($project->pc__amount,2).' Bs'),
                        array('Fecha de inicio de trabajos', date_format(Carbon::parse($project->ini_date),'d/m/Y')),
                        array('Fecha de envío de Planilla de cantidades', $matsh_org_date),
                        array('Fecha de recepción de Planilla de cantidades firmada', $matsh_sgn_date),
                        array('Fecha de envío de Planilla económica', $costsh_org_date),
                        array('Fecha de recepción de Planilla económica firmada', $costsh_sgn_date),
                        array('Monto ejecutado', number_format($project->costsh_amount,2).' Bs'),
                        array('Fecha de recepción de Certificado de Control de calidad', $qcc_file_date),
                        array('Número de factura', $project->bill_number),
                        array('Fecha de emisión de factura', $bill_date),
                        array('Observaciones', $project->ini_obs),
                    );

                    $sheet->fromArray($sheet_content);
                });
            })->export('xls');
        }
        */
        if ($table == 'oc') {
            $oc = OC::find($id);

            $provider = $oc->provider_record; // Provider::find($oc->provider_id);
                                              // Provider::where('prov_name', $oc->provider)->first();

            if(empty($oc->pm_id)){
                $pm_name = '';
                $pm_phone = '';
                $pm_email = '';
            }
            else{
                $pm = User::find($oc->pm_id);
                $pm_name = $pm->name;
                $pm_phone = $pm->phone!=0 ? $pm->phone : '';
                $pm_email = $pm->email;
            }

            $payment_terms = '';

            if($oc->percentages!=''){
                $percentages_exploded = explode('-',$oc->percentages);

                if($percentages_exploded[0]!=0){
                    $payment_terms .= $percentages_exploded[0].'% a la firma de la presente orden contra presentación '.
                        'de la factura, ';
                }
                if($percentages_exploded[1]!=0){
                    $payment_terms .= $percentages_exploded[1].'% contra entrega de avance certificado y factura, ';
                }
                if($percentages_exploded[2]!=0){
                    $payment_terms .= $percentages_exploded[2].'% a 30 días de recibido el certificado de aceptación '.
                        'definitiva y la factura correspondiente.';
                }
            }

            if($oc->flags[1]==1&&$oc->flags[2]==1){
                $data = $oc->code.' aprobada por Gerencia General el '.Carbon::parse($oc->auth_ceo_date)->format('d-m-Y').
                    ' autorización '.$oc->auth_ceo_code;
            }
            elseif($oc->flags[1]==0&&$oc->flags[2]==1){
                $data = $oc->code.' aprobada por Gerencia Tecnica el '.Carbon::parse($oc->auth_tec_date)->format('d-m-Y').
                    ' autorización '.$oc->auth_tec_code;
            }
            else
                $data = $oc->code.' pendiente de aprobación';
            
            $view = View::make('app.qr_code_export', ['data' => $data, 'size' => 90, 'margin' => 0]);
            $qr_code = $view->render(); // (string) $view;

            $this->record_export('/oc/'.$oc->id,'OC to sign',$oc);

            Excel::load('/public/file_layouts/OC_model.xlsx', function($reader) 
                    use($oc, $provider, $pm_name, $pm_phone, $pm_email, $qr_code, $payment_terms)
            {
                foreach($reader->get() as $key => $sheet) {
                    $sheetTitle = $sheet->getTitle();
                    $sheetToChange = $reader->setActiveSheetIndex($key);

                    if($sheetTitle === 'OC') {

                        $delivery_term = $sheetToChange->getCell('H66');
                        $oc->delivery_term = $oc->delivery_term.' '.$delivery_term;

                        //$hoja = $reader->setActiveSheetIndex(0);
                        //$reader->getActiveSheet()->setCellValue('AM2', $oc->id);
                        $sheetToChange->setCellValue('AM2', $oc->id)
                            ->setCellValue('AM3', date_format($oc->created_at, 'd-m-Y'))
                            ->setCellValue('AM6', $oc->client_oc && $oc->client_oc > 0 ? $oc->client_oc : '')
                            ->setCellValue('AM7', $oc->client_ad)
                            ->setCellValue('AM8', $oc->assignment && $oc->assignment->cost_center && $oc->assignment->cost_center > 0 ? $oc->assignment->cost_center : '')
                            ->setCellValue('F10', $oc->provider)
                            ->setCellValue('F11', $provider->address)
                            ->setCellValue('F12', $provider->phone_number)
                            ->setCellValue('Y12', $provider->alt_phone_number!=0 ? $provider->alt_phone_number : '')
                            ->setCellValue('J13', $provider->contact_name)
                            ->setCellValue('F14', $oc->proy_name.' - '.$oc->client)
                            ->setCellValue('Y10', $provider->nit)
                            ->setCellValue('C20', $oc->proy_description)
                            ->setCellValue('AN20', $oc->oc_amount)
                            ->setCellValue('A70', $provider->bnk_name.' '.$provider->bnk_account.' / '.
                                $provider->contact_name)
                            ->setCellValue('G80', $pm_name)
                            ->setCellValue('U80', $pm_phone)
                            ->setCellValue('AJ80', $pm_email)
                            ->setCellValue('O12', $provider->fax!=0 ? $provider->fax : '')
                            ->setCellValue('H65', $oc->delivery_place)
                            ->setCellValue('H66', $oc->delivery_term)
                            ->setCellValue('T13', $provider->email)
                            ->setCellValue('AM5', $oc->client)
                            ->setCellValue('C63', ucfirst($this->convert_number_to_words($oc->oc_amount)).' Bolivianos')
                            ->setCellValue('I68', $payment_terms);

                        if($oc->linked){
                            $sheetToChange->setCellValue('C21', 'OC complementaria a '.$oc->linked->code);
                        }

                        // Insert qr code to excel
                        $reader->sheet('OC', function($sheet){
                            $objDrawing = new PHPExcel_Worksheet_Drawing;
                            $objDrawing->setPath(public_path('files/qr/qrcode.png')); //path of qr code generated
                            $objDrawing->setCoordinates('AC85');
                            $objDrawing->setWorksheet($sheet);
                        });

                        //->appendRow('test','change','view here');
                    }
                    /*
                    if($sheetTitle === 'Obligaciones') {
                        $i = 1;
                        while($i < 10){
                            $sheetToChange->setCellValue('A'.$i,'test1')
                                ->setCellValue('B'.$i,'test2')
                                ->setCellValue('C'.$i,'test3')
                                ->setCellValue('D'.$i,'test4')
                                ->setCellValue('E'.$i,'test5')
                                ->setCellValue('F'.$i,'test6')
                                ->setCellValue('G'.$i,'test7');
                            $i++;
                        }
                    }
                    */
                }
            })->export('xlsx');
        }
        elseif ($table == 'device_characteristics') {
            $excel_name = 'Tabla de características de equipo';
            $sheet_name = 'Características';
            $documents = collect();

            $device = Device::find($id);

            foreach($device->characteristics as $characteristic)
            {
                $sheet_content->push(
                    [   'ID'                    => $characteristic->id,
                        'Tipo'                  => $characteristic->type,
                        'Valor'                 => $characteristic->value,
                        'Unidades'              => $characteristic->units,
                        'Fecha de registo'      => Carbon::parse($characteristic->created_at)->format('d/m/Y'),
                        'Última modificación'   => Carbon::parse($characteristic->updated_at)->format('d/m/Y'),
                    ]);
            }

            foreach($device->files as $file)
            {
                $documents->push(
                    [   'ID'                    => $file->id,
                        'Nombre de archivo'     => $file->name,
                        'Descripción'           => $file->description,
                        'Tipo de archivo'       => $file->type,
                        'Fecha de registo'      => Carbon::parse($file->created_at)->format('d/m/Y'),
                        'Última modificación'   => Carbon::parse($file->updated_at)->format('d/m/Y'),
                    ]);
            }

            $this->record_export('/characteristics/device/'.$device->id,'Characteristics related to this device',$device);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $device, $documents) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $device, $documents) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:F5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A5:F5')
                        ->mergeCells('C2:D2')
                        ->mergeCells('C3:D3')
                        ->mergeCells('C4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Reporte de equipo')
                            ->setCellValue('B2', 'Tipo')
                            ->setCellValue('B3', 'Modelo')
                            ->setCellValue('B4', 'Serial')
                            ->setCellValue('C2', $device->type)
                            ->setCellValue('C3', $device->model)
                            ->setCellValue('C4', $device->serial)
                            ->setCellValue('A5', 'Características')
                            ->setCellValue('E2', 'Fecha de reporte')
                            ->setCellValue('F2', date('d-m-Y'));

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                    $sheet->cells('A'.($sheet_content->count()+7).':F'.($sheet_content->count()+7), function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A'.($sheet_content->count()+7).':F'.($sheet_content->count()+7));

                    $sheet->setCellValue('A'.($sheet_content->count()+7),'Documentos');

                    $i = $sheet->getHighestRow();

                    $sheet->setCellValue('A'.($i+1), 'Id')
                        ->setCellValue('B'.($i+1), 'Nombre de archivo')
                        ->setCellValue('C'.($i+1), 'Descripción')
                        ->setCellValue('D'.($i+1), 'Tipo')
                        ->setCellValue('E'.($i+1), 'Fecha de registo')
                        ->setCellValue('F'.($i+1), 'Última modificación');

                    $i = $i+2;

                    foreach($device->files as $file)
                    {
                        $sheet->appendRow(($i), array(
                                $file->id,
                                $file->name,
                                $file->description,
                                $file->type,
                                Carbon::parse($file->created_at)->format('d/m/Y'),
                                Carbon::parse($file->updated_at)->format('d/m/Y'),
                        ));
                        $i++;
                    }
                });

            })->export('xls');
        }
        elseif ($table == 'vehicle_conditions') {
            $excel_name = 'Libro de control de vehículo';
            $sheet_name = 'Registros';

            $vehicle = Vehicle::find($id);

            foreach($vehicle->condition_records as $record)
            {
                $sheet_content->push(
                    [   'Fecha'                 => Carbon::parse($record->created_at)->format('d/m/Y'),
                        'Km inicio'             => $record->mileage_start.' Km',
                        'Km fin'                => $record->mileage_end.' Km',
                        'Nivel de combustible'  => $record->gas_level.' lts (aprox)'.($record->gas_filled!=0 ?
                                                    ' / '.$record->gas_filled.' lts cargados' : ''),
                        'Registrado por'        => $record->user->name,
                        'Observaciones'         => $record->observations,
                    ]);
            }

            $this->record_export('/vehicle_condition/'.$vehicle->id,'Records of this vehicle conditions',$vehicle);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $vehicle) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $vehicle) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:F5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A5:F5')
                        ->mergeCells('C2:D2')
                        ->mergeCells('C3:D3')
                        ->mergeCells('C4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Libro de control de vehículo')
                        ->setCellValue('B2', 'Tipo')
                        ->setCellValue('B3', 'Modelo')
                        ->setCellValue('B4', 'Placa')
                        ->setCellValue('C2', $vehicle->type)
                        ->setCellValue('C3', $vehicle->model)
                        ->setCellValue('C4', $vehicle->license_plate)
                        ->setCellValue('A5', 'Registros')
                        ->setCellValue('E2', 'Fecha de reporte')
                        ->setCellValue('F2', date('d-m-Y'));

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                });

            })->export('xls');
        }
        elseif ($table == 'sites') {
            $excel_name = 'Sitios por proyecto';
            $sheet_name = 'Sitios';

            $assignment = Assignment::find($id);

            foreach($assignment->sites as $site)
            {
                $site->start_date = $site->start_date=='0000-00-00 00:00:00' ? '' :
                    Carbon::parse($site->start_date)->format('d-m-Y');
                $site->end_date = $site->end_date=='0000-00-00 00:00:00' ? '' : Carbon::parse($site->end_date)->format('d-m-Y');

                $sheet_content->push(
                    [   'Código'                => $site->code,
                        'Sitio'                 => wordwrap($site->name, 50, "\n", false),
                        'Estado'                => $site->statuses($site->status),
                        '% Avance'              => number_format($site->percentage_completed,2).' %',
                        'Fecha de inicio'       => $site->start_Date, //date_format($site->start_date,'d-m-Y'),
                        'Fecha de fin'          => $site->end_date, //date_format($site->end_date,'d-m-Y'),
                    ]);
            }

            $this->record_export('/site/'.$assignment->id,'Sites related to this assignment',$assignment);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $assignment) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $assignment) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:F5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A5:F5')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3')
                        ->mergeCells('B4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Reporte de sitios por proyecto')
                        ->setCellValue('A2', 'Proyecto')
                        ->setCellValue('A3', 'Tipo')
                        ->setCellValue('A4', 'Cliente')
                        ->setCellValue('B2', wordwrap($assignment->name, 50, "\n", false))
                        ->setCellValue('B3', $assignment->type)
                        ->setCellValue('B4', $assignment->client)
                        ->setCellValue('E2', '% completado')
                        ->setCellValue('F2', $assignment->percentage_completed.' %')
                        ->setCellValue('E3', 'Project Manager')
                        ->setCellValue('F3', $assignment->responsible ? $assignment->responsible->name : '')
                        ->setCellValue('E4', 'Fecha de reporte')
                        ->setCellValue('F4', date('d-m-Y'))
                        ->setCellValue('A5', 'Sitios');

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                });

            })->export('xls');
        }
        elseif ($table == 'per-assignment-progress') {
            Date::setLocale('es');

            $assignment = Assignment::find($id);

            if(!$assignment){
                Session::flash('message', 'Error, registro solicitado no encontrado!');
                return redirect()->back();
            }

            $excel_name = 'Resumen de avance de obras - '.$this->normalize_name($assignment->name);
            $sheet_name = $this->normalize_name($assignment->name);

            $assignment->start_date = Carbon::parse($assignment->start_date);
            $assignment->end_date = Carbon::parse($assignment->end_date);

            if($assignment->start_date->year<1||$assignment->end_date->year<1){
                Session::flash('message', 'La asignación debe tener registradas las fechas de inicio y fin de trabajos!');
                return redirect()->back();
            }
            
            /*
            $header = array();

            $header[] = 'Fecha';

            foreach($site->tasks as $task)
            {
                $header[] = $task->name;
            }

            $sheet_content = array_fill_keys($header, '');
            $sheet_content[] = $header;
            */

            $items = $assignment->tasks()->select('tasks.name')->groupBy('tasks.name')->get();

            $items = $items->sortBy('name');

            /*
            foreach($assignment->sites as $site){
                foreach($site->tasks as $task){
                    if($items->count()==0||!($items->contains('name', $task->name))){
                        $items->push($task->name);
                    }
                }
            }
            */

            //$assignment->start_date = $assignment->end_date->subDays(30);

            while($assignment->start_date<=$assignment->end_date){

                $line = array();

                $line['FECHA'] = Date::parse($assignment->start_date)->format('l, j \\d\\e F \\d\\e Y');

                foreach($items as $item){
                    $var = 0;

                    $tasks = $assignment->tasks()->select('tasks.*')->where('tasks.name', $item->name)->get();

                    foreach($tasks as $task){
                        if($task->activities->count()>0){
                            $activity = $task->activities()->whereDate('date', '=', $assignment->start_date)->first();

                            if($activity)
                                $var += $activity->progress;
                        }
                    }

                    $var = $var==0 ? '' : $var;

                    $line[$item->name] = $var;
                }

                /*
                foreach($items as $item){
                    $var = 0;

                    foreach($assignment->sites as $site){
                        /*
                        if($site->tasks()->where('name', $item)->exists()){
                            $task = $site->tasks()->where('name', $item)->first();

                            if($task->activities()->whereDate('date', '=', $assignment->start_date)->exists()){
                                $activity = $task->activities()->whereDate('date', '=', $assignment->start_date)->first();
                                $var += $activity->progress;
                            }
                        }
                        *

                        foreach($site->tasks as $task){
                            if($item==$task->name){
                                if($task->activities()->whereDate('date', '=', $assignment->start_date)->exists()){
                                    $activity = $task->activities()->whereDate('date', '=', $assignment->start_date)->first();
                                    $var += $activity->progress;
                                }
                            }
                        }
                    }

                    if($var==0){
                        $var = '';
                    }

                    $line[$item] = $var;
                }*/

                $sheet_content[] = $line;

                $assignment->start_date->addDay(1);
            }

            //$this->record_export('/site/'.$assignment->id,'Sites related to this assignment',$assignment);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $assignment) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $assignment) {

                    $sheet->setWidth('A', 35);
                    $sheet->setHeight(2, 45);

                    $sheet->setCellValue('B1', $assignment->name);

                    $sheet->fromArray($sheet_content, null, 'A2', true);

                    $sheet->row(1, function($row) {
                        $row->setFontWeight('bold');
                    });

                    $sheet->row(2, function($row) {
                        $row->setFontWeight('bold');
                    });

                    $lastRow = $sheet->getHighestRow();
                    $lastColumn = $sheet->getHighestColumn();

                    $column_widths = array();

                    for($i='B';$i<=$lastColumn;$i++){
                        $column_widths[$i] = 14;
                    }

                    $sheet->setWidth($column_widths);

                    $sheet->getStyle('A3:A'.$lastRow)
                        ->getAlignment()
                        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    $sheet->setBorder('A2:'.$lastColumn.$lastRow, 'thin');
                });

            })->export('xls');
        }
        elseif ($table == 'per-site-progress') {
            $site = Site::find($id);

            if(!$site){
                Session::flash('message', 'Error, registro solicitado no encontrado!');
                return redirect()->back();
            }

            $excel_name = 'Resumen de avance de obras - '.$this->normalize_name($site->name);
            $sheet_name = $this->normalize_name($site->name);

            $site->start_date = Carbon::parse($site->start_date);
            $site->end_date = Carbon::parse($site->end_date);

            if($site->start_date->year<1||$site->end_date->year<1){
                Session::flash('message', 'El sitio debe tener registradas las fechas de inicio y fin de trabajos!');
                return redirect()->back();
            }
            /*
            $header = array();

            $header[] = 'Fecha';

            foreach($site->tasks as $task)
            {
                $header[] = $task->name;
            }

            //$sheet_content = array_fill_keys($header, '');
            //$sheet_content[] = $header;
            */

            while($site->start_date<=$site->end_date){

                $line = array();

                Date::setLocale('es');

                $line['FECHA'] = Date::parse($site->start_date)->format('l, j \\d\\e F \\d\\e Y');

                foreach($site->tasks as $task){

                    if($task->activities()->whereDate('date', '=', $site->start_date)->exists()){
                        /*
                        foreach($task->activities as $activity){
                            if($activity->date==$site->start_date){
                                $line[$task->name] = $activity->progress;
                            }
                        }
                        */
                        $activity = $task->activities()->whereDate('date', '=', $site->start_date)->first();
                        $line[$task->name] = $activity->progress;
                    }
                    else{
                        $line[$task->name] = '';
                    }
                }

                $sheet_content[] = $line;

                $site->start_date->addDay(1);
            }

            //$this->record_export('/site/'.$assignment->id,'Sites related to this assignment',$assignment);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $site) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $site) {

                    $sheet->setWidth('A', 35);
                    $sheet->setHeight(2, 45);
                    //$sheet->setAutoSize(false);

                    $sheet->setCellValue('B1', $site->name);

                    $sheet->fromArray($sheet_content, null, 'A2', true);

                    $sheet->row(1, function($row) {
                        $row->setFontWeight('bold');
                    });

                    $sheet->row(2, function($row) {
                        $row->setFontWeight('bold');
                    });

                    $lastRow = $sheet->getHighestRow();
                    $lastColumn = $sheet->getHighestColumn();

                    $column_widths = array();

                    for($i='B';$i<=$lastColumn;$i++){
                        $column_widths[$i] = 14;
                    }

                    $sheet->setWidth($column_widths);

                    $sheet->getStyle('A3:A'.$lastRow)
                        ->getAlignment()
                        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    $sheet->setBorder('A2:'.$lastColumn.$lastRow, 'thin');
                });

            })->export('xls');
        }
        elseif ($table == 'tasks') {
            $excel_name = 'Items por sitio';
            $sheet_name = 'Items';

            $site = Site::find($id);

            foreach($site->tasks as $task)
            {
                $task->start_date = $task->start_date=='0000-00-00 00:00:00' ? '' :
                    Carbon::parse($task->start_date)->format('d-m-Y');
                $task->end_date = $task->end_date=='0000-00-00 00:00:00' ? '' : Carbon::parse($task->end_date)->format('d-m-Y');

                $sheet_content->push(
                    [   //'Código'                => $task->code,
                        'Item'                  => wordwrap($task->name, 40, "\n", false),
                        //'Descripción'           => wordwrap($task->description, 50, "\n", false),
                        'Cantidad proyectada'   => $task->total_expected + 0, // number_format($task->total_expected,2),
                        'Unidades'              => $task->units,
                        'Cantidad avanzada'     => $task->progress + 0, // number_format($task->progress,2),
                        'Estado'                => $task->statuses($task->status),
                        '% Avance'              => number_format(($task->progress/$task->total_expected)*100,2).' %',
                        'Fecha de inicio'       => $task->start_date, //date_format($task->start_date,'d-m-Y')
                        'Fecha de fin'          => $task->end_date, //date_format($task->end_date,'d-m-Y'),
                    ]);
            }

            $this->record_export('/task/'.$site->id,'Task created for this site',$site);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $site) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $site) {

                    $sheet->cells('A1:H1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:H5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:H1')
                        ->mergeCells('A5:H5')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3')
                        ->mergeCells('B4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Reporte de items por sitio')
                        ->setCellValue('A2', 'Sitio')
                        ->setCellValue('B2', wordwrap($site->name, 50, "\n", false))
                        ->setCellValue('A3', 'Proyecto')
                        ->setCellValue('B3', wordwrap($site->assignment->name, 50, "\n", false))
                        ->setCellValue('A4', 'Cliente')
                        ->setCellValue('B4', $site->assignment->client)
                        ->setCellValue('E2', '% completado')
                        ->setCellValue('F2', $site->percentage_completed.' %')
                        ->setCellValue('E3', 'Project Manager')
                        ->setCellValue('F3', $site->responsible ? $site->responsible->name : '')
                        ->setCellValue('E4', 'Fecha de reporte')
                        ->setCellValue('F4', date('d-m-Y'))
                        ->setCellValue('A5', 'Items');

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                });

            })->export('xls');
        }
        elseif ($table == 'activities-per-task') {
            $excel_name = 'Actividades por Item';
            $sheet_name = 'Actividades';

            $task = Task::find($id);

            foreach($task->activities as $activity)
            {
                $activity->date = Carbon::parse($activity->date);

                $sheet_content->push(
                    [   '#'                     => $activity->number,
                        'Fecha'                 => date_format($activity->date,'d-m-Y'),
                        'Avance'                => $activity->progress.' '.$activity->task->units,
                        'Responsable'           => $activity->responsible ? $activity->responsible->name : '',
                        'Observaciones'         => wordwrap($activity->observations, 50, "\n", false),
                    ]);
            }

            $this->record_export('/activity/'.$task->id,'List of activities for this task',$task);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $task) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $task) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A6:F6', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A10:F10', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A6:F6')
                        ->mergeCells('A10:F10')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3')
                        ->mergeCells('B4:D4')
                        ->mergeCells('B5:D5');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Reporte de actividades por item')
                        ->setCellValue('A2', 'Item')
                        ->setCellValue('B2', wordwrap($task->name, 50, "\n", false))
                        ->setCellValue('A3', 'Sitio')
                        ->setCellValue('B3', wordwrap($task->site->name, 50, "\n", false))
                        ->setCellValue('A4', 'Proyecto')
                        ->setCellValue('B4', wordwrap($task->site->assignment->name, 50, "\n", false))
                        ->setCellValue('A5', 'Cliente')
                        ->setCellValue('B5', $task->site->assignment->client)
                        ->setCellValue('E3', '% completado')
                        ->setCellValue('F3', ($task->assigned_price ?
                                                number_format(($task->executed_price/$task->assigned_price)*100,2).' %' : ''))
                        ->setCellValue('E4', 'Project Manager')
                        ->setCellValue('F4', $task->site->responsible ? $task->site->responsible->name : '')
                        ->setCellValue('E5', 'Fecha de reporte')
                        ->setCellValue('F5', date('d-m-Y'))
                        ->setCellValue('A6', 'Información de item')
                        ->setCellValue('B7', 'C. proyectada')
                        ->setCellValue('C7', $task->total_expected.' '.$task->units)
                        ->setCellValue('B8', 'C. completada')
                        ->setCellValue('C8', $task->progress.' '.$task->units)
                        ->setCellValue('E7', 'Fecha de inicio')
                        ->setCellValue('F7', Carbon::parse($task->start_date)->format('d/m/Y'))
                        ->setCellValue('E8', 'Fecha de fin')
                        ->setCellValue('F8', Carbon::parse($task->end_date)->format('d/m/Y'))
                        ->setCellValue('A10', 'Detalle de avance')
                        ->setCellValue('E9', 'Estado')
                        ->setCellValue('F9', $task->statuses($task->status));

                    $sheet->fromArray($sheet_content, null, 'A11', true);

                    $offset = 11;

                    for($i=0;$i<=$sheet_content->count();$i++){
                        $sheet->mergeCells('E'.($offset+$i).':F'.($offset+$i));
                    }

                });

            })->export('xls');
        }
        elseif ($table == 'events_per_type') {
            if(!is_numeric($id)) {
                $type = explode('-', $id)[0];
                $true_id = explode('-', $id)[1];

                if ($type == 'site') {

                    $site = Site::find($true_id);

                    $excel_name = 'Eventos de sitio '.$this->normalize_name($site->name);
                    $sheet_name = 'Eventos';

                    foreach($site->events as $event)
                    {
                        $sheet_content->push(
                            [   'Fecha'             => Carbon::parse($event->date)->format('d-m-Y'),
                                'Evento'            => wordwrap($event->description, 50, "\n", false),
                                'Detalle'           => wordwrap($event->detail, 50, "\n", false),
                                'empty'             => 'white space',
                                'empty2'            => 'white space',
                                'Responsable'       => $event->responsible ? $event->responsible->name : '',
                            ]);
                    }

                    $this->record_export('/event/site/'.$site->id,'events related to this site',$site);

                    Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $site) {

                        $excel->sheet($sheet_name, function($sheet) use($sheet_content, $site) {

                            $sheet->cells('A1:F1', function($cells) {

                                $cells->setBackground('#98bfe6')
                                    ->setFontColor('#ffffff')
                                    ->setFontWeight('bold');

                            });
                            $sheet->cells('A7:F7', function($cells) {

                                $cells->setBackground('#98bfe6')
                                    ->setFontColor('#ffffff')
                                    ->setFontWeight('bold');

                            });

                            $sheet->mergeCells('A1:F1')
                                ->mergeCells('A7:F7')
                                ->mergeCells('B2:D2')
                                ->mergeCells('B3:D3')
                                ->mergeCells('B4:D4')
                                ->mergeCells('B5:D5');

                            $sheet->cell('A1', function($cell) {

                                $cell->setAlignment('center');

                            });

                            $sheet->setCellValue('A1', 'Reporte de eventos de sitio')
                                ->setCellValue('A2', 'Sitio')
                                ->setCellValue('B2', wordwrap($site->name, 50, "\n", false))
                                ->setCellValue('A3', 'Proyecto')
                                ->setCellValue('B3', wordwrap($site->assignment->name, 50, "\n", false))
                                ->setCellValue('A4', 'Cliente')
                                ->setCellValue('B4', $site->assignment->client)
                                ->setCellValue('A5', 'Project Manager')
                                ->setCellValue('B5', $site->responsible ? $site->responsible->name : '')
                                ->setCellValue('A6', '% completado')
                                ->setCellValue('B6', $site->percentage_completed.' %')
                                ->setCellValue('E3', 'Fecha de reporte')
                                ->setCellValue('F3', date('d/m/Y'))
                                ->setCellValue('E4', 'Fecha de inicio')
                                ->setCellValue('F4', Carbon::parse($site->start_date)->format('d/m/Y'))
                                ->setCellValue('E5', 'Fecha de fin')
                                ->setCellValue('F5', Carbon::parse($site->end_date)->format('d/m/Y'))
                                ->setCellValue('E6', 'Estado')
                                ->setCellValue('F6', $site->statuses($site->status))
                                ->setCellValue('A7', 'Eventos');

                            $sheet->fromArray($sheet_content, null, 'A8', true);

                            $offset = 8;

                            for($i=0;$i<=$sheet_content->count();$i++){
                                $sheet->mergeCells('C'.($offset+$i).':E'.($offset+$i));
                            }

                        });

                    })->export('xls');
                }
            }
        }
        elseif ($table == 'oc_certification') {
            $certificate = OcCertification::find($id);

            $this->record_export('/oc_certificate/'.$certificate->id,'Certificate to sign',$certificate);

            Excel::load('/public/file_layouts/OC_Certificate_model.xlsx', function($reader) use($certificate)
            {
                $sheetToChange = $reader->getActiveSheet();

                $sheetToChange->setCellValue('AI7', $certificate->code)
                    //'CFD-'.date_format($certificate->created_at,'ymd').'-'.str_pad($certificate->id, 3, "0", STR_PAD_LEFT)
                    ->setCellValue('L12', $certificate->user->area)
                    ->setCellValue('L13', $certificate->oc->provider)
                    ->setCellValue('L14', $certificate->oc->code)
                        //'OC-'.str_pad($certificate->oc_id, 5, "0", STR_PAD_LEFT)
                    ->setCellValue('L15', wordwrap($certificate->oc->proy_name.' '.
                        $certificate->oc->proy_description, 100, "\n", false))
                    ->setCellValue('AL12', $certificate->oc->assignment ? $certificate->oc->assignment->cost_center : '')
                    ->setCellValue('AL13', $certificate->oc->provider_record->nit)
                    ->setCellValue('AL14', $certificate->oc->client_oc)
                    ->setCellValue('M19', $certificate->type_reception=='Total' ? 'X' : '')
                    ->setCellValue('U19', $certificate->type_reception=='Parcial' ? 'X' : '')
                    ->setCellValue('AI19', $certificate->type_reception=='Parcial' ? $certificate->num_reception : '')
                    ->setCellValue('A22', Carbon::parse($certificate->date_ack)->format('d/m/Y'))
                    ->setCellValue('N22', Carbon::parse($certificate->date_acceptance)->format('d/m/Y'))
                    ->setCellValue('Z22', date('d/m/Y'))
                    ->setCellValue('AH22', $certificate->user->priv_level==4&&$certificate->oc->responsible ?
                        $certificate->oc->responsible->name : $certificate->oc->user->name)
                    ->setCellValue('AN24', 'Importe total bruto según '.$certificate->oc->code)
                    ->setCellValue('AP53', number_format($certificate->amount,2))
                    ->setCellValue('C54', ucfirst($this->convert_number_to_words($certificate->amount)).' Bolivianos')
                    ->setCellValue('E27', wordwrap($certificate->oc->proy_concept, 70, "\n", false))
                    ->setCellValue('AO27', number_format($certificate->oc->oc_amount,2))
                    //->setCellValue('C42', ucfirst($this->convert_number_to_words($certificate->oc->payed_amount)).' Bolivianos')
                    //->setCellValue('AP39', number_format($certificate->amount,2))
                    //->setCellValue('AP42', number_format($certificate->oc->payed_amount,2)
                        /*number_format($certificate->oc->executed_amount-$certificate->oc->payed_amount,2)*///)
                    //->setCellValue('AP43', number_format($certificate->oc->oc_amount-$certificate->oc->payed_amount
                        //-$certificate->amount /*$certificate->oc->oc_amount-$certificate->oc->executed_amount*/,2))
                    ->setCellValue('B47', wordwrap($certificate->observations, 100, "\n", false));

                if ($certificate->type_reception == 'Total') {
                    $posicionCierre = $certificate->observations ? 'B49' : 'B48';
                    $sheetToChange->setCellValue($posicionCierre, 'Con el presente certificado se cierra la orden de compra');
                }

                $i = 32; //number of row for starting invoices
                $reason = 'Adelanto';
                $listed_amount = 0;

                foreach ($certificate->oc->invoices as $invoice) {
                    //if($invoice->transaction_date!='0000-00-00 00:00:00'){
                    //if ($invoice->amount != $certificate->amount || $invoice->flags[0] == 1) {
                        //if($invoice->flags[5]==1)
                        //    $reason = 'Adelanto';
                        //elseif($invoice->flags[6]==1)
                        //    $reason = 'Pago contra avance';
                        //elseif($invoice->flags[7]==1)
                        //    $reason = 'Pago contra entrega';

                        //$reason .= $invoice->flags[0]==1 ? '' : ' (pendiente)';

                        $sheetToChange->setCellValue('A'.$i, $i-31)
                            ->setCellValue('E'.$i, $reason)
                            ->setCellValue('Z'.$i, $invoice->number)
                            ->setCellValue('AF'.$i, Carbon::parse($invoice->date_issued)->format('d/m/Y') )
                            /* ->setCellValue('AF'.$i, $invoice->transaction_date!='0000-00-00 00:00:00' ?
                                Carbon::parse($invoice->transaction_date)->format('d/m/Y') :
                                'Factura emitida '.Carbon::parse($invoice->date_issued)->format('d/m/Y') ) */
                            ->setCellValue('AP'.$i, $invoice->amount);

                        $i++;
                        $listed_amount += $invoice->amount;
                    //}
                    //}
                }

                $sheetToChange->setCellValue('A'.$i, $i-31)
                    ->setCellValue('A'.$i, $i-31)
                    ->setCellValue('E'.$i, 'Monto certificado actual')
                    ->setCellValue('AF'.$i, '------')
                    ->setCellValue('AP'.$i, number_format($certificate->amount,2))
                    //->setCellValue('E'.($i+1), 'Monto pagado a la fecha')
                    //->setCellValue('AO'.($i+1), number_format($certificate->oc->payed_amount,2))
                    //->setCellValue('E'.($i+3), 'Saldo no ejecutado')
                    //->setCellValue('AO'.($i+3), number_format($certificate->oc->oc_amount-$certificate->amount
                    //    -$listed_amount,2));
                    ->setCellValue('A'.($i+1), $i-30)
                    ->setCellValue('E'.($i+1), 'Total monto certificado a la fecha')
                    ->setCellValue('AF'.($i+1), '------')
                    ->setCellValue('AP'.($i+1), number_format($certificate->amount + $listed_amount, 2))
                    ->setCellValue('A'.($i+2), $i-29)
                    ->setCellValue('E'.($i+2), 'Saldo nominal según Orden de Compra')
                    ->setCellValue('AF'.($i+2), '------')
                    ->setCellValue('AP'.($i+2), number_format($certificate->oc->oc_amount-$certificate->amount
                        -$listed_amount,2));

                $reader->sheet('Certificado', function($sheet) use($i) {
                  // $sheet->mergeCells('AO'.($i+1).':AP'.($i+1));
                
                  $sheet->cell('AP'.($i+1), function($cell) {
                    $cell->setAlignment('right')
                        ->setFontWeight('bold');
                        // ->setBorder('solid', 'none', 'none', 'none');
                        /*->setBorder(array(
                          'top'   => array(
                              'style' => 'solid'
                          ),
                        ));*/
                  });
                  /*$sheet->cells('AO'.($i+1).':AP'.($i+1), function($cells) {
                    $cells->setAlignment('right')
                          ->setFontWeight('bold');
                    //$cells->setBorder(array(
                    //  'top'   => array(
                    //      'style' => 'solid'
                    //  ),
                    //));
                  });*/
                });

            })->export('xlsx');
        }
        elseif ($table == 'vehicle_history') {
            $vehicle = Vehicle::find($id);

            $excel_name = 'Historial de vehículo - '.$vehicle->license_plate;
            $sheet_name = 'Historial de vehículo';

            $vehicle_histories = VehicleHistory::where('vehicle_id',$id)->get();

            foreach($vehicle_histories as $history)
            {
                $sheet_content->push(
                    [   'Fecha'                 => date_format($history->created_at,'d/m/Y'),
                        'Tipo de registro'      => $history->type,
                        'Estado'                => $history->status,
                        'Contenido'             => wordwrap($history->contents, 70, "\n", false),
                    ]);
            }

            $this->record_export('/history/vehicle/'.$vehicle->id,'List of records for this vehicle',$vehicle);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $vehicle) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $vehicle) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:F5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A5:F5')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3')
                        ->mergeCells('B4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Historial de vehículo')
                        ->setCellValue('A2', 'Tipo')
                        ->setCellValue('A3', 'Modelo')
                        ->setCellValue('A4', 'Placa')
                        ->setCellValue('B2', $vehicle->type)
                        ->setCellValue('B3', $vehicle->model)
                        ->setCellValue('B4', $vehicle->license_plate)
                        ->setCellValue('E4', 'Fecha de reporte')
                        ->setCellValue('F4', date('d-m-Y'))
                        ->setCellValue('A5', 'Entradas en el historial');

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                    $offset = 6;

                    for($i=0;$i<=$sheet_content->count();$i++){
                        $sheet->mergeCells('D'.($offset+$i).':F'.($offset+$i));
                    }

                });

            })->export('xls');
        }
        elseif ($table == 'device_history') {
            $device = Device::find($id);

            $excel_name = 'Historial de equipo - '.$device->serial;
            $sheet_name = 'Historial de equipo';

            $device_histories = DeviceHistory::where('device_id',$id)->get();

            foreach($device_histories as $history)
            {
                $sheet_content->push(
                    [   'Fecha'                 => date_format($history->created_at,'d/m/Y'),
                        'Tipo de registro'      => $history->type,
                        'Estado'                => $history->status,
                        'Contenido'             => wordwrap($history->contents, 70, "\n", false),
                    ]);
            }

            $this->record_export('/history/device/'.$device->id,'List of records for this device',$device);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $device) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $device) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:F5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A5:F5')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3')
                        ->mergeCells('B4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Historial de equipo')
                        ->setCellValue('A2', 'Tipo')
                        ->setCellValue('A3', 'Modelo')
                        ->setCellValue('A4', 'Serial')
                        ->setCellValue('B2', $device->type)
                        ->setCellValue('B3', $device->model)
                        ->setCellValue('B4', $device->serial)
                        ->setCellValue('E4', 'Fecha de reporte')
                        ->setCellValue('F4', date('d-m-Y'))
                        ->setCellValue('A5', 'Entradas en el historial');

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                    $offset = 6;

                    for($i=0;$i<=$sheet_content->count();$i++){
                        $sheet->mergeCells('D'.($offset+$i).':F'.($offset+$i));
                    }

                });

            })->export('xls');
        }
        elseif ($table == 'dead_intervals_assig') {
            $assignment = Assignment::find($id);

            $excel_name = 'Tiempos muertos - '.$assignment->code;
            $sheet_name = 'Tiempos muertos';

            foreach($assignment->dead_intervals as $dead_interval)
            {
                $sheet_content->push(
                    [   'Desde'                 => date_format(Carbon::parse($dead_interval->date_from),'d/m/Y'),
                        'Hasta'                 => empty($dead_interval->date_to) ? 'En marcha' :
                            date_format(Carbon::parse($dead_interval->date_to),'d/m/Y'),
                        'Total en días'         => $dead_interval->total_days,
                        'Motivo'                => wordwrap($dead_interval->reason, 70, "\n", false),
                    ]);
            }

            $this->record_export('/dead_interval?assig_id='.$assignment->id,'List of dead intervals for assignment',$assignment);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $assignment) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $assignment) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:F5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A5:F5')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3')
                        ->mergeCells('B4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Listado de intervalos de tiempo muerto')
                        ->setCellValue('A2', 'Asignación')
                        ->setCellValue('A3', 'Cliente')
                        ->setCellValue('A4', 'Responsable')
                        ->setCellValue('B2', wordwrap($assignment->name, 70, "\n", false))
                        ->setCellValue('B3', $assignment->client)
                        ->setCellValue('B4', $assignment->responsible ? $assignment->responsible->name : '')
                        ->setCellValue('E4', 'Fecha de reporte')
                        ->setCellValue('F4', date('d-m-Y'))
                        ->setCellValue('A5', 'Tiempos muertos');

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                    $offset = 6;

                    for($i=0;$i<=$sheet_content->count();$i++){
                        $sheet->mergeCells('D'.($offset+$i).':F'.($offset+$i));
                    }

                });

            })->export('xls');
        }
        elseif ($table == 'dead_intervals_st') {
            $site = Site::find($id);

            $excel_name = 'Tiempos muertos - '.$site->code;
            $sheet_name = 'Tiempos muertos';

            foreach($site->dead_intervals as $dead_interval)
            {
                $sheet_content->push(
                    [   'Desde'                 => date_format(Carbon::parse($dead_interval->date_from),'d/m/Y'),
                        'Hasta'                 => empty($dead_interval->date_to) ? 'En marcha' :
                            date_format(Carbon::parse($dead_interval->date_to),'d/m/Y'),
                        'Total en días'         => $dead_interval->total_days,
                        'Motivo'                => wordwrap($dead_interval->reason, 50, "\n", false),
                    ]);
            }

            $this->record_export('/dead_interval?st_id='.$site->id,'List of dead intervals for this site',$site);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $site) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $site) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A5:F5', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A5:F5')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3')
                        ->mergeCells('B4:D4');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Listado de intervalos de tiempo muerto')
                        ->setCellValue('A2', 'Sitio')
                        ->setCellValue('A3', 'Asignación')
                        ->setCellValue('A4', 'Responsable')
                        ->setCellValue('B2', wordwrap($site->name, 70, "\n", false))
                        ->setCellValue('B3', wordwrap($site->assignment->name, 70, "\n", false))
                        ->setCellValue('B4', $site->responsible ? $site->responsible->name : '')
                        ->setCellValue('E4', 'Fecha de reporte')
                        ->setCellValue('F4', date('d-m-Y'))
                        ->setCellValue('A5', 'Tiempos muertos');

                    $sheet->fromArray($sheet_content, null, 'A6', true);

                    $offset = 6;

                    for($i=0;$i<=$sheet_content->count();$i++){
                        $sheet->mergeCells('D'.($offset+$i).':F'.($offset+$i));
                    }

                });

            })->export('xls');
        }
        elseif ($table == 'materials') {
            $excel_name = 'Materiales por almacén';
            $sheet_name = 'Materiales';

            $warehouse = Warehouse::find($id);
            $i = 1;

            foreach($warehouse->materials as $material)
            {
                $sheet_content->push(
                    [   '#'                     => $i,
                        'Código'                => $material->code,
                        'Nombre'                => wordwrap($material->name, 50, "\n", false),
                        'Tipo'                  => $material->type,
                        'Unidades'              => $material->units,
                        'Cantidad'              => number_format($material->pivot->qty,2),
                    ]);

                $i++;
            }

            $this->record_export('/warehouse/materials/'.$warehouse->id,'List of materials in this warehouse',$warehouse);

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $warehouse) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $warehouse) {

                    $sheet->cells('A1:F1', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });
                    $sheet->cells('A4:F4', function($cells) {

                        $cells->setBackground('#98bfe6')
                            ->setFontColor('#ffffff')
                            ->setFontWeight('bold');

                    });

                    $sheet->mergeCells('A1:F1')
                        ->mergeCells('A4:F4')
                        ->mergeCells('B2:D2')
                        ->mergeCells('B3:D3');

                    $sheet->cell('A1', function($cell) {

                        $cell->setAlignment('center');

                    });

                    $sheet->setCellValue('A1', 'Materiales disponibles en Almacén')
                        ->setCellValue('A2', 'Almacén')
                        ->setCellValue('A3', 'Fecha de reporte')
                        ->setCellValue('B2', wordwrap($warehouse->name, 200, "\n", false))
                        ->setCellValue('B3', date('d-m-Y'))
                        ->setCellValue('A4', 'Materiales');

                    $sheet->fromArray($sheet_content, null, 'A5', true);

                });

            })->export('xls');
        }
        elseif ($table == 'tasks_qty') {
            $real_id = explode('-',$id)[1];
            $type = explode('-',$id)[0];

            $site = Site::find($real_id);

            if($type=='rural')
                $name_of_file = '/public/file_layouts/Planilla_SPE2017_rural.xlsx';
            elseif($type=='urban')
                $name_of_file = '/public/file_layouts/Planilla_SPE2017_urban.xlsx';
            else
                $name_of_file = '/public/file_layouts/certificado_economico_mod.xlsx'; //'/public/file_layouts/Planilla_GRAL2017.xlsx';

            if($name_of_file!='')
                $data = Excel::load($name_of_file, function($reader) {})->get();
            else{
                Session::flash('message', "No se pudo completar la operación solicitada,
                    el documento base no fue encontrado en el sistema. Por favor contácte al administrador");
                return redirect()->back();
            }

            /*
            $excelIsValid = false;

            foreach($data as $ex){
                if(isset($ex["a"])&&isset($ex["b"])&&isset($ex["c"])&&isset($ex["d"])&&isset($ex["e"])&&isset($ex["f"]))
                    $excelIsValid = true;
            }

            if($excelIsValid==false){
                Session::flash('message', "El archivo modelo ha sido modificado y ha quedado inutilizable! Por favor
                    contácte al administrador del sistema.");
                return redirect()->back();
            }
            */

            /*
            if($type=='rural'||$type=='urban'){
                if(!empty($data) && $data->count()){

                    $i = 1;

                    foreach ($data as $key => $value) {
                        $i++;

                        if(!empty($value->a)&&!empty($value->b)&&!empty($value->c)&&!empty($value->d)&&
                            is_numeric($value->a)&&is_numeric($value->d))
                        {
                            foreach($site->tasks as $task){
                                if($task->item){
                                    if($task->item->number==$value->a){
                                        $insert[] = [
                                            'i'                 => $i,
                                            'executed'          => $task->progress,
                                        ];

                                    }
                                }
                            }
                        }
                    }
                    if(!empty($insert)){

                        $this->record_export('/task/'.$site->id,$name_of_file,$site);

                        Excel::load($name_of_file, function($reader) use($insert, $site)
                        {
                            $sheetTochange = $reader->getActiveSheet();

                            foreach($insert as $record){

                                $sheetTochange->setCellValue('E'.$record['i'], $record['executed']);

                            }

                            $sheetTochange->setCellValue('A7', strtoupper($site->name));

                        })->export('xlsx');

                    }
                    else{
                        Session::flash('message', "No se encontraron datos para generar la planilla");
                        return redirect()->back();
                    }
                }
            }
            */
            //else{
                $i = 8;

                $subcategories = Task::join('items', 'tasks.item_id', '=', 'items.id')
                    ->select('items.subcategory')
                    ->where('tasks.site_id', $real_id)
                    ->groupBy('items.subcategory')
                    ->get();

                $total = 0;

                foreach($subcategories as $subcategory){
                    if($subcategory->subcategory!=''){
                        $insert[] = [
                            'i'                 => $i,
                            'number'            => 'subcategory',
                            'description'       => $subcategory->subcategory,
                            'units'             => 0,
                            'cost_per_unit'     => 0,
                            'executed'          => 0,
                        ];

                        $tasks = Task::join('items', 'tasks.item_id', '=', 'items.id')
                            ->select('tasks.*')
                            ->where('tasks.site_id', $real_id)
                            ->where('items.subcategory', $subcategory->subcategory)
                            ->get();

                        $i++;

                        foreach($tasks as $task){

                            $insert[] = [
                                'i'                 => $i,
                                'number'            => $task->item ? $task->item->number : '',
                                'description'       => $task->name,
                                'units'             => $task->units,
                                'cost_per_unit'     => $task->quote_price,
                                'executed'          => $task->progress,
                            ];

                            $total += ($task->quote_price*$task->progress);
                            $i++;
                        }
                    }
                }

                if(!empty($insert)){

                    //$this->record_export('/task/'.$site->id,$name_of_file,$site);

                    Excel::load($name_of_file, function($reader) use($insert, $site, $total)
                    {
                        //$sheetTochange = $reader->getActiveSheet();

                        $reader->sheet('PLANILLA', function($sheet) use($insert, $site, $total) {

                            $sheet->cells('A7:E7', function($cells) {
                                $cells->setBackground('#8cb4e2');
                            });

                            foreach($insert as $record){

                                if($record['number']=='subcategory'){
                                    //$sheetTochange->mergeCells('A'.$record['i'].':G'.$record['i']);

                                    //$reader->sheet('PLANILLA', function($sheet) use($record) {

                                        $sheet->cells('A'.$record['i'].':G'.$record['i'], function($cells) {

                                            $cells->setBackground('#dce6f1')
                                                ->setFontWeight('bold');

                                        });

                                    //});

                                    $sheet->setCellValue('B'.$record['i'], $record['description']);
                                }
                                else{
                                    //$sheet->mergeCells('B'.$record['i'].':C'.$record['i']);

                                    $sheet->setCellValue('A'.$record['i'], $record['number'])
                                        ->setCellValue('B'.$record['i'], $record['description'])
                                        ->setCellValue('D'.$record['i'], $record['units'])
                                        ->setCellValue('E'.$record['i'], $record['executed'])
                                        ->setCellValue('F'.$record['i'], $record['cost_per_unit'])
                                        ->setCellValue('G'.$record['i'], $record['cost_per_unit']*$record['executed']);
                                }
                            }

                            $lastRow = $sheet->getHighestRow();


                            $sheet->setCellValue('C1', strtoupper($site->assignment->client))
                                ->setCellValue('C2', strtoupper($site->assignment->name))
                                ->setCellValue('C5', strtoupper($site->name))
                                ->setCellValue('D'.($lastRow+1), 'TOTAL BRUTO [Bs]')
                                ->setCellValue('G'.($lastRow+1), $total) //'=SUM(G8:G'.$lastRow.')');
                                ->setCellValue('B'.($lastRow+2), 'Son: '.
                                    ucfirst($this->convert_number_to_words($total)).' Bolivianos');

                            $sheet->cells('A'.($lastRow+1).':G'.($lastRow+2), function($cells) {

                                $cells->setBorder('medium', 'medium', 'medium', 'medium')
                                    ->setFontWeight('bold');

                            });

                        });

                    })->export('xlsx');
                }
                else {
                    Session::flash('message', "No se encontraron datos para generar la planilla");
                    return redirect()->back();
                }
            //}
        }
        /*
        elseif ($table == 'rbs_viatics') {
            $rbs_viatics = RbsViatic::find($id);

            $technicians = $rbs_viatics->technician_requests;
            $sites = $rbs_viatics->sites;

            $this->record_export('/rbs_viatic/'.$rbs_viatics->id,'Viatic request',$rbs_viatics);

            Excel::load('/public/file_layouts/rbs_viatic_model.xlsx', function($reader)
            use($rbs_viatics, $technicians, $sites)
            {
                foreach($reader->get() as $key => $sheet) {
                    $sheetTitle = $sheet->getTitle();
                    $sheetTochange = $reader->setActiveSheetIndex($key);

                    if($sheetTitle === 'General') {

                        $sheetTochange->setCellValue('C2', $rbs_viatics->id)
                            ->setCellValue('C3', $rbs_viatics->work_description)
                            ->setCellValue('C4', $sites->first()->assignment->client)
                            ->setCellValue('C5', wordwrap($sites->first()->assignment->name, 50, "\n", false))
                            ->setCellValue('C12', Carbon::parse($rbs_viatics->date_from)->format('d/m/Y'))
                            ->setCellValue('C13', Carbon::parse($rbs_viatics->date_to)->format('d/m/Y'))
                            ->setCellValue('F17', $rbs_viatics->type_transport)
                            ->setCellValue('C18', $rbs_viatics->sub_total_transport)
                            ->setCellValue('C20', $rbs_viatics->extra_expenses)
                            ->setCellValue('E18', $rbs_viatics->vehicle_rent_days);

                        $i = 12;
                        foreach($technicians as $technician){
                            if($technician->technician)
                                $sheetTochange->setCellValue('F'.$i, $technician->technician->name);

                            $i++;
                        }
                    }

                    if($sheetTitle === 'Viaticos') {
                        $i = 5;
                        foreach($technicians as $technician){
                            $sheetTochange->setCellValue('E'.$i, $technician->departure_cost+$technician->return_cost)
                                ->setCellValue('F'.$i, $technician->num_days)
                                ->setCellValue('G'.$i, $technician->viatic_amount)
                                ->setCellValue('I'.$i, $technician->extra_expenses);

                            $i++;
                        }

                        $sheetTochange->setCellvalue('A4', Carbon::now()->format('d/m/Y'));
                    }
                }
            })->export('xlsx');
        }
        */
        elseif ($table == 'client_listed_material') {
            $rbs_char = RbsSiteCharacteristic::find($id);

            if($rbs_char->site->assignment->client=='ZTE'){
                $model_to_load ='/public/file_layouts/inventario_material_zte.xlsx';
                $listed_materials = ClientListedMaterial::where('client', 'ZTE')
                    ->where('applies_to', 'like', "%$rbs_char->solution%")->get();
                $offset = 9;
            }
            else{
                Session::flash('message', 'No se encontró un formato para generar el archivo solicitado!');
                return redirect()->back();
            }

            if($listed_materials->count()==0){
                Session::flash('message', 'No existen materiales en inventario para este tipo de solución!
                    Verifique que el tipo de solución exista, y que esté en un formato válido');
                return redirect()->back();
            }

            //$this->record_export('/site/'.$rbs_char->site->assignment_id.'/show','List of materials',$rbs_char);

            Excel::load($model_to_load, function($reader) use($rbs_char, $listed_materials, $offset)
            {
                $sheetTochange = $reader->getActiveSheet();

                $sheetTochange->setCellValue('F4', $rbs_char->site->name)
                    ->setCellValue('C5', $rbs_char->site->municipality)
                    ->setCellValue('F5', $rbs_char->solution)
                    ->setCellValue('C6', 'ABROS TECHNOLOGIES SRL')
                    ->setCellValue('F6', $rbs_char->tech_group ? ($rbs_char->tech_group->group_head ?
                        $rbs_char->tech_group->group_head->name : '') : '');

                $i = 1;

                foreach($listed_materials as $listed_material)
                {
                    $sheetTochange->setCellValue('A'.$offset, $i)
                        ->setCellValue('B'.$offset, $listed_material->name)
                        ->setCellValue('C'.$offset, $listed_material->model);

                    $offset++;
                    $i++;
                }

            })->export('xlsx');
        }
        elseif ($table == 'vhc_failure_reports') {
            $vehicle = Vehicle::find($id);

            $excel_name = 'Tabla de reportes de falla - vehiculo '.$vehicle->license_plate;
            $sheet_name = 'Reportes de falla - '.$vehicle->license_plate;

            $reports = VhcFailureReport::where('vehicle_id', $vehicle->id)->get();

            $this->record_export('/vehicle_failure_report?vhc='.$vehicle->id,
                'vhc_failure_reports records with '.$vehicle->license_plate, 0);

            return $this->create_excel($excel_name, $sheet_name, $reports);
        }
        elseif ($table == 'dvc_failure_reports') {
            $device = Device::find($id);

            $excel_name = 'Tabla de reportes de falla - equipo '.$device->serial;
            $sheet_name = 'Reportes de falla - '.$device->serial;

            $reports = DvcFailureReport::where('device_id', $device->id)->get();

            $this->record_export('/device_failure_report?dvc='.$device->id,
                'dvc_failure_reports records with '.$device->serial, 0);

            return $this->create_excel($excel_name, $sheet_name, $reports);
        }
        elseif ($table === 'stipend_requests') {
            $assignment = Assignment::find($id);

            $excel_name = 'Tabla de solicitudes de viaticos - asignacion '.$assignment->code;
            $sheet_name = 'Viaticos - '.$assignment->code;

            $stipend_requests = StipendRequest::where('assignment_id', $assignment->id)->get();
            
            foreach($stipend_requests as $stipend_request) {
                $stipend_request->cost_center = $assignment && $assignment->cost_center > 0 ? $assignment->cost_center : '';
            }
            
            $this->record_export('/stipend_request?asg='.$assignment->id,
                'stipend_requests records with '.$assignment->code, 0);

            return $this->create_excel($excel_name, $sheet_name, $stipend_requests);
        }

        /* Last resort redirection when no match is found */
        return redirect()->back();
    }

    public function export_info_page($type, $id)
    {
        //Disabled function
        /*
        $excel_name = 'empty';
        $sheet_name = 'empty';
        $sheet_content = collect();

        if($type=='site')
            return 'continue';

        return redirect()->back();
        */
    }

    public function create_excel($excel_name, $sheet_name, $sheet_content)
    {
        Excel::create($excel_name, function($excel) use($sheet_name,$sheet_content) {

            $excel->sheet($sheet_name, function($sheet) use($sheet_content) {

                $sheet->fromArray($sheet_content);

            });
        })->export('xls');
    }

    public function import_form($type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $place = 0;
        $options = collect();
        $complements = 0;
        
        if($type == 'tasks'||$type=='tasks-from-oc')
            $place = Site::find($id);
        if($type=='sites'||$type=='stipend_requests')
            $place = Assignment::find($id);
        if($type=='items'||$type=='tasks')
            $options = ItemCategory::select('name')->where('status', 1)->get();
            //Item::select('category')->where('category', '<>', '')->groupBy('category')->get();
        if($type=='items'){
            $complements = Project::select('id','name')->where('status', 'Activo')->get();
        }

        if($type == 'client_listed_materials')
            $options = ClientListedMaterial::select('client')->where('client', '<>', '')->groupBy('client')->get();

        return View::make('app.import_form', ['id' => $id, 'type' => $type, 'place' => $place, 'service' => $service,
            'options' => $options, 'complements' => $complements ]);
    }

    public function import_items(Request $request, $type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        //$service = Session::get('service');

        $file_to_import = $request->file('import_file');

        if ($request->hasFile('import_file')) {

            $v = \Validator::make($request->file(), [
                'import_file'   => 'mimes:xls,xlsx',
            ]);

            if ($v->fails()) {
                Session::flash('message', "Tipo de archivo no soportado, solo puede importar archivos Excel!");
                return redirect()->back()->withInput();
            }

            if($type=='tasks') {

                $category = $request->input('category');
                //$area = $request->input('area') ?: $user->work_type;

                if($category==""){
                    Session::flash('message', 'Debe seleccionar una categoría!');
                    return redirect()->back()->withInput();
                }

                $site = Site::find($id);

                $path = $file_to_import->getRealPath();
                $data = Excel::load($path, function($reader) {
                })->get(); // ->skip(1)

                $excelIsValid = false;

                foreach($data as $ex){
                    if(isset($ex["a"])&&isset($ex["b"])&&isset($ex["c"])&&isset($ex["d"]))
                        $excelIsValid = true;
                }
                /*
                    $to_validate = $data->toArray();
                        $number_of_records = count($to_validate);

                        $i = 0;

                        while($i<$number_of_records){
                        $v = \Validator::make($to_validate[$i], [
                            'd'    => 'numeric',
                            'e'    => 'numeric',
                        ]);

                        if ($v->fails()) {
                            $excelIsValid = false;
                            }
                        $i++;
                        }
                /*
                    $converted_excelIsValid = ($excelIsValid) ? 'true' : 'false';
                    return $converted_excelIsValid;
                */

                if ($excelIsValid == false) {
                    Session::flash('message', "El archivo seleccionado contiene datos que no pueden ser importados!");
                    return redirect()->back()->withInput();
                }

                if (!empty($data) && $data->count()) {
                    $prev_number = Task::select('number')->where('site_id',$id)->OrderBy('number','desc')->first();
                    $to_assign_number = empty($prev_number) ? 1 : $prev_number->number+1;

                    foreach ($data as $key => $value) {
                        if(!empty($value->a)&&!empty($value->b)&&!empty($value->c)&&!empty($value->d)&&
                            is_numeric($value->a)&&is_numeric($value->d)&&$value->d>0)
                        {
                            $item = Item::where('description', $value->b)->where('category', $category)
                                ->orderBy('updated_at','desc')->first();

                            if($item){
                                $insert[] = [
                                    'user_id'           => $user->id,
                                    'site_id'           => $site->id,
                                    'item_id'           => $item->id,
                                    'number'            => $to_assign_number,
                                    'name'              => $value->b,
                                    //'description'       => $value->f,
                                    'pondered_weight'   => 1, //Default value for weights
                                    'total_expected'    => $value->d,
                                    'units'             => $value->c,
                                    'status'            => $site->status,
                                    /*$site->status=='Relevamiento'||$site->status=='Cotizado' ? 'En espera' : $site->status*/
                                    'quote_price'       => $item->cost_unit_central,
                                    'assigned_price'    => $value->d*$item->cost_unit_central,
                                    'start_date'        => $site->start_date,
                                    'end_date'          => $site->end_date,
                                    'created_at'        => date('Y-m-d H:i:s')
                                ];

                                $to_assign_number++;
                            }
                        }
                    }
                    if (!empty($insert)) {

                        Task::insert($insert);

                        $empty_coded_tasks = Task::where('code', '')->get();

                        foreach ($empty_coded_tasks as $task) {
                            $task->code = 'TK-' . str_pad($task->id, 4, "0", STR_PAD_LEFT) . '0' .
                                $task->number . date_format($task->created_at, '-y');
                            $task->save();
                        }

                        $message = "Los items fueron agregados al sitio correctamente";
                    }
                    else{
                        $message = "No se cargó ningún item!";
                    }

                    Session::flash('message', $message);
                    if(Session::has('url'))
                        return redirect(Session::get('url'));
                    else
                        return redirect()->action('TaskController@tasks_per_site', ['id' => $id]);
                }
                /*
                * Old code (before the implementation of an items table)

                $site = Site::find($id);

                $path = $file_to_import->getRealPath();
                $data = Excel::load($path, function($reader) {
                })->get();

                $excelIsValid = false;

                foreach($data as $ex){
                    if(isset($ex["numero"])&&isset($ex["nombre"])&&isset($ex["unidades"])&&isset($ex["cantidad"])&&
                        isset($ex["precio"]))
                        $excelIsValid = true;
                }

                $to_validate = $data->toArray();
                $number_of_records = count($to_validate);

                $i = 0;

                while($i<$number_of_records){
                    $v = \Validator::make($to_validate[$i], [
                        'numero'    => 'required|numeric',
                        'nombre'    => 'required',
                        'unidades'  => 'required',
                        'cantidad'  => 'required|numeric',
                        'precio'    => 'required|numeric',
                    ]);

                    if ($v->fails()) {
                        $excelIsValid = false;
                    }
                    $i++;
                }

                if($excelIsValid==false){
                    Session::flash('message', " El archivo seleccionado contiene datos que no pueden ser importados. 
                            Descargue el archivo modelo para referencia! ");
                    return redirect()->back();
                }

                if(!empty($data) && $data->count()){
                    foreach ($data as $key => $value) {
                        $insert[] = [
                            'user_id'           => $user->id,
                            'site_id'           => $site->id,
                            'number'            => $value->numero,
                            'name'              => $value->nombre,
                            'units'             => $value->unidades,
                            'status'            => 'Ejecución',
                            'total_expected'    => $value->cantidad,
                            'quote_price'       => $value->precio,
                            'assigned_price'    => $value->precio*$value->cantidad,
                            'start_date'        => $site->start_date,
                            'end_date'          => $site->end_date,
                            'created_at'        => date('Y-m-d H:i:s')];
                    }
                    if(!empty($insert)){

                        Task::insert($insert);

                        Session::flash('message', " Los items se agregaron correctamente ");
                        return redirect()->action('TaskController@tasks_per_site', ['id' => $id]);
                    }
                }
                */
            }

            if ($type == 'sites') {
                $assignment = Assignment::find($id);

                $path = $file_to_import->getRealPath();
                $data = Excel::load($path, function($reader) {
                })->get();

                $excelIsValid = false;

                foreach($data as $ex){
                    if(isset($ex["numero"])&&isset($ex["nombre"])&&isset($ex["fecha_de_inicio"])&&
                        isset($ex["fecha_de_fin"]))
                        $excelIsValid = true;
                }

                $to_validate = $data->toArray();
                $number_of_records = count($to_validate);
                $i = 0;

                while($i<$number_of_records){
                    $v = \Validator::make($to_validate[$i], [
                        'numero'            => 'numeric',
                        'nombre'            => 'required',
                        'fecha_de_inicio'   => 'date',
                        'fecha_de_fin'      => 'date',
                    ]);

                    if ($v->fails()) {
                        $excelIsValid = false;
                    }
                    $i++;
                }

                if ($excelIsValid == false) {
                    Session::flash('message', "El archivo seleccionado contiene datos que no pueden ser importados. 
                            Descargue el archivo modelo para referencia!");
                    return redirect()->back()->withInput();
                }

                if (!empty($data) && $data->count()) {
                    foreach ($data as $key => $value) {
                        $insert[] = [
                            'user_id'           => $user->id,
                            'name'              => $value->nombre,
                            'assignment_id'     => $assignment->id,
                            'status'            => $assignment ? $assignment->status : 1 /* Initial status */,
                            'origin_name'       => $value->nombre,
                            'latitude'          => isset($value['latitud']) ? $value->latitud : '',
                            'longitude'         => isset($value['longitud']) ? $value->longitud : '',
                            'department'        => isset($value['departamento']) ? $value->departamento : '',
                            'municipality'      => isset($value['municipio']) ? $value->municipio : '',
                            'type_municipality' => isset($value['area']) ? $value->area : '',
                            'contact_id'        => $assignment->contact_id,
                            'start_line'        => $value->fecha_de_inicio ? $value->fecha_de_inicio->format('Y-m-d') :
                                ($assignment->start_date ? $assignment->start_date : $assignment->quote_from),
                            'deadline'          => $value->fecha_de_fin ? $value->fecha_de_fin->format('Y-m-d') :
                                ($assignment->end_date ? $assignment->end_date : $assignment->quote_to),
                            'start_date'        => $value->fecha_de_inicio ? $value->fecha_de_inicio->format('Y-m-d') :
                                ($assignment->start_date ? $assignment->start_date : $assignment->quote_from),
                            'end_date'          => $value->fecha_de_fin ? $value->fecha_de_fin->format('Y-m-d') :
                                ($assignment->end_date ? $assignment->end_date : $assignment->quote_to),
                            'created_at'        => date('Y-m-d H:i:s')];
                    }
                    if(!empty($insert)){

                        Site::insert($insert);

                        $empty_coded_sites = Site::where('code','')->get();

                        foreach($empty_coded_sites as $site)
                        {
                            $site->code = 'ST-'.str_pad($site->id, 4, "0", STR_PAD_LEFT).
                                date_format($site->created_at,'-y');
                            $site->save();
                        }

                        $message = "Los sitios fueron importados correctamente";
                    }
                    else{
                        $message = 'No se cargó ningún sitio!';
                    }

                    Session::flash('message', $message);
                    if(Session::has('url'))
                        return redirect(Session::get('url'));
                    else
                        return redirect()->action('SiteController@sites_per_project', ['id' => $id]);
                }
            }

            if ($type == 'items') {
                $category = $request->input('category');
                $area = $request->input('area');
                $project_id = $request->input('project_id');

                if($category=='Otro'||$category==''){
                    if($request->input('other_category')==""){
                        //$category = 'Otros - '.$area;
                        Session::flash('message', 'Debe indicar una categoría!');
                        return redirect()->back()->withInput();
                    }
                    else
                        $category = $request->input('other_category');
                }

                $path = $file_to_import->getRealPath();
                $data = Excel::load($path, function($reader) {
                    })->get(); // ->skip(1)

                $excelIsValid = false;

                foreach($data as $ex){
                    if(isset($ex["number"])&&isset($ex["description"])&&isset($ex["units"])&&isset($ex["cost_unit_central"]))
                        $excelIsValid = true;
                }
                /*
                $to_validate = $data->toArray();
                $number_of_records = count($to_validate);

                $i = 0;

                while($i<$number_of_records){
                    $v = \Validator::make($to_validate[$i], [
                        'd'    => 'numeric',
                        'e'    => 'numeric',
                    ]);

                    if ($v->fails()) {
                        $excelIsValid = false;
                    }
                    $i++;
                }
                /*
                                $converted_excelIsValid = ($excelIsValid) ? 'true' : 'false';
                                return $converted_excelIsValid;
                */

                if ($excelIsValid == false) {
                    Session::flash('message', "El archivo seleccionado contiene datos que no pueden ser importados!
                        Por favor utilice el formato modelo");
                    return redirect()->back()->withInput();
                }

                if (!empty($data) && $data->count()) {
                    if (ItemCategory::where('name', $category)->exists()) {
                        $new_category = ItemCategory::where('name', $category)->first();
                    } else {
                        $new_category = new ItemCategory();
                        $new_category->project_id = $project_id ?: 0;
                        $new_category->name = $category;
                        $new_category->area = $area ?: $user->work_type;
                        $new_category->status = 1;

                        $new_category->save();
                    }

                    foreach ($data as $key => $value) {
                        if(!empty($value->number)&&!empty($value->description)&&!empty($value->units)&&
                            !empty($value->cost_unit_central)&&/*is_numeric($value->number)&&*/
                            is_numeric($value->cost_unit_central)){

                            $item_exists = Item::where('number', $value->number)->where('description', $value->description)
                                ->where('units', $value->units)->where('cost_unit_central', $value->cost_unit_central)
                                ->where('item_category_id', $new_category->id)->exists();

                            if(!$item_exists) {
                                $insert[] = [
                                    'number' => $value->number,
                                    'client_code' => isset($value["client_code"]) ? $value->client_code : '',
                                    'subcategory' => isset($value["subcategory"]) ? $value->subcategory : '',
                                    'description' => $value->description,
                                    'units' => $value->units,
                                    'cost_unit_central' => $value->cost_unit_central,
                                    //'cost_unit_remote'  => $value->e,
                                    'detail' => isset($value["detail"]) ? $value->detail : '',
                                    'category' => $new_category->name,
                                    'item_category_id' => $new_category->id,
                                    'area' => $area,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')];
                            }
                        }
                    }
                    if (!empty($insert)) {
                        Item::insert($insert);

                        $message = "Los items fueron cargados al sistema correctamente";
                    }
                    else{
                        $message = 'No se cargó ningún item!';
                    }

                    Session::flash('message', $message);
                    if(Session::has('url'))
                        return redirect(Session::get('url'));
                    elseif ($id!=0)
                        return redirect()->action('TaskController@tasks_per_site', ['id' => $id]);
                    else
                        return redirect()->route('item_category.index');
                }
            }

            if ($type == 'client_listed_materials') {
                $client = $request->input('client');

                if ($client=='Otro'||$client=='') {
                    if($request->input('other_client')==""){
                        Session::flash('message', 'Debe indicar un cliente!');
                        return redirect()->back();
                    }
                    else
                        $client = $request->input('other_client');
                }

                $path = $file_to_import->getRealPath();
                $data = Excel::load($path, function($reader) {
                })->get();

                $excelIsValid = false;

                foreach($data as $ex){
                    if(isset($ex["codigo"])&&isset($ex["nombre"])&&isset($ex["modelo"])&&isset($ex["aplica_a"]))
                        $excelIsValid = true;
                }

                $to_validate = $data->toArray();
                $number_of_records = count($to_validate);
                $i = 0;

                while($i<$number_of_records){
                    $v = \Validator::make($to_validate[$i], [
                        'nombre'            => 'required',
                    ]);

                    if ($v->fails()) {
                        $excelIsValid = false;
                    }
                    $i++;
                }

                if ($excelIsValid == false) {
                    Session::flash('message', "El archivo seleccionado contiene datos que no pueden ser importados. 
                            Descargue el archivo modelo para referencia!");
                    return redirect()->back();
                }

                if (!empty($data) && $data->count()) {
                    foreach ($data as $key => $value) {
                        if(!empty($value->nombre)) {
                            $insert[] = [
                                'user_id'       => $user->id,
                                'client'        => $client,
                                'code'          => $value->codigo,
                                'name'          => $value->nombre,
                                'model'         => $value->modelo,
                                'applies_to'    => $value->aplica_a,
                                'created_at'    => date('Y-m-d H:i:s'),
                                'updated_at'    => date('Y-m-d H:i:s')];
                        }
                    }
                    if(!empty($insert)){
                        ClientListedMaterial::insert($insert);

                        $message = "Los materiales contenidos en el archivo fueron importados correctamente";
                    } else {
                        $message = 'No se cargó ningún material!';
                    }

                    Session::flash('message', $message);
                    if(Session::has('url'))
                        return redirect(Session::get('url'));
                    else
                        return redirect()->action('SiteController@sites_per_project', ['id' => $id]);
                }
            }

            if ($type == 'stipend_requests') {
                $assignment = Assignment::find($id);
                $assignment->start_date = Carbon::parse($assignment->start_date);
                $assignment->end_date = Carbon::parse($assignment->end_date);

                $path = $file_to_import->getRealPath();
                $data = Excel::load($path, function($reader) {
                })->get();

                $excelIsValid = false;

                foreach($data as $ex){
                    if(isset($ex["solicitado_para"])&&isset($ex["viatico_por_dia"])&&isset($ex['motivo'])&&
                        isset($ex["fecha_desde"])&&isset($ex["fecha_hasta"]))
                        $excelIsValid = true;
                }

                $to_validate = $data->toArray();
                $number_of_records = count($to_validate);
                $i = 0;

                while($i<$number_of_records){
                    $v = \Validator::make($to_validate[$i], [
                        'solicitado_para'   => 'required',
                        'viatico_por_dia'   => 'required|numeric',
                        'transporte'        => 'numeric',
                        'combustible'       => 'numeric',
                        'taxi'              => 'numeric',
                        'comunicaciones'    => 'numeric',
                        'materiales'        => 'numeric',
                        'extras'            => 'numeric',
                        'motivo'            => 'required',
                        'fecha_desde'       => 'date',
                        'fecha_hasta'       => 'date|after:fecha_desde',
                    ]);

                    if ($v->fails()) {
                        $excelIsValid = false;
                    }

                    if(!($assignment->start_date<=$to_validate[$i]['fecha_desde']&&
                        $assignment->end_date>=$to_validate[$i]['fecha_hasta'])){
                        $excelIsValid = false;
                    }

                    $i++;
                }

                if ($excelIsValid == false) {
                    Session::flash('message', "El archivo seleccionado contiene datos que no pueden ser importados. 
                            Descargue el archivo modelo para referencia!");
                    return redirect()->back()->withInput();
                }

                if (!empty($data) && $data->count()) {
                    foreach ($data as $key => $value) {

                        $employee = Employee::where(function ($query) use($value){
                            $query->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%$value->solicitado_para%");
                        })->first();

                        if ($employee) {
                            $insert[] = [
                                'user_id'           => $user->id,
                                'employee_id'       => $employee->id,
                                'assignment_id'     => $assignment->id,
                                'date_from'         => $value->fecha_desde,
                                'date_to'           => $value->fecha_hasta,
                                'in_days'           => Carbon::parse($value->fecha_desde)
                                        ->diffInDays(Carbon::parse($value->fecha_hasta)) + 1, //Extremes count
                                'per_day_amount'    => $value->viatico_por_dia,
                                'total_amount'      => $value->viatico_por_dia*(Carbon::parse($value->fecha_desde)
                                            ->diffInDays(Carbon::parse($value->fecha_hasta)) + 1),
                                'transport_amount'  => isset($value['transporte']) ? $value->transporte : '',
                                'gas_amount'        => isset($value['combustible']) ? $value->combustible : '',
                                'taxi_amount'       => isset($value['taxi']) ? $value->taxi : '',
                                'comm_amount'       => isset($value['comunicaciones']) ? $value->comunicaciones : '',
                                //'hotel_amount'      => 0,
                                'materials_amount'  => isset($value['materiales']) ? $value->materiales: '',
                                'extras_amount'     => isset($value['extras']) ? $value->extras: '',
                                'reason'            => $value->motivo,
                                'status'            => 'Pending',
                                'created_at'        => date('Y-m-d H:i:s'),
                                'updated_at'        => date('Y-m-d H:i:s')
                            ];

                        }
                    }
                    if (!empty($insert)) {

                        StipendRequest::insert($insert);

                        $empty_coded_requests = StipendRequest::where('code','')->get();

                        foreach($empty_coded_requests as $request)
                        {
                            $request->code = 'STP-'.str_pad($request->id, 4, "0", STR_PAD_LEFT).'-'.
                                date_format($request->created_at,'y');

                            $request->save();
                        }

                        $empty_additional_requests = StipendRequest::where('additional', 0)->get();

                        foreach ($empty_additional_requests as $request)
                        {
                            $request->additional = $request->transport_amount + $request->gas_amount + $request->taxi_amount +
                                $request->comm_amount + $request->hotel_amount + $request->materials_amount +
                                $request->extras_amount;

                            $request->save();
                        }

                        $message = "Las solicitudes de viáticos fueron importadas correctamente";
                    } else {
                        $message = 'No se cargó ninguna solicitud de viáticos!';
                    }

                    Session::flash('message', $message);
                    if(Session::has('url'))
                        return redirect(Session::get('url'));
                    else
                        return redirect('/stipend_request?asg='.$assignment->id);
                }
            }
        }
        /*
        elseif ($type == 'tasks-from-oc') {

            $site = Site::find($id);

            $oc_id = $request->input('oc_id');
            $oc = OC::find($oc_id);
            $file = File::where('imageable_id',$oc_id)->where('imageable_type','App\OC')->where('type','like','xls%')
                ->first();

            if($oc){
                if($file){

                    $path = $file->path.$file->name;

                    $rows = Excel::load($path, function($reader) {
                    })->skip(18)->take(43)->noHeading()->get();

                    if(!empty($rows) && $rows->count()){
                        $loadable_rows = $rows[0];
                        $prev_number = Task::select('number')->where('site_id', $id)->OrderBy('number','desc')
                            ->first();
                        
                        $to_assign_number = empty($prev_number) ? 1 : $prev_number->number+1;

                        foreach($loadable_rows as $loadable_row) {
                            if($loadable_row['3']&&$loadable_row['32']&&$loadable_row['36']&&$loadable_row['40']&&
                                is_numeric($loadable_row['32'])&&is_numeric($loadable_row['40']))
                            {

                                $item = Item::where('description', $loadable_row['3'])->orderBy('updated_at','desc')
                                    ->first();

                                $insert[] = [
                                    'user_id'           => $user->id,
                                    'site_id'           => $site->id,
                                    'item_id'           => $item ? $item->id : 0,
                                    'number'            => $to_assign_number,
                                    'name'              => $loadable_row['3'],
                                    'pondered_weight'   => 1, //Default value for weights
                                    'total_expected'    => $loadable_row['32'],
                                    'units'             => $loadable_row['36'],
                                    'status'            => 1, // Initial status
                                    'quote_price'       => $loadable_row['40'],
                                    'assigned_price'    => $loadable_row['32']*$loadable_row['40'],
                                    'start_date'        => $site->start_date,
                                    'end_date'          => $site->end_date,
                                    'created_at'        => date('Y-m-d H:i:s')];

                                $to_assign_number++;
                            }
                        }
                        if(!empty($insert)){

                            Task::insert($insert);

                            $empty_coded_tasks = Task::where('code','')->get();

                            foreach($empty_coded_tasks as $task)
                            {
                                $task->code = 'TK-'.str_pad($task->id, 4, "0", STR_PAD_LEFT).'0'.
                                    $task->number.date_format($task->created_at,'-y');
                                $task->save();
                            }

                            Session::flash('message', "Los items fueron cargados correctamente");
                            return redirect()->action('TaskController@tasks_per_site', ['id' => $id]);
                        }
                        else{
                            Session::flash('message', "La orden indicada no contiene datos importables!");
                            return redirect()->back();
                        }
                    }
                    else{
                        Session::flash('message', "La orden indicada no contiene datos importables!");
                        return redirect()->back();
                    }
                }
                else{
                    Session::flash('message', "La orden indicada no tiene almacenado un archivo importable!");
                    return redirect()->back();
                }
            }
            else{
                Session::flash('message', "El código de orden indicado no existe!");
                return redirect()->back();
            }
        }
        */
        else {
            Session::flash('message', "No se seleccionó ningún archivo o tamaño de archivo superior al permitido!");
            return redirect()->back()->withInput();
        }

        /* Last resort redirection when no match is found */
        Session::flash('message', 'No se realizó ninguna acción!');
        return redirect()->back()->withInput();
    }

    public function load_format_file($format, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        
        if (is_numeric($format)) {
            $aux = $format;
            $format = $id;
            $id = $aux;
        }

        return View::make('app.excel_fillable_form', ['id' => $id, 'format' => $format, 'service' => $service,
            'user' => $user]);
    }

    public function fill_uploaded_model(Request $request, $format, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        if (is_numeric($format)) {
            $aux = $format;
            $format = $id;
            $id = $aux;
        }

        if (!$request->hasFile('file')) {
            Session::flash('message', "No se seleccionó ningún archivo o tamaño de archivo superior al permitido!");
            return redirect()->back();
        }

        $modelFile = $request->file('file');

        $filePath = $modelFile->getRealPath();

        if ($format=='tracking-report') {
            
            $assignment = Assignment::find($id);

            if (!$assignment) {
                Session::flash('message', "No se encontró el registro solicitado!");
                return redirect()->back();
            }

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

            // $this->record_export('/oc/'.$oc->id,'OC to sign',$oc);

            // Excel::load($filePath, function($reader) use($assignment, $current_date, /*$qr_code*/)
            Excel::load($filePath, function($reader) use($assignment, $current_date)
            {
                $sheetToChange = $reader->getActiveSheet();

                $sheetToChange->setCellValue('B1', 'TRACKING REPORT F.O. '.strtoupper($assignment->name).
                    ' PROJECT MANAGER ABROS: '.($assignment->responsible ? $assignment->responsible->name : 'N/E'));

                $last_row = $sheetToChange->getHighestRow();
                $last_column = $sheetToChange->getHighestColumn();

                //$sheetToChange->setCellValue('A1', $last_row.' - '.$last_column);
                for($j=3;$j<=$last_row;$j++){
                    $item = $sheetToChange->getCell('B'.$j);

                    $count = 0;
                    $total_expected = 0;
                    $has_activities = false;

                    foreach($assignment->sites as $site){
                        if($site->tasks()->where('name', $item)->exists()){
                            $task = $site->tasks()->where('name', $item)->first();

                            $total_expected += $task->total_expected;

                            if($task->activities->count()>0){
                                $has_activities = true;
                            }

                            $count++;
                        }
                    }

                    if($count>0){
                        $sheetToChange->setCellValue('D'.$j, $total_expected);

                        if($has_activities){
                            $col = 'H';

                            while($col!=$last_column){
                                $progress = 0;

                                $cell_date = $sheetToChange->getCell($col.'2')->getFormattedValue();

                                if($cell_date)
                                    $cell_date = Carbon::createFromFormat('m-d-y', $cell_date)
                                        ->hour(0)->minute(0)->second(0);

                                foreach($assignment->sites as $site){
                                    if($site->tasks()->where('name', $item)->exists()){
                                        $task = $site->tasks()->where('name', $item)->first();

                                        foreach($task->activities as $activity){
                                            if($activity->date==$cell_date){
                                                $progress += $activity->progress;
                                            }
                                        }
                                    }
                                }

                                //if($cell_date==$current_date)
                                //    $sheetToChange->setCellValue($i.'4', 'Hoy');

                                if ($progress == 0)
                                    $progress = '';

                                $sheetToChange->setCellValue($col.$j, $progress);

                                ++$col;
                            }
                        }
                    }
                }

                /*
                $i = 'H';

                //$cell_date = $sheetToChange->getCell($i.'2');

                //$sheetToChange->setCellValue($i.'4', $cell_date);

                while($i!=$last_column){


                    $cell_date = $sheetToChange->getCell($i.'2')->getFormattedValue();

                    $sheetToChange->setCellValue($i.'55', $cell_date);

                    if($cell_date)
                        $cell_date = Carbon::createFromFormat('m-d-y', $cell_date)->hour(0)->minute(0)->second(0);

                    if($cell_date==$current_date)
                        $sheetToChange->setCellValue($i.'4', 'Hoy');

                    $sheetToChange->setCellValue($i.'56', $cell_date);
                    $sheetToChange->setCellValue($i.'57', $current_date);

                    ++$i;
                }
                */
            })->export('xlsx');
        }
        
        //Default redirection if no match is found
        return redirect()->back();
    }

    public function report_form($type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $place = 0;
        $options = collect();
        $complements = 0;

        if ($type == 'per-assignment-progress') {
            $place = Assignment::find($id);
            $place->start_date = Carbon::parse($place->start_date)->format('Y-m-d');
            $place->end_date = Carbon::parse($place->end_date)->format('Y-m-d');
        }

        return View::make('app.excel_report_form', ['id' => $id, 'type' => $type, 'place' => $place, 'service' => $service,
            'options' => $options, 'complements' => $complements ]);
    }

    public function generate_report(Request $request, $type, $id)
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        if ($request->date_to!='')
            $request->date_to = $request->date_to.' 23:59:59';

        $validate = ['date_from' => $request->date_from, 'date_to' => $request->date_to];

        $v = \Validator::make($validate, [
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after:date_from',
        ],
            [
                'required'      => 'Debe especificar el intervalo de fechas para el reporte!',
                'date'          => 'Debe introducir fechas válidas para generar el reporte!',
                'after'         => 'La fecha "Hasta" debe ser posterior o igual a la fecha "Desde"!'
            ]);

        if ($v->fails()) {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }
        
        $date_from = Carbon::parse($request->date_from);
        $date_to = Carbon::parse($request->date_to);
        
        if (abs($date_to->diffInDays($date_from)) > 31) {
            Session::flash('message', "El intervalo de fechas no puede exceder los 31 días!");
            return redirect()->back()->withInput();
        }
        
        $excel_name = 'empty';
        $sheet_name = 'empty';
        $sheet_content = collect();

        if ($type == 'per-assignment-progress') {
            Date::setLocale('es');

            $assignment = Assignment::find($id);

            if(!$assignment){
                Session::flash('message', 'Error, registro solicitado no encontrado!');
                return redirect()->back();
            }

            $excel_name = 'Resumen de avance de obras - '.$this->normalize_name($assignment->name);
            $sheet_name = $this->normalize_name($assignment->name);

            //$assignment->start_date = Carbon::parse($assignment->start_date);
            //$assignment->end_date = Carbon::parse($assignment->end_date);

            /*
            if($assignment->start_date->year<1||$assignment->end_date->year<1){
                Session::flash('message', 'La asignación debe tener registradas las fechas de inicio y fin de trabajos!');
                return redirect()->back();
            }
            */

            $items = $assignment->tasks()->select('tasks.name')->groupBy('tasks.name')->get();

            $items = $items->sortBy('name');

            while($date_from<=$date_to /*$assignment->start_date<=$assignment->end_date*/){

                $line = array();

                $line['FECHA'] = Date::parse($date_from /*$assignment->start_date*/)->format('l, j \\d\\e F \\d\\e Y');

                foreach($items as $item){
                    $var = 0;

                    $tasks = $assignment->tasks()->select('tasks.*')->where('tasks.name', $item->name)->get();

                    foreach($tasks as $task){
                        if($task->activities->count()>0){
                            $activity = $task->activities()->whereDate('date', '=', $assignment->start_date)->first();

                            if($activity)
                                $var += $activity->progress;
                        }
                    }

                    $var = $var == 0 ? '' : $var;

                    $line[$item->name] = $var;
                }

                $sheet_content[] = $line;

                $date_from->addDay(1);
                //$assignment->start_date->addDay(1);
            }

            Excel::create($excel_name, function($excel) use($sheet_name, $sheet_content, $assignment) {

                $excel->sheet($sheet_name, function($sheet) use($sheet_content, $assignment) {

                    $sheet->setWidth('A', 35);
                    $sheet->setHeight(2, 45);

                    $sheet->setCellValue('B1', $assignment->name);

                    $sheet->fromArray($sheet_content, null, 'A2', true);

                    $sheet->row(1, function($row) {
                        $row->setFontWeight('bold');
                    });

                    $sheet->row(2, function($row) {
                        $row->setFontWeight('bold');
                    });

                    $lastRow = $sheet->getHighestRow();
                    $lastColumn = $sheet->getHighestColumn();

                    $column_widths = array();

                    for($i='B';$i<=$lastColumn;$i++){
                        $column_widths[$i] = 14;
                    }

                    $sheet->setWidth($column_widths);

                    $sheet->getStyle('A3:A'.$lastRow)
                        ->getAlignment()
                        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    $sheet->setBorder('A2:'.$lastColumn.$lastRow, 'thin');
                });

            })->export('xls');

            return redirect()->action('SiteController@sites_per_project', ['id' => $id]);
        }

        /* Last resort redirection when no match is found */
        Session::flash('message', 'No se realizó ninguna acción!');
        return redirect()->back()->withInput();
    }

    public function convert_number_to_words($number)
    {
        //$hyphen      = '-';
        $conjunction = ' y ';
        //$separator   = ', ';
        $negative    = 'negativo ';
        //$decimal     = ' point ';
        $dictionary  = array(
            0                   => 'cero',
            1                   => 'un', //'uno',
            2                   => 'dos',
            3                   => 'tres',
            4                   => 'cuatro',
            5                   => 'cinco',
            6                   => 'seis',
            7                   => 'siete',
            8                   => 'ocho',
            9                   => 'nueve',
            10                  => 'diez',
            11                  => 'once',
            12                  => 'doce',
            13                  => 'trece',
            14                  => 'catorce',
            15                  => 'quince',
            16                  => 'diéciseis',
            17                  => 'diecisiete',
            18                  => 'dieciocho',
            19                  => 'diecinueve',
            20                  => 'veinte',
            21                  => 'veintiun', //'veintiuno',
            22                  => 'veintidos',
            23                  => 'veintitres',
            24                  => 'veinticuatro',
            25                  => 'veinticinco',
            26                  => 'veintiseis',
            27                  => 'veintisiete',
            28                  => 'veintiocho',
            29                  => 'veintinueve',
            30                  => 'treinta',
            40                  => 'cuarenta',
            50                  => 'cincuenta',
            60                  => 'sesenta',
            70                  => 'setenta',
            80                  => 'ochenta',
            90                  => 'noventa',
            100                 => 'cien',
            200                 => 'doscientos',
            300                 => 'trescientos',
            400                 => 'cuatrocientos',
            500                 => 'quinientos',
            600                 => 'seiscientos',
            700                 => 'setecientos',
            800                 => 'ochocientos',
            900                 => 'novecientos',
            1000                => 'mil',
            1000000             => 'millón',
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . Self::convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 31:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $conjunction . ($units==1 ? 'un' : $dictionary[$units]);
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = ($remainder==0 ? $dictionary[intval($hundreds)*100] : (intval($hundreds)==1 ? 'ciento' : 
                        $dictionary[intval($hundreds)*100])) . ' ';
                if ($remainder) {
                    $string .= Self::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = ($numBaseUnits==1 ? 'un' : Self::convert_number_to_words($numBaseUnits)).' '.
                        $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : ' '; //$separator previously used separator instead of space
                    $string .= Self::convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            //$string .= $decimal;
            $string .= ' '.str_pad($fraction, 2, "0", STR_PAD_RIGHT).'/100';
            /*
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
            */
        }

        return $string; //ucfirst($string); Upper case for every word
    }

    public function record_export($url, $description, $model)
    {
        $user = Session::get('user');

        $record = new ExportedFiles();

        $record->user_id = $user->id;
        $record->url = $url;
        $record->description = $description;

        if ($model) {
            $record->exportable()->associate($model);
        }

        $record->save();
    }

    function normalize_name($name){
        $new_name = str_replace(array('/', '\\', '*', '?', ':', '[', ']'), '-', $name);
        $new_name = str_limit($new_name, 20);

        return $new_name;
    }
}
