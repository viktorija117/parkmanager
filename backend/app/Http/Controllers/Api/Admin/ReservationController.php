<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\ReservationCancelled;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'user_id' => 'sometimes|exists:users,id',
            'parking_space_id' => 'sometimes|exists:parking_spaces,id',
            'status' => 'sometimes|in:confirmed,cancelled',
        ]);

        $query = Reservation::with(['user', 'parkingSpace', 'canceller'])->orderByDesc('date');

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->parking_space_id) {
            $query->where('parking_space_id', $request->parking_space_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        return ReservationResource::collection($query->paginate(50));
    }

    public function adminCancel(Request $request, Reservation $reservation)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        abort_if(
            $reservation->status !== 'confirmed' || $reservation->date->lt(today()),
            422,
            'Only upcoming confirmed reservations can be cancelled.'
        );

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancellation_reason' => $request->reason,
        ]);

        event(new ReservationCancelled($reservation->fresh()->load('parkingSpace', 'user'), byAdmin: true));

        return new ReservationResource($reservation->fresh()->load('parkingSpace', 'user'));
    }
}
