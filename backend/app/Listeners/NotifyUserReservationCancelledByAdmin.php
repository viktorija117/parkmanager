<?php

namespace App\Listeners;

use App\Events\ReservationCancelled;
use App\Mail\ReservationCancelledByAdmin;
use Illuminate\Support\Facades\Mail;

class NotifyUserReservationCancelledByAdmin
{
    public function handle(ReservationCancelled $event): void
    {
        if (! $event->byAdmin) {
            return;
        }

        Mail::to($event->reservation->user)->send(
            new ReservationCancelledByAdmin($event->reservation)
        );
    }
}
