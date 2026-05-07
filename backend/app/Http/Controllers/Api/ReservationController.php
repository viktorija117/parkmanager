<?php

namespace App\Http\Controllers\Api;

use App\Events\ReservationCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\ParkingSpace;
use App\Models\PermanentReservation;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'scope' => 'sometimes|in:upcoming,past,all',
        ]);

        $query = $request->user()->reservations()->with('parkingSpace');

        match ($request->get('scope', 'upcoming')) {
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
            foreach ($dates as $date) {
                $permanentIds = PermanentReservation::pluck('parking_space_id')->all();
                $reservedIds = Reservation::confirmed()->whereDate('date', $date)->pluck('parking_space_id')->all();
                $excludedIds = array_merge($permanentIds, $reservedIds);

                if ($request->parking_space_id) {
                    if (in_array($request->parking_space_id, $excludedIds)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'parking_space_id' => __('reservations.space_taken', ['date' => $date]),
                        ]);
                    }
                    $space = ParkingSpace::find($request->parking_space_id);
                } else {
                    $space = ParkingSpace::active()->whereNotIn('id', $excludedIds)->inRandomOrder()->first();
                }

                if (! $space) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
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

    public function show(Request $request, Reservation $reservation)
    {
        abort_if($reservation->user_id !== $request->user()->id, 403);

        return new ReservationResource($reservation->load('parkingSpace'));
    }

    public function destroy(Request $request, Reservation $reservation)
    {
        abort_if($reservation->user_id !== $request->user()->id, 403);

        $reservation->delete();

        return response()->noContent();
    }
}
