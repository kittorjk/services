<?php

namespace App\Http\Controllers;

use App\License;
use Illuminate\Http\Request;
use View;
use Mail;
use Session;
use Exception;
use App\Assignment;
use App\Bill;
use App\Contract;
use App\Email;
use App\Guarantee;
use App\Order;
use App\Project;
use App\Tender;
use App\User;
use App\Vehicle;
use Carbon\Carbon;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MailController extends Controller
{
    public function index()
    {
        $user = Session::get('user');
        if ((is_null($user))||(!$user->id))
            return redirect()->route('root');

        $service = session('service');
        $emails = Email::orderBy('created_at','desc')->paginate(20);

        return View::make('app.email_brief', ['emails' => $emails, 'service' => $service, 'user' => $user]);
    }
    
    public function send_notifications($type)
    {
        /* send emails */
        if ($type == 'assignment_reminder' || $type == 'assignment_expiring') {

            $last_stat = count(Assignment::$status_names) - 1; //Assignment::first()->last_stat();

            if ($type == 'assignment_expiring') {
                $assignments = Assignment::where('deadline', '<', Carbon::now()->addDays(5))
                    ->where('deadline', '>=', Carbon::now())
                    ->where('deadline', '<>', '0000-00-00 00:00:00')
                    ->whereNotIn('status', [$last_stat/*'Concluído'*/, 0/*'No asignado'*/])->get();
            }
            /* else {
                $assignments = Assignment::whereNotIn('status', [$last_stat/*'Concluído', 0/*'No asignado'])
                    ->where('deadline', '<>', '0000-00-00 00:00:00')->get();
            } */
            else {
                $assignments = Assignment::whereNotIn('status', [$last_stat/*'Concluído'*/, 0/*'No asignado'*/])
                    ->where('deadline', '<>', '0000-00-00 00:00:00')
                    ->where(function($query) {
                        $query->where('deadline', '=', Carbon::now()-addDays(10))
                            ->orwhere('deadline', '=', Carbon::now()->addDays(20));
                    })->get();
            }

            $manager = User::where('area', 'Gerencia Tecnica')->where('priv_level', 3)->first();

            foreach ($assignments as $assignment) {
                $recipient = $assignment->responsible ? $assignment->responsible : $manager;
                //$assignment->end_date = Carbon::parse($assignment->end_date);
                $assignment->deadline = Carbon::parse($assignment->deadline);

                $data = array('recipient' => $recipient, 'cc_user' => $manager, 'assignment' => $assignment);
                $subject = 'Recordatorio de la asignación '.$assignment->name;

                $mail_structure = 'emails.expiring_assignment';

                $view = View::make($mail_structure, $data);
                    //['recipient' => $recipient, 'cc_user' => $manager, 'assignment' => $assignment]);
                $content = (string) $view;
                $success = 1;

                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient, $assignment, $manager, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->cc($manager->email, $manager->name)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }

                $this->record_email_data($recipient->email, $manager->email, $subject, $content, $success);
            }
        }

        if ($type == 'order') {
            $orders = Order::where('date_issued','<',Carbon::now()->subDays(15))->where('status','<>','Cobrado')
                ->where('status','<>','Anulado')->get();
            $manager = User::where('area','Gerencia General')->where('priv_level',3)->first();
            $recipient = User::where('role','Asistente administrativo de cobranzas')->where('priv_level',2)->first();

            $recipient = $recipient ?: $manager;

            foreach($orders as $order){
                $order->date_issued = Carbon::parse($order->date_issued);
            }

            if($orders->count()>0) {

                $data = array('recipient' => $recipient, 'orders' => $orders);
                $subject = 'Recordatorio de órdenes pendientes';
                $mail_structure = 'emails.expiring_orders';

                $view = View::make($mail_structure, $data/*['recipient' => $recipient, 'orders' => $orders]*/);
                $content = (string)$view;
                $success = 1;
                try {
                    Mail::send($mail_structure, $data, function ($message) use ($recipient, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }

                $this->record_email_data($recipient->email, '', $subject, $content, $success);
            }
        }

        if($type=='contract'){
            $projects = Project::where('valid_to','<',Carbon::now()->addDays(5))->where('status', 'Activo')->get();

            $carbon_copies = User::select('email')
                ->where(function ($query) {$query->where('area','Gerencia General')->where('priv_level',2);})
                ->orwhere(function ($query) {$query->where('priv_level','>=',3);})
                ->get();

            $cc = array();
            foreach($carbon_copies as $carbon_copy){
                if($carbon_copy->email)
                    $cc[] = $carbon_copy->email;
            }

            $recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();

            foreach($projects as $project){
                $project->valid_to = Carbon::parse($project->valid_to);
            }

            if($projects->count()>0){

                $data = array('recipient' => $recipient, 'contracts' => $projects);
                $subject = 'Recordatorio de contratos a punto de vencer';
                $mail_structure = 'emails.expiring_contracts';

                $view = View::make($mail_structure, $data);
                $content = (string) $view;
                $success = 1;

                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $subject){
                        $message->to($recipient->email, $recipient->name)
                            ->cc($cc)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }

                $this->record_email_data($recipient->email, implode(', ', $cc), $subject, $content, $success);
            }
        }

        if($type=='bill'){
            $bills = Bill::where('date_issued','<',Carbon::now()->subDays(15))->where('status',0)->get();
            $manager = User::where('area','Gerencia General')->where('priv_level',3)->first();
            $recipient = User::where('role','Asistente administrativo de cobranzas')->where('priv_level',2)->first();

            $recipient = $recipient ?: $manager;

            foreach($bills as $bill){
                $bill->date_issued = Carbon::parse($bill->date_issued);
            }

            if($bills->count()>0) {

                $data = array('recipient' => $recipient, 'bills' => $bills);
                $subject = 'Recordatorio de facturas pendientes de cobro';
                $mail_structure = 'emails.expiring_bills';

                $view = View::make($mail_structure, $data/*['recipient' => $recipient, 'bills' => $bills]*/);
                $content = (string)$view;
                $success = 1;

                try {
                    Mail::send($mail_structure, $data, function ($message) use ($recipient, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }

                $this->record_email_data($recipient->email, '', $subject, $content, $success);
            }
        }

        if($type=='guarantee'){
            /*
            $guarantees = Guarantee::join('assignments', 'guarantees.assignment_id','=','assignments.id')
                ->select('guarantees.*')
                ->where('expiration_date','<',Carbon::now()->addDays(5))
                ->where('assignments.status','<>','Concluído')
                ->where('assignments.status','<>','No asignado')
                ->get();
            */
            $guarantees = Guarantee::where('closed',0)->where('expiration_date','<',Carbon::now()->addDays(5))->get();

            $manager = User::where('area','Gerencia General')->where('priv_level',3)->first();
            $supervisor = User::where('role','Asistente administrativo de cobranzas')->where('priv_level',2)->first();
            $recipient = User::where('role','Secretaria')->first();

            $recipient = $recipient ?: $supervisor;
            $supervisor = $recipient ? $supervisor : $manager;

            foreach($guarantees as $guarantee){
                $guarantee->expiration_date = Carbon::parse($guarantee->expiration_date);
            }

            if($guarantees->count()>0){

                $data = array('recipient' => $recipient, 'guarantees' => $guarantees);
                $subject = 'Recordatorio de polizas de garantía próximas a vencer';
                $mail_structure = 'emails.expiring_guarantees';

                $view = View::make($mail_structure, $data/*['recipient'=>$recipient, 'guarantees'=>$guarantees]*/);
                $content = (string) $view;
                $success = 1;

                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient,$supervisor,$subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->cc($supervisor->email, $supervisor->name)
                            //->cc($manager->email, $manager->name)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }

                $this->record_email_data($recipient->email, $supervisor->email /*.', '.$manager->email*/, $subject,
                    $content, $success);
            }
        }

        if($type=='project'){

            $tenders = Tender::where('applied',0)->where('status','Activo')
                ->where('application_deadline','<=',Carbon::now()/*->addDays(3)*/)->get();
            
            $recipient = User::where('area','Gerencia General')->where('priv_level',3)->first();
            $carbon_copies = User::select('email')
                ->where(function ($query) {
                    $query->where('area','Gerencia General')->where('priv_level',2);
                })
                //->orwhere(function ($query) {$query->where('area','Gerencia Tecnica')->where('priv_level','>=',2);})
                ->get();

            $cc = array();
            foreach($carbon_copies as $carbon_copy){
                if($carbon_copy->email)
                    $cc[] = $carbon_copy->email;
            }

            foreach($tenders as $tender){
                $tender->application_deadline = Carbon::parse($tender->application_deadline);
            }

            if($tenders->count()>0){

                $data = array('recipient' => $recipient, 'projects' => $tenders);
                $subject = 'Plazo para presentación a licitaciones termina pronto';
                $mail_structure = 'emails.project_application_deadlines_ending';

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

                $this->record_email_data($recipient->email, implode(', ', $cc), $subject, $content, $success);
            }
        }

        if($type=='driver_license'){

            $licenses = License::where('exp_date', '<>', '0000-00-00 00:00:00')->where('exp_date', '<', Carbon::now()->addDays(15))->whereHas('user', function ($q) {
                $q->where('status', 'Activo');
            })->get();
            
            if($licenses->count()>0){
                $recipient = User::where('work_type', 'Transporte')->where('status', 'Activo')->first();
                $cc = User::where('priv_level', 3)->where('area', 'Gerencia Tecnica')->where('status', 'Activo')->first();

                //$manager = User::where('area', 'Gerencia General')->where('priv_level', 3)->first();

                foreach($licenses as $license){
                    $license->exp_date = Carbon::parse($license->exp_date);
                }

                $data = array('recipient' => $recipient, 'licenses' => $licenses);
                $subject = 'Licencias de conducir prontas a vencer';
                $mail_structure = 'emails.expiring_driver_licenses';

                $view = View::make($mail_structure, $data);
                $content = (string) $view;
                
                $success = 1;

                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->cc($cc->email, $cc->name)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }

                $this->record_email_data($recipient->email, $cc->email, $subject, $content, $success);
            }
        }

        if($type=='vhc_gas_inspection'){

            $exp_inspections = Vehicle::where('gas_inspection_exp', '<', Carbon::now()->addDays(30))
                ->where('gas_inspection_exp', '<>', '0000-00-00 00:00:00')->where('flags', '<>', '0000')->get();

            if($exp_inspections->count()>0){
                $recipient = User::where('work_type', 'Transporte')->first();
                $cc = User::where('priv_level', 3)->where('area', 'Gerencia Administrativa')->first();

                foreach($exp_inspections as $inspection){
                    $inspection->gas_inspection_exp = Carbon::parse($inspection->gas_inspection_exp);
                }

                $data = array('recipient' => $recipient, 'exp_inspections' => $exp_inspections);
                $subject = 'Documentos de inspección técnica de vehículos a GNV prontos a vencer';
                $mail_structure = 'emails.expiring_vhc_gas_inspection';

                $view = View::make($mail_structure, $data);
                $content = (string) $view;

                $success = 1;

                try {
                    Mail::send($mail_structure, $data, function($message) use($recipient, $cc, $subject) {
                        $message->to($recipient->email, $recipient->name)
                            ->cc($cc->email, $cc->name)
                            ->subject($subject)
                            ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                    });
                } catch (Exception $ex) {
                    $success = 0;
                }

                $this->record_email_data($recipient->email, $cc->email, $subject, $content, $success);
            }
        }

        echo "Emails sent. Please check your inbox.";
    }

    public function send_requested_notification($type, $id)
    {
        if($type=='user'){
            $recipient = User::find($id);

            $data = array('recipient' => $recipient);
            $subject = 'Nueva cuenta en el sistema gerteabros.com';
            $mail_structure = 'emails.new_user';

            $view = View::make($mail_structure, $data/*['recipient' => $recipient]*/);
            $content = (string) $view;
            
            $success = 1;

            try {
                Mail::send($mail_structure, $data, function($message) use($recipient, $subject) {
                    $message->to($recipient->email, $recipient->name)
                        ->subject($subject)
                        ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
                });
            } catch (Exception $ex) {
                $success = 0;
            }
            
            $this->record_email_data($recipient->email, '', $subject, $content, $success);
        }

        $user = Session::get('user');

        Session::flash('message', "Emails sent and successfully stored");

        if(Session::has('url'))
            return redirect(Session::get('url'));
        elseif($user->priv_level==4)
            return redirect()->route('user.index');
        else
            return redirect()->back();
    }

    /*
    public function basic_email(){
        $data = array('name' => "Test");

        Mail::send(['text'=>'layouts.mail'], $data, function($message) {
            $message->to('nestor.romero@abrostec.com', 'Subject 1')
                    ->subject('Laravel Basic Testing Mail');
            $message->from('nestor.rrc@gmail.com', 'Subject ORG');
        });
        echo "Basic Email Sent. Check your inbox.";
    }
    */

    public function html_email()
    {
        $data = array('name'=>"Admin");

        $recipient = User::where('priv_level',4)->first();
        $message = "HTML Email Sent. Please check your inbox.";

        try{
            Mail::send('emails.test_mail_conf', $data, function($message) use($recipient) {
                $message->to($recipient->email, $recipient->name)
                        ->cc('nestor.rrc@gmail.com', 'Admin')
                        ->subject('Testing Hosting Mail Functions')
                        ->from('postmaster@gerteabros.com', 'Postmaster');
            });
        } catch (Exception $ex) {
            $message = $ex;
            // $message = 'Email couldn\'t be send, please check configuration';
        }

        echo $message;
    }

    /*
    public function attachment_email(){
        $data = array('name' => "Test");

        Mail::send('layouts.mail', $data, function($message) {
            $message->to('nestor.romero@abrostec.com', 'Subject 1')
                    ->subject('Laravel Testing Mail with Attachment');
            $message->attach('C:\xampp\htdocs\cite\public\imagenes\cite.ico');
            $message->attach('C:\xampp\htdocs\cite\public\robots.txt');
            $message->from('nestor.rrc@gmail.com', 'Subject ORG');
        });
        echo "Email Sent with attachment. Check your inbox.";
    }
    */

    public function resend_email($id)
    {
        $old_email = Email::find($id);
        $data = array('model'=>$old_email);
        $subject = $old_email->subject.' (reenviado '.date_format($old_email->created_at,'d-m-Y').')';
        
        if(!$old_email){
            Session::flash('message', 'Ocurrió un error al recuperar el correo del servidor! Intente de nuevo por favor');
            return redirect()->back();
        }

        $success = 1;

        try {
            Mail::send('emails.basic_layout', $data, function($message) use($old_email, $subject) {
                $message->to($old_email->sent_to)
                    ->subject($subject)
                    ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
            });
        } catch (Exception $ex) {
            $success = 0;
        }

        if($success==1){
            $this->record_email_data($old_email->sent_to, '', $subject, $old_email->content, $success);

            $message = "El correo fue reenviado correctamente";
        }
        else
            $message = "Ocurrió un error al reenviar el correo! Intente de nuevo por favor";

        Session::flash('message', $message);
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('email.index');
    }

    public function choose_recipient_form($id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');

        $service = Session::get('service');
        $email = Email::find($id);

        if($email)
            return View::make('app.email_choose_recipient_form', ['email' => $email, 'service' => $service, 'user' => $user]);
        else{
            Session::flash('message', 'Ocurrió un error al recuperar el correo del servidor! Intente de nuevo por favor');
            return redirect()->back();
        }
    }

    public function send_to_new_recipient(Request $request, $id)
    {
        $user = Session::get('user');
        if ((is_null($user)) || (!$user->id))
            return redirect()->route('root');
        
        $v = \Validator::make($request->all(), [
            'email'                   => 'required|email',
        ],
            [
                'email.required'        => 'Debe especificar la dirección a la que se enviará el correo electrónico!',
                'email.email'           => 'Debe introducir una dirección de correo electrónico válida!',
            ]
        );
        
        if ($v->fails())
        {
            Session::flash('message', $v->messages()->first());
            return redirect()->back()->withInput();
        }

        $sent_to = $request->input('email');

        $old_email = Email::find($id);
        $data = array('model' => $old_email);
        $subject = $old_email->subject.' (de fecha '.date_format($old_email->created_at,'d-m-Y').')';
        $mail_structure = 'emails.basic_layout';

        if(!$old_email){
            Session::flash('message', 'Ocurrió un error al recuperar el correo del servidor. Intente de nuevo por favor');
            return redirect()->back()->withInput();
        }

        $success = 1;
        
        try {
            Mail::send($mail_structure, $data, function($message) use($old_email, $sent_to, $subject) {
                $message->to($sent_to)
                    ->subject($subject)
                    ->from('postmaster@gerteabros.com', 'postmaster@gerteabros.com');
            });
        } catch (Exception $ex) {
            $success = 0;
        }

        if($success==1){
            $this->record_email_data($sent_to, '', $subject, $old_email->content, $success);

            $message = "El correo fue reenviado correctamente";
        }
        else
            $message = "El correo no pudo ser enviado al destinatario especificado!";

        Session::flash('message', $message);
        if(Session::has('url'))
            return redirect(Session::get('url'));
        else
            return redirect()->route('email.index');
    }

    function record_email_data($sent_to, $sent_cc, $subject, $content, $success)
    {
        $email = new Email;
        $email->sent_by = 'postmaster@gerteabros.com';
        $email->sent_to = $sent_to;
        $email->sent_cc = $sent_cc;
        $email->subject = $subject;
        $email->content = $content;
        $email->success = $success;
        $email->save();

        return true;
    }
}
