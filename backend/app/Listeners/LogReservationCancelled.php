<?php

namespace App\Listeners;

use App\Events\ReservationCancelled;
use Illuminate\Support\Facades\Log;

class LogReservationCancelled
{
    public function handle(ReservationCancelled $event): void
    {
        Log::info('Reservation cancelled', [
            'reservation_id'      => $event->reservation->id,
            'user_id'             => $event->reservation->user_id,
            'date'                => $event->reservation->date->toDateString(),
            'cancellation_reason' => $event->reservation->cancellation_reason,
        ]);
    }
}
