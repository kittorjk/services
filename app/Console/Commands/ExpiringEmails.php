<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use Exception;

class ExpiringEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification emails when something is expiring';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = array('name' => "Admin");

        $message = "HTML Email Sent. Please check your inbox.";

        try {
            Mail::send('emails.test_mail_conf', $data, function ($message) {
                $message->to('nestor.romero@abrostec.com', 'Usuario 1')
                    ->cc('nestor.rrc@gmail.com', 'Usuario 1')
                    ->subject('Laravel Testing Mail');
                $message->from('postmaster@gerteabros.com', 'Postmaster');
            });
        } catch (Exception $ex) {
            // $message = $ex;
            $message = 'Email couldn\'t be send, please check configuration';
        }

        $this->info($message);
    }
}
