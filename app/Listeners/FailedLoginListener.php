<?php

namespace App\Listeners;

use App\Events\FailedLoginEvent;
use Illuminate\Http\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FailedLoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public Request $request)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FailedLoginEvent  $event
     * @return void
     */
    public function handle(object $event): void
    {
        $listener = config('authentication-log.events.failed');
        if (! $event instanceof $listener) {
            return;
        }

        if ($event->user) {
            $log = $event->user->authentications()->create([
                'ip_address' => $ip = $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'login_at' => now(),
                'login_successful' => false,
                'location' => config('authentication-log.notifications.new-device.location') ? optional(geoip()->getLocation($ip))->toArray() : null,
            ]);

            if (config('authentication-log.notifications.failed-login.enabled')) {
                $failedLogin = config('authentication-log.notifications.failed-login.template') ?? FailedLogin::class;
                $event->user->notify(new $failedLogin($log));
            }
        }
    }
}
