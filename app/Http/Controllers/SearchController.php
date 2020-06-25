<?php

namespace App\Http\Controllers;

use Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use View;
use Input;
use App\Assignment;
use App\Bill;
use App\Branch;
use App\Calibration;
use App\Cite;
use App\ClientSession;
use App\Contact;
use App\CorpLine;
use App\CorpLineAssignation;
use App\CorpLineRequirement;
use App\DeadInterval;
use App\Device;
use App\DeviceHistory;
use App\DeviceRequirement;
use App\Driver;
use App\DvcFailureReport;
use App\Email;
use App\Employee;
use App\Event;
use App\ExportedFiles;
use App\File;
use App\Guarantee;
use App\Invoice;
use App\ItemCategory;
use App\Maintenance;
use App\OC;
use App\OcCertification;
use App\Operator;
use App\Order;
use App\Project;
use App\Provider;
use App\Site;
use App\StipendRequest;
use App\Task;
use App\Tender;
use App\User;
use App\Vehicle;
use App\VehicleCondition;
use App\VehicleHistory;
use App\VehicleRequirement;
use App\VhcFailureReport;
//use App\Contract;
//use App\Material;
//use App\Warehouse;
//use App\WarehouseEntry;
//use App\WarehouseOutlet;
//use App\RbsViatic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    public function search_form($table, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = session('service');

        return View::make('app.search_form', ['user' => $user, 'service' => $service, 'table' => $table, 'id' => $id]);
    }

    public function search_results(/*Request $request,*/ $table, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = session('service');

        $form_content = Request::all();

        $parameter = $form_content['parametro']; //Request::input('parametro');
        $search_term = $form_content['buscar']; //Request::input('buscar');
        $from = $form_content['fecha_desde']; //Request::input('fecha_desde');
        $to = $form_content['fecha_hasta']; //Request::input('fecha_hasta');
        $to = $to . ' 23:59:59';

        $has_date = Request::has('fecha_desde');

        if ($table == 'assignments') {
            $columns = Schema::getColumnListing('assignments');

            if ($has_date) {
                $assignments = Assignment::whereBetween('start_date', [$from, $to])
                    ->orderBy('id')->paginate(20);
            } else {
                if ($parameter == 'all') {
                    $assignments = Assignment::join('users', 'assignments.resp_id', '=', 'users.id')
                        ->join('contacts', 'assignments.contact_id', '=', 'contacts.id')
                        ->join('projects', 'assignments.project_id', '=', 'projects.id')
                        ->select('assignments.*')
                        ->where(function($query) use($search_term, $columns) {
                            $query->where('users.name', 'like', "%$search_term%")
                                ->orwhere('contacts.name', 'like', "%$search_term%")
                                ->orwhere('projects.name', 'like', "%$search_term%");

                            $query->orwhere(function($q1) use($search_term) {
                                $q1->whereHas('sites', function ($q11) use($search_term) {
                                    $q11->where('name', 'like', "%$search_term%");
                                });
                            });

                            $query->orwhere(function($q2) use($search_term) {
                                $q2->whereHas('sites', function ($q22) use($search_term) {
                                    $q22->where('du_id', 'like', "%$search_term%");
                                });
                            });

                            $query->orwhere(function($q3) use($search_term) {
                                $q3->whereHas('sites', function ($q33) use($search_term) {
                                    $q33->where('isdp_account', 'like', "%$search_term%");
                                });
                            });
                            
                            $query->orwhere(function($q4) use($search_term) {
                                $q4->whereHas('sites', function ($q44) use($search_term) {
                                    $q44->where('code', 'like', "%$search_term%");
                                });
                            });
                            
                            foreach ($columns as $column) {
                                $query->orwhere("assignments.$column", 'like', "%$search_term%");
                            }
                        })
                        ->orderBy('assignments.id');
                } elseif ($parameter == 'resp_name') {
                    $assignments = Assignment::join('users', 'assignments.resp_id', '=', 'users.id')
                        ->select('assignments.*')
                        ->where('users.name', 'like', "%$search_term%")
                        ->orderBy('assignments.id');
                } elseif ($parameter == 'contact_name') {
                    $assignments = Assignment::join('contacts', 'assignments.contact_id', '=', 'contacts.id')
                        ->select('assignments.*')
                        ->where('contacts.name', 'like', "%$search_term%")
                        ->orderBy('assignments.id');
                } elseif ($parameter == 'project_name') {
                    $assignments = Assignment::join('projects', 'assignments.project_id', '=', 'projects.id')
                        ->select('assignments.*')
                        ->where('projects.name', 'like', "%$search_term%")
                        ->orderBy('assignments.id');
                } elseif ($parameter == 'site_name') {
                    $assignments = Assignment::whereHas('sites', function ($query) use($search_term) {
                        $query->where('name', 'like', "%$search_term%");
                    })->orderBy('id');
                } else if ($parameter == 'du_id') {
                    $assignments = Assignment::whereHas('sites', function ($query) use($search_term) {
                        $query->where('du_id', 'like', "%$search_term%");
                    })->orderBy('id');
                } else if ($parameter == 'isdp_account') {
                    $assignments = Assignment::whereHas('sites', function ($query) use($search_term) {
                        $query->where('isdp_account', 'like', "%$search_term%");
                    })->orderBy('id');
                } else if ($parameter == 'order_code') {
                    $assignments = Assignment::whereHas('sites', function ($query) use($search_term) {
                        $query->whereHas('order', function ($query2) use($search_term) {
                            $query2->where('code', 'like', "%$search_term%");
                        });
                    })->orderBy('id');
                } else {
                    $assignments = Assignment::where("$parameter", 'like', "%$search_term%")
                        ->orderBy('id');
                }
            }

            $assignments = $assignments->paginate(20);

            foreach ($assignments as $assignment) {
                $assignment->quote_from = Carbon::parse($assignment->quote_from);
                $assignment->quote_to = Carbon::parse($assignment->quote_to);
                $assignment->start_line = Carbon::parse($assignment->start_line);
                $assignment->deadline = Carbon::parse($assignment->deadline);
                $assignment->start_date = Carbon::parse($assignment->start_date);
                $assignment->end_date = Carbon::parse($assignment->end_date);
                $assignment->billing_from = Carbon::parse($assignment->billing_from);
                $assignment->billing_to = Carbon::parse($assignment->billing_to);

                foreach ($assignment->guarantees as $guarantee) {
                    $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date)
                        ->hour(0)->minute(0)->second(0);
                    $guarantee->start_date = Carbon::parse($guarantee->start_date)
                        ->hour(0)->minute(0)->second(0);
                }

                foreach ($assignment->files as $file) {
                    $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
                }
                
                /* Add general progress values for key items */
                $this->get_key_item_values($assignment);

                /* Add general progress values for key items */
                /* Obsolete
                if ($assignment->type == 'Fibra óptica') {
                    $assignment->cable_projected = $assignment->cable_executed = $assignment->cable_percentage = 0;
                    $assignment->splice_projected = 0;
                    $assignment->splice_executed = 0;
                    $assignment->splice_percentage = 0;
                    $assignment->posts_projected = 0;
                    $assignment->posts_executed = 0;
                    $assignment->posts_percentage = 0;
                    $assignment->meassures_projected = 0;
                    $assignment->meassures_executed = 0;
                    $assignment->meassures_percentage = 0;

                    foreach ($assignment->sites as $site) {
                        foreach ($site->tasks as $task) {
                            if ($task->status > 0) {
                                if ((stripos($task->name, 'tendido')!==FALSE&&stripos($task->name, 'cable')!==FALSE)||
                                    stripos($task->name, 'lineal')!==FALSE){
                                    $assignment->cable_projected += $task->total_expected;
                                    $assignment->cable_executed += $task->progress;
                                } elseif(stripos($task->name, 'empalme')!==FALSE&&stripos($task->name, 'ejecución')!==FALSE){
                                    $assignment->splice_projected += $task->total_expected;
                                    $assignment->splice_executed += $task->progress;
                                } elseif(stripos($task->name, 'poste')!==FALSE&&(stripos($task->name, 'madera')!==FALSE||
                                        stripos($task->name, 'prfv')!==FALSE||stripos($task->name, 'hormig')!==FALSE)&&
                                    stripos($task->name, 'traslado')===FALSE){
                                    $assignment->posts_projected += $task->total_expected;
                                    $assignment->posts_executed += $task->progress;
                                } elseif(stripos($task->name, 'medida')!==FALSE){
                                    $assignment->meassures_projected += $task->total_expected;
                                    $assignment->meassures_executed += $task->progress;
                                }
                            }
                        }
                    }

                    $assignment->cable_percentage = $this->get_percentage($assignment->cable_executed, $assignment->cable_projected);
                    $assignment->splice_percentage = $this->get_percentage($assignment->splice_executed, $assignment->splice_projected);
                    $assignment->posts_percentage = $this->get_percentage($assignment->posts_executed, $assignment->posts_projected);
                    $assignment->meassures_percentage = $this->get_percentage($assignment->meassures_executed,
                        $assignment->meassures_projected);
                }*/
            }

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

            return View::make('app.assignment_brief', ['assignments' => $assignments, 'service' => $service,
                'current_date' => $current_date, 'user' => $user]);
        }

        elseif ($table == 'bills') {
            $columns = Schema::getColumnListing('bills');

            if ($has_date) {
                $bills = Bill::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $bills = Bill::where(function($query) use($search_term, $columns) {
                        foreach ($columns as $column) {
                            $query->orwhere("$column", 'like', "%$search_term%");
                        }
                    });
                } else {
                    $bills = Bill::where("$parameter", 'like', "%$search_term%");
                }
            }

            $bills = $bills->orderBy('id', 'desc')->paginate(20);

            foreach ($bills as $bill) {
                $bill->date_issued = Carbon::parse($bill->date_issued)->hour(0)->minute(0)->second(0);
            }

            return View::make('app.bill_brief', ['bills' => $bills, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'branches') {
            $columns = Schema::getColumnListing('branches');

            if ($has_date) {
                $branches = Branch::whereBetween('created_at', [$from, $to])->orderBy('name');
            } else {
                if ($parameter == 'all') {
                    $branches = Branch::leftJoin('employees', 'branches.head_id', '=', 'employees.id')
                        ->select('branches.*')
                        ->where(function($query) use($search_term, $columns) {
                            $query->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%$search_term%");
                            
                            foreach ($columns as $column) {
                                $query->orwhere("branches.$column", 'like', "%$search_term%");
                            }
                        })
                        ->orderBy('branches.name');
                } elseif ($parameter == 'head_name') {
                    $branches = Branch::join('employees', 'branches.head_id', '=', 'employees.id')
                        ->select('branches.*')
                        ->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%$search_term%")
                        ->orderBy('branches.name');
                } else {
                    $branches = Branch::where("$parameter", 'like', "%$search_term%")->orderBy('name');
                }
            }

            $branches = $branches->paginate(20);

            return View::make('app.branch_brief', ['branches' => $branches, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'calibrations') {
            $columns = Schema::getColumnListing('calibrations');
            $columns2 = Schema::getColumnListing('devices');

            if ($has_date) {
                $calibrations = Calibration::whereBetween('date_in', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $calibrations = Calibration::join('devices','calibrations.device_id','=','devices.id')
                        ->select('calibrations.*')
                        ->where(function($query) use($search_term, $columns, $columns2) {
                            foreach ($columns2 as $col) {
                                $query->orwhere("devices.$col",'like',"%$search_term%");
                            }
                            
                            if (similar_text($search_term, 'En calibración'))
                                $query->orwhere('calibrations.completed', 0);
                            elseif (similar_text($search_term, 'Finalizado'))
                                $query->orwhere('calibrations.completed', 1);
                            
                            foreach ($columns as $column) {
                                $query->orwhere("calibrations.$column", 'like', "%$search_term%");
                            }
                        })
                        ->orderBy('calibrations.created_at', 'desc');
                } elseif ($parameter == 'type' || $parameter == 'model' || $parameter == 'serial') {
                    $calibrations = Calibration::join('devices','calibrations.device_id','=','devices.id')
                        ->select('calibrations.*')
                        ->where("devices.$parameter",'like',"%$search_term%")
                        ->orderBy('calibrations.created_at', 'desc');
                } elseif ($parameter == 'completed') {
                    if (similar_text($search_term, 'En calibración'))
                        $calibrations = Calibration::where('completed', 0);
                    elseif (similar_text($search_term, 'Finalizado'))
                        $calibrations = Calibration::where('completed', 1);
                    else
                        $calibrations = Calibration::where('completed', '>', 1);
                } else {
                    $calibrations = Calibration::where("$parameter", 'like', "%$search_term%");
                }
            }

            $calibrations = $calibrations->paginate(20);

            return View::make('app.calibration_brief', ['calibrations' => $calibrations,
                'service' => $service, 'user' => $user ]);
        }

        elseif ($table == 'cites') {
            $columns = Schema::getColumnListing('cites');

            if (($user->area == 'Gerencia General' && $user->priv_level == 3) || $user->priv_level == 4) {
                if ($has_date) {
                    $cites = Cite::whereBetween('created_at', [$from, $to]);
                        //->where('num_cite', '>', '0')
                } else {
                    if ($parameter == 'all') {
                        $cites = Cite::query();
    
                        foreach ($columns as $column) {
                            $cites->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                        }
                    } else {
                        /*
                        if ($parameter == 'codigo_cite') {
                            $v = \Validator::make(Request::all(), [
                                'buscar' => 'min:14|max:16|alpha_dash',
                            ]);

                            if ($v->fails()) {
                                Session::flash('message', 'Introduzca un codigo de CITE válido!');
                                return redirect()->back();
                            }

                            $array_buscar = explode('-', $buscar);
                            $area_cite = $array_buscar[0] . '-' . $array_buscar[1];
                            $cites = Cite::where('num_cite', $array_buscar[2])->whereYear('created_at','=',$array_buscar[3])
                                ->where('title', $area_cite)
                                ->paginate(20);
                        }
                        */
                        $cites = Cite::where("$parameter", 'like', "%$search_term%");
                    }
                }
            } else {
                if ($has_date) {
                    $cites = Cite::where('area', $user->area)->whereBetween('created_at', [$from, $to]);
                } else {
                    if ($parameter == 'all') {
                        $cites = Cite::where('area', $user->area)
                            ->where(function($query) use($search_term, $columns) {
                                foreach ($columns as $column) {
                                    $query->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                                }
                            });
                    } else {
                        $cites = Cite::where('area', $user->area)->where("$parameter", 'like', "%$search_term%");
                    }
                }
            }

            $cites = $cites->orderBy('created_at', 'desc')->paginate(20);

            return View::make('app.cite_brief', ['cites' => $cites, 'service' => $service, 'user' => $user]);
        }
        
        elseif ($table === 'client_sessions') {
            $columns = Schema::getColumnListing('client_sessions');

            if ($has_date) {
                $sessions = ClientSession::whereBetween('created_at', [$from, $to])->orderBy('created_at');
            } else {
                if ($parameter == 'all') {
                    $sessions = ClientSession::query();

                    foreach ($columns as $column) {
                        $sessions->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $sessions = ClientSession::where("$parameter", 'like', "%$search_term%")->orderBy('created_at');
                }
            }

            $sessions = $sessions->paginate(20);

            return View::make('app.client_session_brief', ['records' => $sessions, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'contacts') {
            $columns = Schema::getColumnListing('contacts');

            if ($has_date) {
                $contacts = Contact::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $contacts = Contact::query();

                    foreach ($columns as $column) {
                        $contacts->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $contacts = Contact::where("$parameter", 'like', "%$search_term%");
                }
            }

            $contacts = $contacts->orderBy('name')->paginate(20);

            return View::make('app.contact_brief', ['contacts' => $contacts, 'service' => $service, 'user' => $user]);
        }

        /* Contract merged with Projects (all contract functions moved or merged with projects' functions)
        elseif ($table == 'contracts') {

            if ($has_date)
                $contracts = Contract::whereBetween('created_at', [$from, $to])->paginate(20);
            else
                $contracts = Contract::where("$parameter", 'like', "%$search_term%")->paginate(20);

            foreach($contracts as $contract){
                $contract->expiration_date = Carbon::parse($contract->expiration_date);
            }

            return View::make('app.contract_brief', ['contracts' => $contracts, 'service' => $service,
                'user' => $user]);
        }
        */

        elseif ($table=='corp_line_assignations') {
            $columns = Schema::getColumnListing('corp_line_assignations');

            if ($has_date) {
                $assignations = CorpLineAssignation::whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at', 'desc');
            } elseif ($parameter == 'all') {
                $assignations = CorpLineAssignation::join('corp_lines','corp_line_assignations.corp_line_id','=','corp_lines.id')
                    ->join('users', function ($join) {
                        $join->on('users.id', '=', 'corp_line_assignations.resp_before_id')->orOn('users.id', '=', 'corp_line_assignations.resp_after_id');
                    })
                    ->select('corp_line_assignations.*')
                    ->where(function($query) use($search_term, $columns) {
                        $query->where('corp_lines.number', 'like', "%$search_term%")
                            ->orwhere('users.name', 'like', "%$search_term%");
                        
                        foreach ($columns as $column) {
                            $query->orWhere("corp_line_assignations.$column", 'LIKE', '%' . $search_term . '%');
                        }
                    })
                    ->orderBy('corp_line_assignations.created_at', 'desc');
            } elseif ($parameter == 'line_number') {
                $assignations = CorpLineAssignation::join('corp_lines','corp_line_assignations.corp_line_id','=','corp_lines.id')
                    ->select('corp_line_assignations.*')
                    ->where('corp_lines.number', 'like', "%$search_term%")
                    ->orderBy('corp_line_assignations.created_at', 'desc');
            } elseif ($parameter == 'resp_before_name') {
                $assignations = CorpLineAssignation::join('users','corp_line_assignations.resp_before_id','=','users.id')
                    ->select('corp_line_assignations.*')
                    ->where('users.name', 'like', "%$search_term%")
                    ->orderBy('corp_line_assignations.created_at', 'desc');
            } elseif($parameter == 'resp_after_name') {
                $assignations = CorpLineAssignation::join('users','corp_line_assignations.resp_after_id','=','users.id')
                    ->select('corp_line_assignations.*')
                    ->where('users.name', 'like', "%$search_term%")
                    ->orderBy('corp_line_assignations.created_at', 'desc');
            } else {
                $assignations = CorpLineAssignation::where("$parameter", 'like', "%$search_term%")
                    ->orderBy('created_at', 'desc');
            }

            $assignations = $assignations->paginate(20);

            return View::make('app.line_assignation_brief', ['assignations' => $assignations, 'service' => $service,
                'user' => $user]);
        }

        elseif ($table == 'corp_line_requirements') {
            $columns = Schema::getColumnListing('corp_line_requirements');

            if ($has_date) {
                $requirements = CorpLineRequirement::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $requirements = CorpLineRequirement::join('users', function ($join) {
                            $join->on('users.id', '=', 'corp_line_requirements.user_id')->orOn('users.id', '=', 'corp_line_requirements.for_id');
                        })
                        ->select('corp_line_requirements.*')
                        ->where(function($query) use($search_term, $columns) {
                            $query->where('users.name', 'like', "%$search_term%");
    
                            foreach ($columns as $column) {
                                $query->orWhere("corp_line_requirements.$column", 'LIKE', '%' . $search_term . '%');
                            }
                        });
                } elseif ($parameter == 'user_name') {
                    $requirements = CorpLineRequirement::join('users','corp_line_requirements.user_id','=','users.id')
                        ->select('corp_line_requirements.*')
                        ->where('users.name','like',"%$search_term%");
                } elseif($parameter == 'person_for') {
                    $requirements = CorpLineRequirement::join('users','corp_line_requirements.for_id','=','users.id')
                        ->select('corp_line_requirements.*')
                        ->where('users.name','like',"%$search_term%");
                } else {
                    $requirements = CorpLineRequirement::where("$parameter", 'like', "%$search_term%");
                }
            }

            if ($user->priv_level < 2) {
                $requirements = $requirements->where(function ($query) use($user) {
                    $query->where('for_id', $user->id)->orwhere('user_id', '=', $user->id);
                });
            }

            $requirements = $requirements->orderBy('updated_at','desc')->paginate(20);

            return View::make('app.corp_line_requirement_brief', ['requirements' => $requirements, 'service' => $service,
                'user' => $user]);
        }

        elseif ($table == 'corp_lines') {
            $columns = Schema::getColumnListing('corp_lines');

            if ($has_date) {
                $lines = CorpLine::whereBetween('created_at', [$from, $to])
                    ->orderBy('number');
            } elseif ($parameter == 'all') {
                $lines = CorpLine::join('users','corp_lines.responsible_id','=','users.id')
                    ->select('corp_lines.*')
                    ->where(function($query) use($search_term, $columns) {
                        $query->where('users.name', 'like', "%$search_term%");

                        foreach ($columns as $column) {
                            $query->orWhere("corp_lines.$column", 'LIKE', '%' . $search_term . '%');
                        }
                    })
                    ->orderBy('corp_lines.number');
            } elseif ($parameter == 'responsible_name') {
                $lines = CorpLine::join('users','corp_lines.responsible_id','=','users.id')
                    ->select('corp_lines.*')
                    ->where('users.name', 'like', "%$search_term%")
                    ->orderBy('corp_lines.number');
            } else {
                $lines = CorpLine::where("$parameter", 'like', "%$search_term%")
                    ->orderBy('number');
            }

            $lines = $lines->paginate(20);

            return View::make('app.corp_line_brief', ['lines' => $lines, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'dead_intervals_assig') {
            $columns = Schema::getColumnListing('dead_intervals');

            $assignment = Assignment::find($id);
            $site = 0;

            if ($has_date) {
                $dead_intervals = DeadInterval::whereBetween('created_at', [$from, $to])
                    ->where('relatable_id',$assignment->id)
                    ->where('relatable_type','App\Assignment');
            } else {
                if ($parameter == 'all') {
                    $dead_intervals = DeadInterval::where('relatable_id',$assignment->id)
                        ->where('relatable_type','App\Assignment')
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                            }
                        });
                } else {
                    $dead_intervals = DeadInterval::where("$parameter", 'like', "%$search_term%")
                        ->where('relatable_id',$assignment->id)
                        ->where('relatable_type','App\Assignment');
                }
            }

            $dead_intervals = $dead_intervals->paginate(20);

            foreach ($dead_intervals as $dead_interval) {
                $dead_interval->date_from = Carbon::parse($dead_interval->date_from);
                $dead_interval->date_to = Carbon::parse($dead_interval->date_to);
            }

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

            return View::make('app.dead_interval_brief', ['dead_intervals' => $dead_intervals, 'service' => $service,
                'current_date' => $current_date, 'user' => $user, 'assignment' => $assignment, 'site' => $site]);
        }

        elseif ($table == 'dead_intervals_st') {
            $columns = Schema::getColumnListing('dead_intervals');

            $site = Site::find($id);
            $assignment = 0;

            if ($has_date) {
                $dead_intervals = DeadInterval::whereBetween('created_at', [$from, $to])
                    ->where('relatable_id',$site->id)
                    ->where('relatable_type','App\Site');
            } else {
                if ($parameter == 'all') {
                    $dead_intervals = DeadInterval::where('relatable_id',$site->id)
                        ->where('relatable_type','App\Site')
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                            }
                        });
                } else {
                    $dead_intervals = DeadInterval::where("$parameter", 'like', "%$search_term%")
                        ->where('relatable_id',$site->id)
                        ->where('relatable_type','App\Site');
                }
            }

            $dead_intervals = $dead_intervals->paginate(20);

            foreach ($dead_intervals as $dead_interval) {
                $dead_interval->date_from = Carbon::parse($dead_interval->date_from);
                $dead_interval->date_to = Carbon::parse($dead_interval->date_to);
            }

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

            return View::make('app.dead_interval_brief', ['dead_intervals' => $dead_intervals, 'service' => $service,
                'current_date' => $current_date, 'user' => $user, 'assignment' => $assignment, 'site' => $site]);
        }

        elseif ($table == 'device_histories') {
            $columns = Schema::getColumnListing('device_histories');

            $device = Device::find($id);

            if ($has_date) {
                $device_histories = DeviceHistory::whereBetween('created_at', [$from, $to])
                    ->where('device_id',$device->id);
            } else {
                if ($parameter == 'all') {
                    $device_histories = DeviceHistory::where('device_id',$device->id)
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                            }
                        });
                } else {
                    $device_histories = DeviceHistory::where("$parameter", 'like', "%$search_term%")
                        ->where('device_id',$device->id);
                }
            }

            $device_histories = $device_histories->paginate(20);

            return View::make('app.device_history', ['device_histories' => $device_histories, 'device' => $device,
                'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'device_requirements') {
            $columns = Schema::getColumnListing('device_requirements');

            if ($has_date) {
                $requirements = DeviceRequirement::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $requirements = DeviceRequirement::join('devices','device_requirements.device_id','=','devices.id')
                        ->join('users', function ($join) {
                            $join->on('users.id', '=', 'device_requirements.from_id')->orOn('users.id', '=', 'device_requirements.for_id');
                        })
                        ->select('device_requirements.*')
                        ->where(function($query) use($search_term, $columns) {
                            $query->where('users.name', 'like', "%$search_term%")
                                ->orwhere("devices.serial",'like',"%$search_term%")
                                ->orwhere("devices.model",'like',"%$search_term%");
    
                            foreach ($columns as $column) {
                                $query->orWhere("device_requirements.$column", 'LIKE', '%' . $search_term . '%');
                            }
                        });
                } elseif ($parameter == 'person_from') {
                    $requirements = DeviceRequirement::join('users','device_requirements.from_id','=','users.id')
                        ->select('device_requirements.*')
                        ->where('users.name','like',"%$search_term%");
                } elseif ($parameter == 'person_for') {
                    $requirements = DeviceRequirement::join('users','device_requirements.for_id','=','users.id')
                        ->select('device_requirements.*')
                        ->where('users.name','like',"%$search_term%");
                } elseif ($parameter == 'serial') {
                    $requirements = DeviceRequirement::join('devices','device_requirements.device_id','=','devices.id')
                        ->select('device_requirements.*')
                        ->where("devices.serial",'like',"%$search_term%");
                } elseif ($parameter == 'model') {
                    $requirements = DeviceRequirement::join('devices','device_requirements.device_id','=','devices.id')
                        ->select('device_requirements.*')
                        ->where("devices.model",'like',"%$search_term%");
                } else {
                    $requirements = DeviceRequirement::where("$parameter", 'like', "%$search_term%");
                }
            }

            $dvc = Input::get('dvc');

            if (!is_null($dvc))
                $requirements = $requirements->where('device_id', $dvc);

            if (!(($user->priv_level >= 2 && $user->area == 'Gerencia Tecnica') || $user->priv_level >= 3 || $user->work_type == 'Almacén')) {
                $requirements = $requirements->where(function ($query) use($user) {
                    $query->where('for_id', $user->id)
                        ->orwhere('from_id', '=', $user->id);
                });
            }

            $requirements = $requirements->orderBy('updated_at','desc')->paginate(20);

            return View::make('app.device_requirement_brief', ['requirements' => $requirements, 'service' => $service,
                'user' => $user, 'dvc' => $dvc]);
        }

        elseif ($table == 'devices') {
            $columns = Schema::getColumnListing('devices');

            if ($has_date) {
                $devices = Device::whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at', 'desc');
            } elseif ($parameter == 'all') {
                $devices = Device::join('users', 'devices.responsible','=','users.id')
                    ->select('devices.*')
                    ->where(function($query) use($search_term, $columns) {
                        $query->where('users.name', 'like', "%$search_term%");

                        foreach ($columns as $column) {
                            $query->orWhere("devices.$column", 'LIKE', '%' . $search_term . '%');
                        }
                    })
                    ->orderBy('devices.created_at', 'desc');
            } elseif ($parameter == 'responsible_name') {
                $devices = Device::join('users', 'devices.responsible','=','users.id')
                    ->select('devices.*')
                    ->where('users.name','like',"%$search_term%")
                    ->orderBy('devices.created_at', 'desc');
            } else {
                $devices = Device::where("$parameter", 'like', "%$search_term%")
                    ->orderBy('created_at', 'desc');
            }

            Session::put('db_query', $devices->get());
            $devices = $devices->paginate(20);

            foreach ($devices as $device) {
                if ($device->last_operator)
                    $device->last_operator->date = Carbon::parse($device->last_operator->date);
            }

            return View::make('app.device_brief', ['devices' => $devices, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'drivers') {
            $columns = Schema::getColumnListing('drivers');
            $columns2 = Schema::getColumnListing('vehicles');

            if ($has_date) {
                $drivers = Driver::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $drivers = Driver::join('vehicles','drivers.vehicle_id','=','vehicles.id')
                        ->join('users', function ($join) {
                            $join->on('users.id', '=', 'drivers.who_receives')->orOn('users.id', '=', 'drivers.who_delivers');
                        })
                        ->select('drivers.*')
                        ->where(function($query) use($search_term, $columns, $columns2) {
                            $query->where('users.name', 'like', "%$search_term%");
    
                            foreach ($columns as $column) {
                                $query->orWhere("drivers.$column", 'LIKE', '%' . $search_term . '%');
                            }

                            foreach ($columns2 as $col) {
                                $query->orwhere("vehicles.$col", 'like', "%$search_term%");
                            }
                        });
                } elseif ($parameter == 'who_receives') {
                    $drivers = Driver::join('users','drivers.who_receives','=','users.id')
                        ->select('drivers.*')
                        ->where('users.name','like',"%$search_term%");
                } elseif ($parameter == 'who_delivers') {
                    $drivers = Driver::join('users','drivers.who_delivers','=','users.id')
                        ->select('drivers.*')
                        ->where('users.name','like',"%$search_term%");
                } else {
                    $drivers = Driver::join('vehicles','drivers.vehicle_id','=','vehicles.id')
                        ->select('drivers.*')
                        ->where("vehicles.$parameter",'like',"%$search_term%");
                }
            }

            if ($user->priv_level >= 2) {
                $drivers = $drivers->orderBy('created_at', 'desc');
            } else {
                $drivers = $drivers->where(function ($query) use($user) {
                    $query->where('drivers.user_id', $user->id)
                        ->orwhere('who_receives','=',$user->id)
                        ->orwhere('who_delivers','=',$user->id);})
                        ->orderBy('created_at', 'desc');
            }

            $drivers = $drivers->paginate(20);

            return View::make('app.driver_brief', ['drivers' => $drivers, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'dvc_failure_reports') {
            $columns = Schema::getColumnListing('dvc_failure_reports');
            $device = Device::find($id);

            if (!$device) {
                Session::flash('message', 'Sucedió un error al recuperar la información del servidor, revise la dirección
                 e intente de nuevo por favor');
                return redirect()->back();
            }

            if ($has_date) {
                $reports = DvcFailureReport::whereBetween('created_at', [$from, $to])
                    ->where('device_id', $id)
                    ->orderBy('created_at', 'desc');
            } elseif ($parameter == 'all') {
                $reports = DvcFailureReport::join('users', 'dvc_failure_reports.user_id', '=', 'users.id')
                    ->select('dvc_failure_reports.*')
                    ->where('dvc_failure_reports.device_id', $id)
                    ->where(function($query) use($search_term, $columns) {
                        $query->where('users.name', 'like', "%$search_term%");

                        foreach ($columns as $column) {
                            $query->orWhere("dvc_failure_reports.$column", 'LIKE', '%' . $search_term . '%');
                        }
                    })
                    ->orderBy('dvc_failure_reports.created_at', 'desc');
            } elseif ($parameter == 'user_name') {
                $reports = DvcFailureReport::join('users', 'dvc_failure_reports.user_id', '=', 'users.id')
                    ->select('dvc_failure_reports.*')
                    ->where('users.name', 'like', "%$search_term%")
                    ->where('dvc_failure_reports.device_id', $id)
                    ->orderBy('dvc_failure_reports.created_at', 'desc');
            } else {
                $reports = DvcFailureReport::where("$parameter", 'like', "%$search_term%")
                    ->where('device_id', $id)
                    ->orderBy('created_at', 'desc');
            }

            $reports = $reports->paginate(20);

            return View::make('app.device_failure_report_brief', ['reports' => $reports, 'service' => $service,
                'user' => $user, 'device' => $device]);
        }

        elseif ($table == 'emails') {
            $columns = Schema::getColumnListing('emails');

            if ($has_date) {
                $emails = Email::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $emails = Email::query();
                    
                    foreach ($columns as $column) {
                        $emails->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $emails = Email::where("$parameter", 'like', "%$search_term%");
                }
            }

            $emails = $emails->orderBy('created_at','desc')->paginate(20);

            return View::make('app.email_brief', ['emails' => $emails, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'employees') {
            $columns = Schema::getColumnListing('employees');

            if ($has_date) {
                $employees = Employee::whereBetween('created_at', [$from, $to])
                    ->orderBy('last_name', 'asc');
            } else {
                if ($parameter == 'all') {
                    $employees = Employee::query();
                    
                    foreach ($columns as $column) {
                        $employees->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }

                    $employees->orderBy('last_name', 'asc');
                } else {
                    $employees = Employee::where("$parameter", 'like', "%$search_term%")
                        ->orderBy('last_name', 'asc');
                }
            }

            $employees = $employees->paginate(20);

            return View::make('app.employee_brief', ['employees' => $employees, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'employee_account_info') {
            //$columns = Schema::getColumnListing('employees');
            $employee_record = Employee::find($id);

            if ($has_date) {
                $stipend_requests = $employee_record->stipend_requests()
                    ->whereBetween('created_at', [$from, $to])
                    ->whereNotIn('status', ['Observed', 'Rejected']);
            } else {
                if ($parameter == 'all') {
                    $stipend_requests = $employee_record->stipend_requests()->join('assignments', 'stipend_requests.assignment_id', '=', 'assignments.id')
                        ->select('stipend_requests.*')
                        ->where(function($query) use($search_term) {
                            $query->where('assignments.cost_center', 'Like', '%'.$search_term.'%')
                                ->orwhere('stipend_requests.code', 'Like', '%'.$search_term.'%');
                        })
                        ->whereNotIn('stipend_requests.status', ['Observed', 'Rejected']);
                } elseif ($parameter == 'code') {
                    $stipend_requests = $employee_record->stipend_requests()
                        ->where("code", 'like', "%$search_term%")
                        ->whereNotIn('status', ['Observed', 'Rejected']);
                } elseif ($parameter == 'cc') {
                    $stipend_requests = $employee_record->stipend_requests()->join('assignments', 'stipend_requests.assignment_id', '=', 'assignments.id')
                        ->select('stipend_requests.*')
                        ->where('assignments.cost_center', 'Like', '%'.$search_term.'%')
                        ->whereNotIn('stipend_requests.status', ['Observed', 'Rejected']);
                }  else {
                    $stipend_requests = $employee_record->stipend_requests()
                        ->whereNotIn('status', ['Observed', 'Rejected']);
                }
            }

            $stipend_requests = $stipend_requests->orderBy('created_at', 'desc')->paginate(20);

            $total_solicitudes = 0;
            $total_rendiciones = 0;
            $saldo_global_abros = 0;
            $saldo_global_empleado = 0;

            foreach ($stipend_requests as $request) {
                $request->date_from = Carbon::parse($request->date_from);
                $request->date_to = Carbon::parse($request->date_to);

                if ($request->status == 'Completed' || $request->status == 'Documented') {
                    $total_solicitudes += $request->total_amount + $request->additional;
                }
                
                if ($request->rendicion_viatico) {
                    $total_rendiciones += $request->rendicion_viatico->total_rendicion;
                }
            }

            $saldo_global_abros = $total_solicitudes - $total_rendiciones;
            $saldo_global_empleado = $total_rendiciones - $total_solicitudes;

            return View::make('app.employee_account_info', ['stipend_requests' => $stipend_requests, 'service' => $service, 'user' => $user, 
                'employee_record' => $employee_record, 'total_solicitudes' => $total_solicitudes, 'total_rendiciones' => $total_rendiciones, 
                'saldo_global_abros' => $saldo_global_abros, 'saldo_global_empleado' => $saldo_global_empleado]);
        }

        elseif ($table == 'events') {
            $columns = Schema::getColumnListing('events');

            $aux = explode('-', $id);
            //$type_info = $aux[0] == 'site' ? Site::find($aux[1]) : collect();

            $type_info = collect();
            $open = true;
            $model = '';
        
            if ($aux[0] == 'site') {
                $type_info = Site::find($aux[1]);
                $open = $type_info && ($type_info->status != $type_info->last_stat() && $type_info->status != 0) ? true : false;
            } elseif ($aux[0] == 'assignment') {
                $type_info = Assignment::find($aux[1]);
                $open = $type_info && ($type_info->status != $type_info->last_stat() && $type_info->status != 0) ? true : false;
            } elseif ($aux[0] == 'task') {
                $type_info = Task::find($aux[1]);
                $open = $type_info && ($type_info->status != $type_info->last_stat() && $type_info->status != 0) ? true : false;
            } elseif ($aux[0] == 'oc') {
                $type_info = OC::find($aux[1]);
            } elseif ($aux[0] == 'invoice') {
                $type_info = Invoice::find($aux[1]);
            } elseif ($aux[0] == 'rendicion_viatico') {
                $model = 'RendicionViatico';
                $type_info == RendicionViatico::find($aux[1]);
            }

            if (!$type_info) {
                Session::flash('message', "No se encontró la página solicitada, revise la dirección e intente de nuevo por favor");
                return redirect()->back();
            }

            $type = $aux[0];
            $id = $aux[1];

            if ($has_date) {
                $events = Event::whereBetween('date', [$from, $to])
                    ->where('eventable_id',$aux[1])
                    ->where(function ($query) use($type, $model) {
                        $query->where('eventable_type','like',"%$type%")
                            ->orwhere('eventable_type','like',"%$model%");
                    })
                    ->orderBy('number');
            } else {
                if ($parameter == 'all') {
                    $events = Event::join('users', 'events.responsible_id', '=', 'users.id')
                        ->select('events.*')
                        ->where('eventable_id',$aux[1])
                        ->where(function ($q) use($type, $model) {
                            $q->where('eventable_type','like',"%$type%")
                                ->orwhere('eventable_type','like',"%$model%");
                        })
                        ->where(function($query) use($search_term, $columns) {
                            $query->where('users.name', 'like', "%$search_term%");

                            foreach ($columns as $column) {
                                $query->orWhere("events.$column", 'LIKE', '%' . $search_term . '%');
                            }
                        })
                        ->orderBy('events.number');
                } elseif ($parameter == 'responsible_name') {
                    $events = Event::join('users', 'events.responsible_id', '=', 'users.id')
                        ->select('events.*')
                        ->where('users.name', 'like', "%$search_term%")
                        ->where('eventable_id',$aux[1])
                        ->where(function ($q) use($type, $model) {
                            $q->where('eventable_type','like',"%$type%")
                                ->orwhere('eventable_type','like',"%$model%");
                        })
                        ->orderBy('events.number');
                } else {
                    $events = Event::where("$parameter", 'like', "%$search_term%")
                        ->where('eventable_id',$aux[1])
                        ->where(function ($q) use($type, $model) {
                            $q->where('eventable_type','like',"%$type%")
                                ->orwhere('eventable_type','like',"%$model%");
                        })
                        ->orderBy('number');
                }
            }

            $events = $events->paginate(20);

            foreach ($events as $event) {
                $event->date = Carbon::parse($event->date);
            }

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

            return View::make('app.event_brief', ['type_info' => $type_info, 'events' => $events, 'type' => $aux[0],
                'open' => $open, 'id' => $aux[1], 'service' => $service, 'current_date' => $current_date, 'user' => $user]);
        }

        elseif ($table == 'exported_files') {
            $columns = Schema::getColumnListing('exported_files');

            if ($has_date) {
                $records = ExportedFiles::whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at', 'desc');
            } elseif ($parameter == 'all') {
                $records = ExportedFiles::join('users', 'exported_files.user_id', '=', 'users.id')
                        ->select('exported_files.*')
                        ->where('users.name','like',"%$search_term%");
                    
                foreach ($columns as $column) {
                    $records->orWhere("exported_files.$column", 'LIKE', '%' . $search_term . '%');
                }
            } elseif ($parameter == 'name') {
                $records = ExportedFiles::join('users', 'exported_files.user_id', '=', 'users.id')
                    ->select('exported_files.*')
                    ->where('users.name','like',"%$search_term%");
            } else {
                $records = ExportedFiles::where("$parameter", 'like', "%$search_term%")
                    ->orderBy('created_at', 'desc');
            }

            $records = $records->paginate(20);

            return View::make('app.exported_files_brief', ['records' => $records, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'files') {
            $columns = Schema::getColumnListing('files');

            if ($has_date) {
                $files = File::whereBetween('created_at', [$from, $to]);
            } elseif ($parameter == 'all') {
                $files = File::join('users','files.user_id', '=', 'users.id')
                        ->select('files.*')
                        ->where('users.name','like',"%$search_term%");
                    
                foreach ($columns as $column) {
                    $files->orWhere("files.$column", 'LIKE', '%' . $search_term . '%');
                }
            } elseif ($parameter == 'user_name') {
                $uploaded_by = User::where('name', 'like', "%$search_term%")->first();

                if ($uploaded_by)
                    $files = File::where('user_id', $uploaded_by->id);
                else
                    $files = File::where('user_id', 0);
            } else {
                $files = File::where("$parameter", 'like', "%$search_term%");
            }

            $files = $files->orderBy('created_at','desc')->paginate(20);

            return View::make('app.file_brief', ['files' => $files, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'guarantees') {
            $columns = Schema::getColumnListing('guarantees');

            if ($has_date) {
                $guarantees = Guarantee::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $guarantees = Guarantee::query();
                    
                    foreach ($columns as $column) {
                        $guarantees->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    /*
                    elseif($parameter=='assignment_name'){
                        $guarantees = Guarantee::join('assignments', 'guarantees.assignment_id','=','assignments.id')
                            ->select('guarantees.*')
                            ->where('assignments.name','like',"%$search_term%")
                            ->paginate(20);
                    }
                    */
                    
                    $guarantees = Guarantee::where("$parameter", 'like', "%$search_term%");
                }
            }

            $guarantees = $guarantees->paginate(20);

            foreach ($guarantees as $guarantee) {
                $guarantee->start_date = Carbon::parse($guarantee->start_date);
                $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date);
            }

            return View::make('app.guarantee_brief', ['guarantees' => $guarantees, 'service' => $service,
                'user' => $user]);
        }

        elseif ($table == 'invoices') {
            $columns = Schema::getColumnListing('invoices');

            if ($user->priv_level >= 3) {
                if ($has_date) {
                    $invoices = Invoice::whereBetween('created_at', [$from, $to])
                        ->orderBy('id', 'desc');
                } else {
                    if ($parameter == 'all') {
                        $invoices = Invoice::join('o_c_s','invoices.oc_id','=','o_c_s.id')->select('invoices.*')
                            ->where('o_c_s.code','like',"%$search_term%")
                            ->orwhere('o_c_s.provider','like',"%$search_term%");
                        
                        foreach ($columns as $column) {
                            $invoices->orWhere("invoices.$column", 'LIKE', '%' . $search_term . '%');
                        }
                        $invoices->orderBy('invoices.id', 'desc');
                    } elseif ($parameter == 'oc_code') {
                        /* Validation not required after adding code column to OCs
                        $v = \Validator::make(Request::all(), [
                            'buscar' => 'required|regex:[^(OC)-(\d{5})$]',
                        ]);

                        if ($v->fails()) {
                            Session::flash('message', 'Introduzca un codigo de OC válido!');
                            return redirect()->back();
                        }

                        $exploded_code = explode('-', Request::input('buscar'));

                        $invoices = Invoice::where('oc_id', $exploded_code[1])->orderBy('id', 'desc')->paginate(20);
                        */

                        $invoices = Invoice::join('o_c_s','invoices.oc_id','=','o_c_s.id')->select('invoices.*')
                            ->where('o_c_s.code','like',"%$search_term%")
                            ->orderBy('invoices.id', 'desc');
                    } elseif ($parameter == 'provider') {
                        $invoices = Invoice::join('o_c_s','invoices.oc_id','=','o_c_s.id')->select('invoices.*')
                            ->where('o_c_s.provider','like',"%$search_term%")
                            ->orderBy('invoices.id', 'desc');
                    } else {
                        $invoices = Invoice::where("$parameter", 'like', "%$search_term%")
                            ->orderBy('id', 'desc');
                    }
                }
            } else {
                if ($has_date) {
                    $invoices = Invoice::whereBetween('created_at', [$from, $to])
                        ->where('user_id', $user->id)
                        ->orderBy('id', 'desc');
                } else {
                    if ($parameter == 'all') {
                        $invoices = Invoice::join('o_c_s','invoices.oc_id','=','o_c_s.id')->select('invoices.*')
                            ->where('invoices.user_id', $user->id)
                            ->where(function($query) use($search_term, $columns) {
                                $query->where('o_c_s.code','like',"%$search_term%")
                                    ->orwhere('o_c_s.provider','like',"%$search_term%");
                            
                                foreach ($columns as $column) {
                                    $query->orWhere("invoices.$column", 'LIKE', '%' . $search_term . '%');
                                }
                            });
                        
                        $invoices->orderBy('invoices.id', 'desc');
                    } elseif ($parameter == 'oc_code') {
                        /* Validation not required after adding code column to OCs
                        $v = \Validator::make(Request::all(), [
                            'buscar' => 'required|regex:[^(OC)-(\d{5})$]',
                        ]);

                        if ($v->fails()) {
                            Session::flash('message', 'Introduzca un codigo de OC válido!');
                            return redirect()->back();
                        }

                        $exploded_code = explode('-', Request::input('buscar'));

                        $invoices = Invoice::where('oc_id', $exploded_code[1])->where('user_id', $user->id)
                            ->orderBy('id', 'desc')->paginate(20);
                        */

                        $invoices = Invoice::join('o_c_s','invoices.oc_id','=','o_c_s.id')->select('invoices.*')
                            ->where('invoices.user_id', $user->id)
                            ->where('o_c_s.code','like',"%$search_term%")
                            ->orderBy('invoices.id', 'desc');
                    } elseif ($parameter == 'provider') {
                        $invoices = Invoice::join('o_c_s','invoices.oc_id','=','o_c_s.id')->select('invoices.*')
                            ->where('invoices.user_id', $user->id)
                            ->where('o_c_s.provider','like',"%$search_term%")
                            ->orderBy('invoices.id', 'desc');
                    } else {
                        $invoices = Invoice::where("$parameter", 'like', "%$search_term%")->where('user_id', $user->id)
                            ->orderBy('id', 'desc');
                    }
                }
            }

            $invoices = $invoices->paginate(20);

            foreach ($invoices as $invoice) {
                $invoice->date_issued = Carbon::parse($invoice->date_issued)->hour(0)->minute(0)->second(0);
                if ($invoice->transaction_date != '0000-00-00 00:00:00')
                    $invoice->transaction_date = Carbon::parse($invoice->transaction_date)->hour(0)->minute(0)->second(0);
            }

            Session::put('db_query', $invoices);

            return View::make('app.invoice_brief', ['invoices' => $invoices, 'service' => $service, 'user' => $user,
                'inv_waiting_approval' => 0]);
        }

        elseif ($table == 'item_categories') {
            $columns = Schema::getColumnListing('item_categories');

            if ($has_date) {
                $categories = ItemCategory::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $categories = ItemCategory::query();
                    
                    foreach ($columns as $column) {
                        $categories->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $categories = ItemCategory::where("$parameter", 'like', "%$search_term%");
                }
            }

            $categories = $categories->orderBy('name')->paginate(20);

            return View::make('app.item_categories_brief', ['categories' => $categories, 'service' => $service,
                'user' => $user]);
        }

        elseif ($table == 'items') {
            $columns = Schema::getColumnListing('items');
            $category = ItemCategory::find($id);

            if (!$category) {
                Session::flash('message', 'No se encontraron registros para la categoría solicitada!');
                return redirect()->back();
            }

            if ($has_date) {
                $items = $category->items()->whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $items = $category->items();
                    
                    $items->where(function($query) use($search_term, $columns) {
                        foreach ($columns as $column) {
                            $query->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                        }
                    });
                } else {
                    $items = $category->items()->where("$parameter", 'like', "%$search_term%");
                }
            }

            $items = $items->orderBy('created_at')->paginate(20);

            return View::make('app.items_per_category', ['category' => $category, 'items' => $items, 'service' => $service,
                'user' => $user]);
        }

        elseif ($table == 'maintenances') {
            $columns = Schema::getColumnListing('maintenances');

            if ($has_date) {
                $maintenances = Maintenance::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $maintenances = Maintenance::join('users','maintenances.user_id','=','users.id')
                        ->select('maintenances.*')
                        ->where('users.name','like',"%$search_term%");
                    
                    foreach ($columns as $column) {
                        $maintenances->orWhere("maintenances.$column", 'LIKE', '%' . $search_term . '%');
                    }
                } elseif ($parameter == 'user_name') {
                    $maintenances = Maintenance::join('users','maintenances.user_id','=','users.id')
                        ->select('maintenances.*')
                        ->where('users.name','like',"%$search_term%");
                } else {
                    $maintenances = Maintenance::where("$parameter",'like',"%$search_term%");
                }
            }

            if ($user->priv_level >= 2)
                $maintenances = $maintenances->orderBy('created_at', 'desc')->paginate(20);
            else
                $maintenances = $maintenances->where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(20);

            return View::make('app.maintenance_brief', ['maintenances' => $maintenances, 'service' => $service,
                'user' => $user]);
        }

        elseif ($table == 'oc_certificates') {
            $columns = Schema::getColumnListing('oc_certifications');

            if ($has_date) {
                $certificates = OcCertification::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $certificates = OcCertification::query();
                        
                    foreach ($columns as $column) {
                        $certificates->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $certificates = OcCertification::where("$parameter", 'like', "%$search_term%");
                }
            }

            $certificates = $certificates->paginate(20);

            return View::make('app.oc_certification_brief', ['certificates' => $certificates,
                'service' => $service, 'user' => $user ]);
        }

        elseif ($table == 'ocs') {
            //$columns = ['id', 'code', 'user_id'];
            $columns = Schema::getColumnListing('o_c_s');

            if ($user->priv_level >= 2 || ($user->priv_level == 1 && $user->area == 'Gerencia General')) {
                if ($has_date) {
                    $ocs = OC::whereBetween('created_at', [$from, $to])->orderBy('id', 'desc');
                } else {
                    if ($parameter == 'all') {
                        //$ocs = OC::query();
                        $ocs = OC::join('users', 'o_c_s.pm_id', '=', 'users.id')->select('o_c_s.*')
                            ->where('users.name', 'like', "%$search_term%");
                        
                        foreach ($columns as $column) {
                            $ocs->orWhere("o_c_s.$column", 'LIKE', '%' . $search_term . '%');
                        }
                        $ocs->orderBy('o_c_s.id', 'desc');
                    } elseif ($parameter == 'id') {
                        $ocs = OC::where('id', $search_term)->orderBy('id', 'desc');
                    } elseif ($parameter == 'pm_name') {
                        $ocs = OC::join('users', 'o_c_s.pm_id', '=', 'users.id')->select('o_c_s.*')
                            ->where('users.name', 'like', "%$search_term%")
                            ->orderBy('o_c_s.id', 'desc');

                        //$pm_id = User::select('id')->where('name', 'like', "%$search_term%")->first();
                        //$ocs = OC::where('pm_id', $pm_id)->orderBy('id', 'desc');
                    } else {
                        $ocs = OC::where("$parameter", 'like', "%$search_term%")->orderBy('id', 'desc');
                    }
                }
                //$files = File::where('imageable_type','App\OC');
            } else {
                if ($has_date) {
                    $ocs = OC::whereBetween('created_at', [$from, $to])
                        ->where(function($query) use($user) {
                            $query->where('user_id', $user->id)->orwhere('pm_id', '=', $user->id);
                        })->orderBy('id', 'desc');
                } else {
                    if ($parameter == 'all') {
                        $ocs = OC::join('users', 'o_c_s.pm_id', '=', 'users.id')->select('o_c_s.*')
                            ->where(function($query) use($user) {
                                $query->where('o_c_s.user_id', $user->id)->orwhere('o_c_s.pm_id', '=', $user->id);
                            })
                            ->where(function($query2) use($user, $columns, $search_term) {
                                foreach ($columns as $column) {
                                    $query2->orWhere("o_c_s.$column", 'LIKE', '%' . $search_term . '%');
                                }
                                $query2->orWhere('users.name', 'like', "%$search_term%");
                            });
                        
                        $ocs->orderBy('o_c_s.id', 'desc');
                    } elseif ($parameter == 'id') {
                        $ocs = OC::where('id', $search_term)->where(function($query) use($user) {
                            $query->where('user_id', $user->id)->orwhere('pm_id', '=', $user->id);
                        })->orderBy('id', 'desc');
                    } elseif ($parameter == 'pm_name') {
                        $ocs = OC::join('users', 'o_c_s.pm_id', '=', 'users.id')->select('o_c_s.*')
                            ->where('users.name', 'like', "%$search_term%")
                            ->where(function($query) use($user) {
                                $query->where('o_c_s.user_id', $user->id)->orwhere('o_c_s.pm_id', '=', $user->id);
                            })
                            ->orderBy('o_c_s.id', 'desc');

                        //$pm_info = User::where('name', 'like', "%$search_term%")->first();
                        //$ocs = OC::where('user_id', $user->id)->where('pm_id', $pm_info->id)->orderBy('id', 'desc');
                    } else {
                        $ocs = OC::where("$parameter", 'like', "%$search_term%")->where(function($query) use($user) {
                            $query->where('user_id', $user->id)->orwhere('pm_id', '=', $user->id);
                        })->orderBy('id', 'desc');
                    }
                }
                /*$files = File::join('o_c_s', 'files.imageable_id', '=', 'o_c_s.id')
                    ->select('files.id', 'files.name', 'files.path', 'files.type', 'files.size', 'files.imageable_id')
                    ->where('user_id', $user->id)->orwhere('pm_id','=',$user->id)
                    ->where('imageable_type','App\OC')->get();*/
            }

            Session::put('db_query', $ocs->get());
            $ocs = $ocs->paginate(20);

            return View::make('app.oc_brief', ['ocs' => $ocs, 'service' => $service, 'user' => $user,
                'ocs_waiting_approval' => 0, 'inv_waiting_approval' => 0, 'incomplete_providers' => 0, 'rejected_ocs' => 0 ]);
        }

        elseif ($table == 'operators') {
            $columns = Schema::getColumnListing('operators');
            $columns2 = Schema::getColumnListing('devices');

            if ($has_date) {
                $operators = Operator::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $operators = Operator::join('devices','operators.device_id','=','devices.id')
                        //->join('users','operators.who_delivers','=','users.id')
                        //->join('users','operators.who_receives','=','users.id')
                        ->join('users', function ($join) {
                            $join->on('users.id', '=', 'operators.who_receives')->orOn('users.id', '=', 'operators.who_delivers');
                        })
                        ->select('operators.*');
                        
                    foreach ($columns as $column) {
                        $operators->orWhere("operators.$column", 'LIKE', '%' . $search_term . '%');
                    }

                    $operators->orwhere('users.name','like',"%$search_term%");

                    foreach ($columns2 as $col) {
                        $operators->orwhere("devices.$col",'like',"%$search_term%");
                    }
                } elseif ($parameter == 'who_receives') {
                    $operators = Operator::join('users','operators.who_receives','=','users.id')
                        ->select('operators.*')
                        ->where('users.name','like',"%$search_term%");
                } elseif ($parameter == 'who_delivers') {
                    $operators = Operator::join('users','operators.who_delivers','=','users.id')
                        ->select('operators.*')
                        ->where('users.name','like',"%$search_term%");
                } else {
                    $operators = Operator::join('devices','operators.device_id','=','devices.id')
                        ->select('operators.*')
                        ->where("devices.$parameter",'like',"%$search_term%");
                }
            }

            if ($user->priv_level >= 2) {
                $operators = $operators->orderBy('created_at', 'desc')->paginate(20);
            } else {
                $operators = $operators->where(function ($query) use($user) {
                    $query->where('operators.user_id', $user->id)
                        ->orwhere('who_receives','=',$user->id)
                        ->orwhere('who_delivers','=',$user->id);
                    })
                    ->orderBy('created_at', 'desc')->paginate(20);
            }

            return View::make('app.operator_brief', ['operators' => $operators, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'orders') {
            $columns = Schema::getColumnListing('orders');

            if ($has_date) {
                $orders = Order::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $orders = Order::query();
                        
                    foreach ($columns as $column) {
                        $orders->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $orders = Order::where("$parameter", 'like', "%$search_term%");
                }
            }

            $orders = $orders->orderBy('date_issued')->paginate(20);

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);
            /*
            $files = File::join('orders', 'files.imageable_id', '=', 'orders.id')
                ->select('files.id', 'files.name', 'files.imageable_id', 'files.created_at')
                ->where('imageable_type', 'App\Order')
                ->get();
            */
            foreach ($orders as $order) {
                $order->date_issued = Carbon::parse($order->date_issued)->hour(0)->minute(0)->second(0);
                $order->updated_at = Carbon::parse($order->updated_at)->hour(0)->minute(0)->second(0);

                foreach ($order->files as $file) {
                    $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
                }
            }

            return View::make('app.order_brief', ['orders' => $orders, 'service' => $service,
                'recent_qcc' => 0, 'current_date' => $current_date, 'user' => $user]);
        }

        elseif ($table == 'projects') {
            $columns = Schema::getColumnListing('projects');

            if ($has_date) {
                $projects = Project::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $projects = Project::query();
                        
                    foreach ($columns as $column) {
                        $projects->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $projects = Project::where("$parameter", 'like', "%$search_term%");
                }
            }

            $projects = $projects->paginate(20);
            $projects->ending = 0;

            foreach ($projects as $project) {
                if ($project->application_deadline != '0000-00-00 00:00:00')
                    $project->application_deadline = Carbon::parse($project->application_deadline)->setTime(0,0,0);

                $project->valid_to = Carbon::parse($project->valid_to)->hour(0)->minute(0)->second(0);

                foreach ($project->guarantees as $guarantee) {
                    $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date)->setTime(0,0,0);
                    $guarantee->start_date = Carbon::parse($guarantee->start_date)->setTime(0,0,0);
                }

                if ($project->status == 'Activo' && ($project->user_id == $user->id || $user->priv_level >= 3)) {
                    if (Carbon::now()->diffInDays($project->valid_to,false) <= 5 &&
                        Carbon::now()->diffInDays($project->valid_to,false) >= 0) {
                        $projects->ending++;
                    }
                }
            }

            return View::make('app.project_brief', ['projects' => $projects, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'providers') {
            $columns = Schema::getColumnListing('providers');

            if ($has_date) {
                $providers = Provider::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $providers = Provider::query();
                        
                    foreach ($columns as $column) {
                        $providers->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $providers = Provider::where("$parameter", 'like', "%$search_term%");
                }
                /*
                if ($parameter == 'nit') {
                    $v = \Validator::make(Request::all(), [
                        'buscar' => 'numeric|digits_between:8,10',
                    ]);

                    if ($v->fails()) {
                        Session::flash('message', 'Introduzca un número de NIT válido!');
                        return redirect()->back();
                    }
                }
                */
            }

            $providers = $providers->orderBy('prov_name')->paginate(20);

            return View::make('app.provider_brief', ['providers' => $providers, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'sites') {
            $columns = Schema::getColumnListing('sites');

            $assignment_info = Assignment::find($id);

            if ($has_date) {
                if (!$assignment_info) {
                    $sites = Site::whereBetween('start_date', [$from, $to])->orderBy('id');
                } else {
                    $sites = Site::whereBetween('start_date', [$from, $to])
                        ->where('assignment_id', $id)
                        ->orderBy('id');
                }
            } else {
                if (!$assignment_info) {
                    if ($parameter == 'all') {
                        $sites = Site::join('contacts', 'sites.contact_id', '=', 'contacts.id')
                            ->leftJoin('users', 'sites.resp_id', '=', 'users.id')
                            //->join('users', function ($join) {
                                //$join->on('users.id', '=', 'operators.who_receives')->orOn('users.id', '=', 'operators.who_delivers');
                            //})
                            ->select('sites.*');
                            
                        foreach ($columns as $column) {
                            $sites->orWhere("sites.$column", 'LIKE', '%' . $search_term . '%');
                        }
    
                        $sites->orwhere('users.name', 'like', "%$search_term%")
                            ->orwhere('contacts.name', 'like', "%$search_term%")
                            ->orwhere(function ($query) use($search_term) {
                                $query->whereHas('order', function ($query2) use($search_term) {
                                    $query2->where('code', 'like', "%$search_term%");
                                });
                            });

                        $sites->orderBy('sites.id');
                    } elseif ($parameter == 'resp_name') {
                        $sites = Site::join('users', 'sites.resp_id', '=', 'users.id')->select('sites.*')
                            ->where('users.name', 'like', "%$search_term%")
                            ->orderBy('sites.id');
                    } elseif($parameter == 'contact_name') {
                        $sites = Site::join('contacts', 'sites.contact_id', '=', 'contacts.id')->select('sites.*')
                            ->where('contacts.name', 'like', "%$search_term%")
                            ->orderBy('sites.id');
                    } elseif ($parameter == 'order_code') {
                        $sites = Site::whereHas('order', function ($query) use($search_term) {
                            $query->where('code', 'like', "%$search_term%");
                        })->orderBy('id');
                    } else {
                        $sites = Site::where("$parameter", 'like', "%$search_term%")->orderBy('id');
                    }
                } else {
                    if ($parameter == 'all') {
                        $sites = Site::join('contacts', 'sites.contact_id', '=', 'contacts.id')
                            ->leftJoin('users', 'sites.resp_id', '=', 'users.id')
                            ->select('sites.*')
                            ->where('sites.assignment_id', $id)
                            ->where(function ($query1) use($search_term, $columns) {
                                foreach ($columns as $column) {
                                    $query1->orWhere("sites.$column", 'LIKE', '%' . $search_term . '%');
                                }
            
                                $query1->orwhere('users.name', 'like', "%$search_term%")
                                    ->orwhere('contacts.name', 'like', "%$search_term%")
                                    ->orwhere(function ($query) use($search_term) {
                                        $query->whereHas('order', function ($query2) use($search_term) {
                                            $query2->where('code', 'like', "%$search_term%");
                                        });
                                    });
                            });

                        $sites->orderBy('sites.id');
                    } elseif ($parameter == 'resp_name') {
                        $sites = Site::join('users', 'sites.resp_id', '=', 'users.id')->select('sites.*')
                            ->where('users.name', 'like', "%$search_term%")
                            ->where('sites.assignment_id', $id)
                            ->orderBy('sites.id');
                    } elseif ($parameter == 'contact_name') {
                        $sites = Site::join('contacts', 'sites.contact_id', '=', 'contacts.id')->select('sites.*')
                            ->where('contacts.name', 'like', "%$search_term%")
                            ->where('sites.assignment_id', $id)
                            ->orderBy('sites.id');
                    } elseif ($parameter == 'order_code') {
                        $sites = Site::whereHas('order', function ($query) use($search_term) {
                            $query->where('code', 'like', "%$search_term%");
                        })->where('assignment_id', $id)
                        ->orderBy('id');
                    } else {
                        $sites = Site::where("$parameter", 'like', "%$search_term%")
                            ->where('assignment_id', $id)
                            ->orderBy('id');
                    }
                }
            }

            $sites = $sites->paginate(20);

            foreach ($sites as $site) {
                $site->start_line = Carbon::parse($site->start_line);
                $site->deadline = Carbon::parse($site->deadline);
                $site->start_date = Carbon::parse($site->start_date);
                $site->end_date = Carbon::parse($site->end_date);

                if ($site->assignment->type == 'Fibra óptica') {
                    $site->cable_projected = 0;
                    $site->cable_executed = 0;
                    $site->splice_projected = 0;
                    $site->splice_executed = 0;

                    foreach ($site->tasks as $task) {
                        if (stripos($task->name, 'tendido') !== FALSE && stripos($task->name, 'cable') !== FALSE) {
                            $site->cable_projected += $task->total_expected;
                            $site->cable_executed += $task->progress;
                        } elseif (stripos($task->name, 'empalme') !== FALSE && stripos($task->name, 'ejecución') !== FALSE) {
                            $site->splice_projected += $task->total_expected;
                            $site->splice_executed += $task->progress;
                        }
                    }
                }
            }

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

            return View::make('app.site_brief', ['assignment_info' => $assignment_info, 'sites' => $sites,
                'service' => $service, 'current_date' => $current_date, 'user' => $user]);
        }

        elseif ($table == 'stipend_requests') {
            $columns = Schema::getColumnListing('stipend_requests');

            if ($id && $id != 0) {
                $assignment = Assignment::find($id);

                if (!$assignment) {
                    Session::flash('message', 'Sucedió un error al recuperar la información del servidor, revise la dirección
                        e intente de nuevo por favor');
                    return redirect()->back();
                }
            }
            
            if ($has_date) {
                $stipend_requests = StipendRequest::whereBetween('created_at', [$from, $to])
                    //->where('assignment_id', $id)
                    ->orderBy('created_at', 'desc');
            } else {
                if ($parameter == 'all') {
                    $stipend_requests = StipendRequest::join('employees', 'stipend_requests.employee_id', '=', 'employees.id')
                        ->select('stipend_requests.*');
                        
                    foreach ($columns as $column) {
                        $stipend_requests->orWhere("stipend_requests.$column", 'LIKE', '%' . $search_term . '%');
                    }

                    $stipend_requests->orwhere(DB::raw("CONCAT(TRIM(`first_name`), ' ', TRIM(`last_name`))"), 'like', "%$search_term%")
                        ->orderBy('stipend_requests.created_at', 'desc');
                } elseif ($parameter == 'employee_name') {
                    $stipend_requests = StipendRequest::join('employees', 'stipend_requests.employee_id', '=', 'employees.id')
                        ->select('stipend_requests.*')
                        ->where(DB::raw("CONCAT(TRIM(`first_name`), ' ', TRIM(`last_name`))"), 'like', "%$search_term%")
                        //->where('stipend_requests.assignment_id', $id)
                        ->orderBy('stipend_requests.created_at', 'desc');
                } else {
                    $stipend_requests = StipendRequest::where("$parameter", 'like', "%$search_term%")
                        //->where('assignment_id', $id)
                        ->orderBy('created_at', 'desc');
                }
            }

            $stipend_requests = $stipend_requests->paginate(20);

            foreach ($stipend_requests as $request) {
                $request->date_from = Carbon::parse($request->date_from);
                $request->date_to = Carbon::parse($request->date_to);
            }

            $employee_record = Employee::where('access_id', $user->id)->first();

            return View::make('app.stipend_request_brief', ['stipend_requests' => $stipend_requests, 'service' => $service,
                'user' => $user, 'waiting_payment' => 0, 'waiting_approval' => 0, 'esperando_rendicion' => 0,
                'observed' => 0, 'asg' => ($id ?: ''), 'employee_record' => $employee_record]);
        }

        elseif ($table == 'tasks') {
            $columns = Schema::getColumnListing('tasks');

            $site_info = Site::find($id);

            if (!$site_info) {
                Session::flash('message', 'Sucedió un error al recuperar la información solicitada, revise la dirección 
                    e intente de nuevo por favor');
                return redirect()->back();
            }

            if ($has_date) {
                $tasks = Task::whereBetween('created_at', [$from, $to])
                    ->where('site_id', $id)
                    ->orderBy('id');
            } else {
                if ($parameter == 'all') {
                    $tasks = Task::join('items', 'tasks.item_id', '=', 'items.id')
                        ->select('tasks.*')
                        ->where('tasks.site_id', $id)
                        ->where(function ($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orWhere("tasks.$column", 'LIKE', '%' . $search_term . '%');
                            }
        
                            $query->orwhere('items.client_code', 'like', "%$search_term%");
                        });

                    $tasks->orderBy('tasks.id');
                } elseif ($parameter == 'client_code') {
                    $tasks = Task::join('items', 'tasks.item_id', '=', 'items.id')->select('tasks.*')
                        ->where('items.client_code', 'like', "%$search_term%")
                        ->where('tasks.site_id', $id)
                        ->orderBy('tasks.id');
                } else {
                    $tasks = Task::where("$parameter", 'like', "%$search_term%")
                        ->where('site_id', $id)
                        ->orderBy('id');
                }
            }

            $tasks = $tasks->paginate(20);

            $last_stat = count(Task::$status_options) - 1;
            //$tasks->count()>0 ? $tasks->first()->last_stat() : Task::first()->last_stat();

            foreach ($tasks as $task) {
                $task->start_date = Carbon::parse($task->start_date);
                $task->end_date = Carbon::parse($task->end_date);
            }

            $current_date = Carbon::now()->setTime(0, 0, 0); //->hour(0)->minute(0)->second(0);

            return View::make('app.task_brief', ['site_info' => $site_info, 'tasks' => $tasks, 'last_stat' => $last_stat,
                'service' => $service, 'current_date' => $current_date, 'user' => $user]);
        }

        elseif ($table == 'tenders') {
            $columns = Schema::getColumnListing('tenders');

            if ($has_date) {
                $tenders = Tender::whereBetween('created_at', [$from, $to])->orderBy('id', 'desc');
            } else {
                if ($parameter == 'all') {
                    $tenders = Tender::query();

                    foreach ($columns as $column) {
                        $tenders->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }

                    $tenders->orderBy('id', 'desc');
                } else {
                    $tenders = Tender::where("$parameter", 'like', "%$search_term%")->orderBy('id', 'desc');
                }
            }

            $tenders = $tenders->paginate(20);

            $tenders->ending = 0;
            $tenders->ended = 0;

            foreach ($tenders as $tender) {
                if ($tender->application_deadline != '0000-00-00 00:00:00') {
                    $tender->application_deadline = Carbon::parse($tender->application_deadline)
                        ->setTime(0,0,0);

                    if ($tender->applied == 0 && $tender->status == 'Activo') {
                        if (Carbon::now()->diffInDays($tender->application_deadline,false) <= 5 &&
                            Carbon::now()->diffInDays($tender->application_deadline,false) >= 0) {
                            $tenders->ending++;
                        } elseif ((Carbon::now()->diffInDays($tender->application_deadline, false) < 0)) {
                            $tenders->ended++;
                        }
                    }
                }
            }

            return View::make('app.tender_brief', ['tenders' => $tenders, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'users') {
            $columns = Schema::getColumnListing('users');

            if ($has_date) {
                $records = User::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $records = User::query();

                    foreach ($columns as $column) {
                        $records->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                    }
                } else {
                    $records = User::where("$parameter", 'like', "%$search_term%");
                }
            }
            
            $records = $records->paginate(20);

            return View::make('app.user_brief', ['records' => $records, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'vehicle_conditions') {
            $columns = Schema::getColumnListing('vehicle_conditions');

            $vehicle_info = Vehicle::find($id);

            if ($has_date) {
                $condition_records = VehicleCondition::whereBetween('created_at', [$from, $to])
                    ->where('vehicle_id', $id)
                    ->orderBy('id');
            } else {
                if ($parameter == 'all') {
                    $condition_records = VehicleCondition::where('vehicle_id', $id)
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                            }
                        })
                        ->orderBy('id');
                } else {
                    $condition_records = VehicleCondition::where("$parameter", 'like', "%$search_term%")
                        ->where('vehicle_id', $id)
                        ->orderBy('id');
                }
            }

            $condition_records = $condition_records->paginate(20);

            foreach ($condition_records as $condition_record) {
                $condition_record->last_maintenance = Carbon::parse($condition_record->last_maintenance);
            }

            $current_date = Carbon::now()->hour(0)->minute(0)->second(0);

            return View::make('app.vehicle_condition_brief', ['vehicle_info' => $vehicle_info, 'service' => $service,
                'condition_records' => $condition_records, 'current_date' => $current_date, 'user' => $user]);
        }

        elseif ($table == 'vehicle_histories') {
            $columns = Schema::getColumnListing('vehicle_histories');

            $vehicle = Vehicle::find($id);

            if ($has_date) {
                $vehicle_histories = VehicleHistory::whereBetween('created_at', [$from, $to])
                    ->where('vehicle_id',$vehicle->id);
            } else {
                if ($parameter == 'all') {
                    $vehicle_histories = VehicleHistory::where('vehicle_id',$vehicle->id)
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orWhere("$column", 'LIKE', '%' . $search_term . '%');
                            }
                        });
                } else {
                    $vehicle_histories = VehicleHistory::where("$parameter", 'like', "%$search_term%")
                        ->where('vehicle_id',$vehicle->id);
                }
            }

            $vehicle_histories = $vehicle_histories->paginate(20);

            return View::make('app.vehicle_history', ['vehicle_histories' => $vehicle_histories, 'vehicle' => $vehicle,
                'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'vehicle_requirements') {
            $columns = Schema::getColumnListing('vehicle_requirements');

            if ($has_date) {
                $requirements = VehicleRequirement::whereBetween('created_at', [$from, $to]);
            } else {
                if ($parameter == 'all') {
                    $requirements = VehicleRequirement::join('vehicles','vehicle_requirements.vehicle_id','=','vehicles.id')
                        ->join('users', function ($join) {
                            $join->on('users.id', '=', 'vehicle_requirements.from_id')->orOn('users.id', '=', 'vehicle_requirements.for_id');
                        })
                        ->select('vehicle_requirements.*')
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orWhere("vehicle_requirements.$column", 'LIKE', '%' . $search_term . '%');
                            }
        
                            $query->orwhere('users.name','like',"%$search_term%")
                                ->orwhere("vehicles.license_plate",'like',"%$search_term%")
                                ->orwhere("vehicles.model",'like',"%$search_term%");
                        });
                } elseif ($parameter == 'person_from') {
                    $requirements = VehicleRequirement::join('users','vehicle_requirements.from_id','=','users.id')
                        ->select('vehicle_requirements.*')
                        ->where('users.name','like',"%$search_term%");
                } elseif ($parameter == 'person_for') {
                    $requirements = VehicleRequirement::join('users','vehicle_requirements.for_id','=','users.id')
                        ->select('vehicle_requirements.*')
                        ->where('users.name','like',"%$search_term%");
                } elseif ($parameter == 'license_plate') {
                    $requirements = VehicleRequirement::join('vehicles','vehicle_requirements.vehicle_id','=','vehicles.id')
                        ->select('vehicle_requirements.*')
                        ->where("vehicles.license_plate",'like',"%$search_term%");
                } elseif ($parameter == 'model') {
                    $requirements = VehicleRequirement::join('vehicles','vehicle_requirements.vehicle_id','=','vehicles.id')
                        ->select('vehicle_requirements.*')
                        ->where("vehicles.model",'like',"%$search_term%");
                } else {
                    $requirements = VehicleRequirement::where("$parameter",'like',"%$search_term%");
                }
            }

            $vhc = Input::get('vhc');

            if (!is_null($vhc))
                $requirements = $requirements->where('vehicle_id', $vhc);

            if (!(($user->priv_level >= 2 && $user->area == 'Gerencia Tecnica') || $user->priv_level >= 3 || $user->work_type == 'Transporte')) {
                $requirements = $requirements->where(function ($query) use($user) {
                    $query->where('for_id', $user->id)
                        ->orwhere('from_id', '=', $user->id);
                });
            }

            $requirements = $requirements->orderBy('updated_at','desc')->paginate(20);

            return View::make('app.vehicle_requirement_brief', ['requirements' => $requirements, 'service' => $service,
                'user' => $user, 'vhc' => $vhc]);
        }

        elseif ($table == 'vehicles') {
            $columns = Schema::getColumnListing('vehicles');

            if ($has_date) {
                $vehicles = Vehicle::whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at', 'desc');
            } else {
                if ($parameter == 'all') {
                    $vehicles = Vehicle::join('users', 'vehicles.responsible','=','users.id')
                        ->select('vehicles.*')
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orwhere("vehicles.$column", 'LIKE', '%' . $search_term . '%');
                            }
        
                            $query->orwhere('users.name','like',"%$search_term%");
                        })
                        ->orderBy('vehicles.created_at', 'desc');
                } elseif ($parameter == 'responsible_name') {
                    $vehicles = Vehicle::join('users', 'vehicles.responsible','=','users.id')
                        ->select('vehicles.*')
                        ->where('users.name','like',"%$search_term%")
                        ->orderBy('vehicles.created_at', 'desc');
                } else {
                    $vehicles = Vehicle::where("$parameter", 'like', "%$search_term%")
                        ->orderBy('created_at', 'desc');
                }
            }

            Session::put('db_query', $vehicles->get());
            $vehicles = $vehicles->paginate(20);

            return View::make('app.vehicle_brief', ['vehicles' => $vehicles, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'vhc_failure_reports') {
            $columns = Schema::getColumnListing('vhc_failure_reports');
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                Session::flash('message', 'Sucedió un error al recuperar la información solicitada, revise la dirección 
                    e intente de nuevo por favor');
                return redirect()->back();
            }

            if ($has_date) {
                $reports = VhcFailureReport::whereBetween('created_at', [$from, $to])
                    ->where('vehicle_id', $id)
                    ->orderBy('created_at', 'desc');
            } else {
                if ($parameter == 'all') {
                    $reports = VhcFailureReport::join('users', 'vhc_failure_reports.user_id','=','users.id')
                        ->select('vhc_failure_reports.*')
                        ->where('vhc_failure_reports.vehicle_id', $id)
                        ->where(function($query) use($search_term, $columns) {
                            foreach ($columns as $column) {
                                $query->orwhere("vhc_failure_reports.$column", 'LIKE', '%' . $search_term . '%');
                            }
        
                            $query->orwhere('users.name', 'like', "%$search_term%");
                        })
                        ->orderBy('vhc_failure_reports.created_at', 'desc');
                } elseif ($parameter == 'user_name') {
                    $reports = VhcFailureReport::join('users', 'vhc_failure_reports.user_id', '=', 'users.id')
                        ->select('vhc_failure_reports.*')
                        ->where('users.name', 'like', "%$search_term%")
                        ->where('vhc_failure_reports.vehicle_id', $id)
                        ->orderBy('vhc_failure_reports.created_at', 'desc');
                } else {
                    $reports = VhcFailureReport::where("$parameter", 'like', "%$search_term%")
                        ->where('vehicle_id', $id)
                        ->orderBy('created_at', 'desc');
                }
            }

            $reports = $reports->paginate(20);

            return View::make('app.vehicle_failure_report_brief', ['reports' => $reports, 'service' => $service,
                'user' => $user, 'vehicle' => $vehicle]);
        }

        // Obsolete functions part of Warehouse module (no longer in use)
        /*
        elseif ($table == 'warehouses') {

            if ($has_date)
                $warehouses = Warehouse::whereBetween('created_at', [$from, $to])->paginate(20);
            else
                $warehouses = Warehouse::where("$parameter", 'like', "%$search_term%")->paginate(20);

            return View::make('app.warehouse_brief', ['warehouses' => $warehouses, 'service' => $service,
                'user' => $user]);
        }

        elseif ($table == 'materials') {

            if($id==0){
                if ($has_date)
                    $materials = Material::whereBetween('created_at', [$from, $to])->paginate(20);
                else
                    $materials = Material::where("$parameter", 'like', "%$search_term%")->paginate(20);

                return View::make('app.material_brief', ['materials' => $materials, 'service' => $service,
                    'user' => $user]);
            }
            else{
                $wh_info = Warehouse::find($id);

                if ($has_date)
                    $wh_materials = Warehouse::find($id)->materials()->whereBetween('created_at', [$from, $to])
                        ->paginate(20);
                else
                    $wh_materials = Warehouse::find($id)->materials()->where("$parameter", 'like', "%$search_term%")
                        ->paginate(20);

                return View::make('app.warehouse_materials_brief', ['wh_materials' => $wh_materials, 'wh_info' => $wh_info,
                    'service' => $service, 'user' => $user]);
            }

        }

        elseif ($table == 'wh_entries') {

            if ($has_date)
                $entries = WarehouseEntry::whereBetween('date', [$from, $to]);
            elseif($parameter=='material_name'){
                $material = Material::where('name', 'like', "%$search_term%")->first();
                $material_id = $material ? $material->id : 0;

                $entries = WarehouseEntry::where('material_id', $material_id);
            }
            elseif($parameter=='warehouse_name'){
                $warehouse = Warehouse::where('name', 'like', "%$search_term%")->first();
                $warehouse_id = $warehouse ? $warehouse->id : 0;

                $entries = WarehouseEntry::where('warehouse_id', $warehouse_id);
            }
            else
                $entries = WarehouseEntry::where("$parameter", 'like', "%$search_term%");

            $entries = $entries->orderBy('date','desc')->paginate(20);

            return View::make('app.warehouse_entry_brief', ['entries' => $entries, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'wh_outlets') {

            if ($has_date)
                $outlets = WarehouseOutlet::whereBetween('date', [$from, $to]);
            elseif($parameter=='material_name'){
                $material = Material::where('name', 'like', "%$search_term%")->first();
                $material_id = $material ? $material->id : 0;

                $outlets = WarehouseOutlet::where('material_id', $material_id);
            }
            elseif($parameter=='warehouse_name'){
                $warehouse = Warehouse::where('name', 'like', "%$search_term%")->first();
                $warehouse_id = $warehouse ? $warehouse->id : 0;

                $outlets = WarehouseOutlet::where('warehouse_id', $warehouse_id);
            }
            else
                $outlets = WarehouseOutlet::where("$parameter", 'like', "%$search_term%");

            $outlets = $outlets->orderBy('date','desc')->paginate(20);

            return View::make('app.warehouse_outlet_brief', ['outlets' => $outlets, 'service' => $service, 'user' => $user]);
        }

        elseif ($table == 'wh_events') {

            if ($has_date)
                $events = Event::whereBetween('date', [$from, $to]);
            elseif($parameter=='responsible_name'){
                $responsible = User::where('name', 'like', "%$search_term%")->first();

                if($responsible)
                    $events = Event::where('responsible_id', $responsible->id);
                else
                    $events = Event::where('responsible_id', 0);
            }
            else
                $events = Event::where("$parameter", 'like', "%$search_term%");

            $events = $events->where('eventable_type','like','%Warehouse%')->paginate(20);

            return View::make('app.warehouse_events_brief', ['events' => $events, 'service' => $service, 'user' => $user]);
        }
        */

        //Obsolete function part of RBS viatics table, replaced with stipend_requests
        /*
        elseif ($table == 'rbs_viatics') {

            if ($has_date) {
                $records = RbsViatic::whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at', 'desc');
            }
            elseif($parameter=='status_name'){
                $statuses = array(0 => 'Nueva', 1 => 'Observada', 2 => 'Modificada', 3 => 'Aprobada', 4 => 'Rechazada',
                    5 => 'Completada', 6 => 'Cancelada');

                $key = array_search($search_term, $statuses);
                $key = $key ? $key : 9; //A non existent status to return an empty collection

                $records = RbsViatic::where('status', $key)->orderBy('created_at', 'desc');
            }
            elseif($parameter=='tech_name'){
                $technician = User::where('name', 'like', "%$search_term%")->first();
                $records = RbsViatic::join('rbs_viatic_requests','rbs_viatics.id','=','rbs_viatic_requests.rbs_viatic_id')
                    ->select('rbs_viatics.*')
                    ->where('rbs_viatic_requests.technician_id', $technician->id);
            }
            elseif($parameter=='site_name'){
                $site = Site::with('rbs_viatics')->where('name', 'like', "%$search_term%")->first();
                $records = $site->rbs_viatics();
            }
            else {
                $records = RbsViatic::where("$parameter", 'like', "%$search_term%")
                    ->orderBy('created_at', 'desc');
            }

            $records = $records->paginate(20);

            $waiting_approval = RbsViatic::where('status', 0)->orwhere('status', '=', 2)->count();
            $observed = RbsViatic::where('status', 1)->count();

            return View::make('app.rbs_viatic_brief', ['viatics' => $records, 'service' => $service, 'user' => $user,
                'waiting_approval' => $waiting_approval, 'observed' => $observed]);
        }
        */

        Session::flash('message', 'No se encontró ningún criterio de búsqueda coincidente!');
        return redirect()->back(); //default redirection if no match is found
    }

    // Too specific, should be moved to assignments
    function get_key_item_values($assignment)
    {
        if($assignment->type=='Fibra óptica'){
            $assignment->cable_projected = $assignment->cable_executed = $assignment->cable_percentage = 0;
            $assignment->splice_projected = 0;
            $assignment->splice_executed = 0;
            $assignment->splice_percentage = 0;
            $assignment->posts_projected = 0;
            $assignment->posts_executed = 0;
            $assignment->posts_percentage = 0;
            $assignment->meassures_projected = 0;
            $assignment->meassures_executed = 0;
            $assignment->meassures_percentage = 0;

            foreach($assignment->sites as $site){
                if($site->status>0 /*'No asignado'*/){
                    foreach($site->tasks as $task){
                        $this->get_task_sum_values($task, $assignment);
                    }
                }
            }

            $assignment->cable_percentage = $this->get_percentage($assignment->cable_executed, $assignment->cable_projected);
            $assignment->splice_percentage = $this->get_percentage($assignment->splice_executed, $assignment->splice_projected);
            $assignment->posts_percentage = $this->get_percentage($assignment->posts_executed, $assignment->posts_projected);
            $assignment->meassures_percentage = $this->get_percentage($assignment->meassures_executed,
                $assignment->meassures_projected);
        }
    }

    // Too specific, should be moved to assignments
    function get_task_sum_values($task, $model)
    {
        if($task->status>0/*'No asignado'*/){
            if($task->summary_category){
                if($task->summary_category->cat_name=='fo_cable'){
                    $model->cable_projected += $task->total_expected;
                    $model->cable_executed += $task->progress;
                }
                elseif($task->summary_category->cat_name=='fo_splice'){
                    $model->splice_projected += $task->total_expected;
                    $model->splice_executed += $task->progress;
                }
                elseif($task->summary_category->cat_name=='fo_post'){
                    $model->posts_projected += $task->total_expected;
                    $model->posts_executed += $task->progress;
                }
                elseif($task->summary_category->cat_name=='fo_measure'){
                    $model->meassures_projected += $task->total_expected;
                    $model->meassures_executed += $task->progress;
                }
            }
            
            /*
            if ((stripos($task->name, 'tendido')!==FALSE&&stripos($task->name, 'cable')!==FALSE)||
                stripos($task->name, 'lineal')!==FALSE){
                $model->cable_projected += $task->total_expected;
                $model->cable_executed += $task->progress;
            }
            elseif(stripos($task->name, 'empalme')!==FALSE&&stripos($task->name, 'ejecución')!==FALSE){
                $model->splice_projected += $task->total_expected;
                $model->splice_executed += $task->progress;
            }
            elseif(stripos($task->name, 'poste')!==FALSE&&(stripos($task->name, 'madera')!==FALSE||
                    stripos($task->name, 'prfv')!==FALSE||stripos($task->name, 'hormig')!==FALSE)&&
                    stripos($task->name, 'traslado')===FALSE){
                $model->posts_projected += $task->total_expected;
                $model->posts_executed += $task->progress;
            }
            elseif(stripos($task->name, 'medida')!==FALSE){
                $model->meassures_projected += $task->total_expected;
                $model->meassures_executed += $task->progress;
            }
            */
        }
    }

    function get_percentage($numerator, $denominator)
    {
        $denominator = $denominator == 0 ? 1 : $denominator;

        $percentage = number_format(($numerator / $denominator) * 100, 2);

        return $percentage;
    }
    
    // Old code for searching projects with phases
    /*
        elseif ($table == 'projects') {

            if ($parametro == 'bill_number') {
                $v = \Validator::make(Request::all(), [
                    'buscar' => 'numeric',
                ]);

                if ($v->fails()) {
                    Session::flash('message', 'Introduzca un número de factura válido!');
                    return redirect()->back();
                }
            }

            if ($current_user->priv_level >= 3) {
                if (Request::has('fecha_desde')) {
                    $projects = Project::whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                        ->where('id', '>', 0)
                        ->orderBy('id', 'desc')->paginate(20);
                } else {
                    $projects = Project::where('id', '>', 0)->where("$parametro", 'like', "%$buscar%")
                        ->orderBy('id', 'desc')->paginate(20);
                }

                $next_step = ['Subir documento de asignación',
                    'Subir cotización',
                    'Subir pedido de compra',
                    'Subir pedido de compra firmado',
                    'Subir planilla de cantidades',
                    'Subir planilla de cantidades firmada',
                    'Subir planilla económica',
                    'Subir planilla económica firmada',
                    'Subir certificado de control de calidad',
                    'Agregar datos de factura',
                    'Dar por concluido el proyecto',
                    'Concluído'
                ];
            } elseif ($current_user->area == 'Gerencia General' && $current_user->priv_level == 2) {

                if (Request::has('fecha_desde')) {
                    $projects = Project::whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                        ->whereIn('status', [0, 1, 2, 3, 4, 9, 10])->where('id', '>', 0)
                        ->orderBy('id', 'desc')->paginate(20);
                } else {
                    $projects = Project::where('id', '>', 0)->where("$parametro", 'like', "%$buscar%")
                        ->whereIn('status', [0, 1, 2, 3, 4, 9, 10])
                        ->orderBy('id', 'desc')->paginate(20);
                }

                $next_step = ['Subir documento de asignación',
                    ' ',
                    'Subir pedido de compra',
                    'Subir pedido de compra firmado',
                    ' ',
                    ' ',
                    ' ',
                    ' ',
                    ' ',
                    'Agregar datos de factura',
                    ' ',
                ];
            } elseif ($current_user->area == 'Gerencia Tecnica' && $current_user->priv_level == 2) {

                if (Request::has('fecha_desde')) {
                    $projects = Project::whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                        ->whereIn('status', [1, 2, 4, 5, 6, 7, 8, 9])->where('id', '>', 0)
                        ->orderBy('id', 'desc')->paginate(20);
                } else {
                    $projects = Project::where('id', '>', 0)->where("$parametro", 'like', "%$buscar%")
                        ->whereIn('status', [1, 2, 4, 5, 6, 7, 8, 9])
                        ->orderBy('id', 'desc')->paginate(20);
                }

                $next_step = [' ',
                    'Subir cotización',
                    ' ',
                    ' ',
                    'Subir planilla de cantidades',
                    'Subir planilla de cantidades firmada',
                    'Subir planilla económica',
                    'Subir planilla económica firmada',
                    'Subir certificado de control de calidad',
                    ' ',
                ];
            }

            $files = File::join('projects', 'files.imageable_id', '=', 'projects.id')
                ->select('files.id', 'files.name', 'files.imageable_id', 'files.created_at')
                ->where('imageable_type', 'App\Project')
                ->get();

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

            foreach ($projects as $project) {
                $project->ini_date = Carbon::parse($project->ini_date);
            }
            foreach ($files as $file) {
                $file->created_at = Carbon::parse($file->created_at)->hour(0)->minute(0)->second(0);
            }

            $current_date = Carbon::now();
            $current_date->hour = 0;
            $current_date->minute = 0;
            $current_date->second = 0;

            $service = Session::get('service');

            return View::make('app.project_brief', ['projects' => $projects, 'files' => $files, 'service' => $service,
                'current_date' => $current_date, 'etapa' => $etapa, 'next_step' => $next_step, 'user' => $user]);
        }

        //Old code for searching sites belonging phased projects
        elseif ($table == 'sites') {

            $service = Session::get('service');

            if (Request::has('fecha_desde')) {
                $project_info = Project::find($id);
                $project_sites = Event::where('project_id',$id)->whereBetween('created_at',[$fecha_desde,$fecha_hasta])
                    ->groupBy('project_site')->get();
            }
            else {
                $project_info = Project::find($id);
                $project_sites = Event::where('project_id',$id)->where("$parametro", 'like', "%$buscar%")
                    ->groupBy('project_site')->get();
            }

            return View::make('app.event_brief', ['projects' => 0, 'project_info' => $project_info,
                'project_sites' => $project_sites, 'service' => $service, 'user' => $user]);
        }
        */
}
