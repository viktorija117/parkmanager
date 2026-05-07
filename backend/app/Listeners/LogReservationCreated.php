<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use Illuminate\Support\Facades\Log;

class LogReservationCreated
{
    public function handle(ReservationCreated $event): void
    {
        Log::info('Reservation created', [
            'reservation_id'   => $event->reservation->id,
            'user_id'          => $event->reservation->user_id,
            'parking_space_id' => $event->reservation->parking_space_id,
            'date'             => $event->reservation->date->toDateString(),
        ]);
    }
}
