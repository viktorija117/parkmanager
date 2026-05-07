<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParkingSpace;
use App\Models\PermanentReservation;
use App\Models\Reservation;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\UserResource;

class CalendarController extends Controller
{
    public function week(Request $request)
    {
        $request->validate(['start' => 'sometimes|date']);
        $start = $request->date('start') ?? now()->startOfWeek(); // ponedeljak

        $days = collect(range(0, 4))->map(fn($i) => $start->copy()->addDays($i));

        $allSpaces = ParkingSpace::active()->with('permanentReservation.user')->get();
        $permanentSpaceIds = PermanentReservation::pluck('parking_space_id')->all();

        $reservationsByDate = Reservation::confirmed()
            ->whereBetween('date', [$days->first(), $days->last()])
            ->with('user', 'parkingSpace')
            ->get()
            ->groupBy(fn($r) => $r->date->toDateString());

        $myReservationsByDate = Reservation::confirmed()
            ->where('user_id', $request->user()->id)
            ->whereBetween('date', [$days->first(), $days->last()])
            ->get()
            ->keyBy(fn($r) => $r->date->toDateString());

        $result = $days->map(function ($day) use ($allSpaces, $permanentSpaceIds, $reservationsByDate, $myReservationsByDate) {
            $dateStr = $day->toDateString();
            $reservations = $reservationsByDate[$dateStr] ?? collect();
            $takenSpaceIds = $reservations->pluck('parking_space_id')->merge($permanentSpaceIds)->unique();

            return [
                'date' => $dateStr,
                'day_label' => $day->isoFormat('ddd D.M'),
                'available_count' => $allSpaces->whereNotIn('id', $takenSpaceIds)->count(),
                'taken_count' => $reservations->count(),
                'permanent_count' => count($permanentSpaceIds),
                'my_reservation' => $myReservationsByDate[$dateStr] ?? null
                    ? new ReservationResource($myReservationsByDate[$dateStr]->load('parkingSpace'))
                    : null,
            ];
        });

        return response()->json(['week' => $result]);
    }

    public function day(Request $request)
    {
        $request->validate(['date' => 'sometimes|date']);
        $date = $request->date('date') ?? now();

        $allSpaces = ParkingSpace::active()
            ->with([
                'permanentReservation.user',
                'reservations' => fn($q) => $q->confirmed()->whereDate('date', $date)->with('user'),
            ])
            ->get();

        $myReservation = Reservation::confirmed()
            ->where('user_id', $request->user()->id)
            ->whereDate('date', $date)
            ->with('parkingSpace')
            ->first();

        $spaces = $allSpaces->map(function ($space) use ($date) {
            $permanent = $space->permanentReservation;
            $reservation = $space->reservations->first();

            if ($permanent?->isActiveOn($date)) {
                $status = 'permanent';
                $occupant = new UserResource($permanent->user);
            } elseif ($reservation) {
                $status = 'taken';
                $occupant = new UserResource($reservation->user);
            } else {
                $status = 'available';
                $occupant = null;
            }

            return [
                'id'       => $space->id,
                'label'    => $space->label,
                'row'      => $space->row,
                'status'   => $status,
                'occupant' => $occupant,
            ];
        });

        return response()->json([
            'date'           => $date->toDateString(),
            'day_label'      => $date->isoFormat('ddd D.M'),
            'spaces'         => $spaces,
            'my_reservation' => $myReservation ? new ReservationResource($myReservation) : null,
        ]);
    }
}
