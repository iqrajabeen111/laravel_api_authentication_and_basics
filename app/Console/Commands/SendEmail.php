<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notifications\WelcomeNotification;
use App\Models\User;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Welcome:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send email to new user';

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
     * @return int
     */
    public function handle()
    {
        $user = User::where('status', '=', 0)->orderBy('id', 'DESC')->first();
            // echo $user->status;
        if ($user && $user->status != 1 ) {
            $user->notify(new WelcomeNotification());
            $user->status = 1;
            $user->update();
            echo "notification sent";
        } else {
            echo "trying to get property of non-object";
        }
    }
}
