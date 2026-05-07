<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationRules;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->isAdmin() || $reservation->user_id === $user->id;
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($reservation->user_id !== $user->id) {
            return false;
        }

        if ($reservation->status !== 'confirmed') {
            return false;
        }

        return ReservationRules::canCancel(\Carbon\Carbon::parse($reservation->date));
    }
}
