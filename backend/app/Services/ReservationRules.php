<?php

namespace App\Services;

use Carbon\Carbon;

class ReservationRules
{
    public static function canReserve(Carbon $date): bool
    {
        $maxDate = today()->addDays((int) config('parking.reservation_window_days'));

        return $date->isFuture() && $date->lte($maxDate) && $date->isWeekday();
    }

    public static function canCancel(Carbon $reservationDate): bool
    {
        $cutoff = $reservationDate->copy()
            ->subDay()
            ->setHour((int) config('parking.cancellation_cutoff_hour'))
            ->setMinute(59);

        return now()->lt($cutoff);
    }
}
