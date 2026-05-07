<?php

namespace App\Providers;

use App\Events\ReservationCancelled;
use App\Events\ReservationCreated;
use App\Listeners\LogReservationCancelled;
use App\Listeners\LogReservationCreated;
use App\Listeners\NotifyUserReservationCancelledByAdmin;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.frontend_url') . "/reset-password?token={$token}&email={$user->email}";
        });

        Event::listen(ReservationCreated::class, LogReservationCreated::class);
        Event::listen(ReservationCancelled::class, LogReservationCancelled::class);
        Event::listen(ReservationCancelled::class, NotifyUserReservationCancelledByAdmin::class);
    }
}
