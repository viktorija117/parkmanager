<?php

namespace App\Http\Controllers\Api;

use App\Events\ReservationCancelled;
use App\Events\ReservationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\ParkingSpace;
use App\Models\PermanentReservation;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'scope' => 'sometimes|in:upcoming,past,all',
        ]);

        $query = $request->user()->reservations()->with('parkingSpace');

        $query = match ($request->input('scope', 'upcoming')) {
            'upcoming' => $query->confirmed()->upcoming()->orderBy('date'),
            'past'     => $query->where('date', '<', today())->orderByDesc('date'),
            'all'      => $query->orderByDesc('date'),
        };

        return ReservationResource::collection($query->paginate(20));
    }

    public function store(CreateReservationRequest $request)
    {
        $dates = $request->resolvedDates();
        $reservations = [];

        DB::transaction(function () use ($request, $dates, &$reservations) {
            $permanentIds = PermanentReservation::lockForUpdate()->pluck('parking_space_id')->all();

            foreach ($dates as $date) {
                $reservedIds = Reservation::confirmed()
                    ->lockForUpdate()
                    ->whereDate('date', $date)
                    ->pluck('parking_space_id')
                    ->all();

                $excludedIds = array_merge($permanentIds, $reservedIds);

                if ($request->parking_space_id) {
                    if (in_array($request->parking_space_id, $excludedIds)) {
                        throw ValidationException::withMessages([
                            'parking_space_id' => __('reservations.space_taken', ['date' => $date]),
                        ]);
                    }
                    $space = ParkingSpace::findOrFail($request->parking_space_id);
                } else {
                    $space = ParkingSpace::active()
                        ->whereNotIn('id', $excludedIds)
                        ->lockForUpdate()
                        ->inRandomOrder()
                        ->first();
                }

                if (! $space) {
                    throw ValidationException::withMessages([
                        'dates' => __('reservations.no_spaces', ['date' => $date]),
                    ]);
                }

                $reservation = Reservation::create([
                    'user_id'          => $request->user()->id,
                    'parking_space_id' => $space->id,
                    'date'             => $date,
                    'status'           => 'confirmed',
                ]);

                $reservation->load('parkingSpace');
                event(new ReservationCreated($reservation));
                $reservations[] = $reservation;
            }
        });

        return ReservationResource::collection(collect($reservations));
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return new ReservationResource($reservation->load('parkingSpace'));
    }

    public function destroy(Request $request, Reservation $reservation)
    {
        $this->authorize('cancel', $reservation);

        $reservation->update([
            'status'               => 'cancelled',
            'cancelled_at'         => now(),
            'cancelled_by'         => $request->user()->id,
            'cancellation_reason'  => $request->input('reason', 'User cancelled'),
        ]);

        event(new ReservationCancelled($reservation));

        return response()->noContent();
    }
}
