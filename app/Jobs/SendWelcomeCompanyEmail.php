<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeCompanyMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendWelcomeCompanyEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $details;
    public $tries=5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            Mail::to($this->details['email'])->send(new WelcomeCompanyMail($this->details));

            DB::table('emails_history')->insert([
                'email'=>$this->details['email'],
                'title'=>$this->details['title'],
                'body'=>$this->details['body'],
                'job_name'=>self::class,
                'time'=>Carbon::now()
            ]);
        }
        
        catch(Exception $e)
        {

        }
    }
}
