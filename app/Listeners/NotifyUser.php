<?php

namespace App\Listeners;

use App\Events\UserEventCreated;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;

class NotifyUser
{
    /**
     * Create the event listener.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\UserEventCreated  $event
     * @return void
     */
    public function handle(UserEventCreated $event)
    {
        //
        $user = User::where('status', '=', 0)->orderBy('id', 'DESC')->first();
        $user->notify(new WelcomeNotification($event->user));
    }
}
