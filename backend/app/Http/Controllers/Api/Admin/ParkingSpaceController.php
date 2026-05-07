<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreParkingSpaceRequest;
use App\Http\Resources\ParkingSpaceResource;
use App\Models\ParkingSpace;

class ParkingSpaceController extends Controller
{
    public function index()
    {
        return ParkingSpaceResource::collection(
            ParkingSpace::with('permanentReservation.user')->orderBy('label')->get()
        );
    }

    public function store(StoreParkingSpaceRequest $request)
    {
        $space = ParkingSpace::create($request->validated());

        return new ParkingSpaceResource($space);
    }

    public function update(StoreParkingSpaceRequest $request, ParkingSpace $space)
    {
        $space->update($request->validated());

        return new ParkingSpaceResource($space->fresh());
    }

    public function destroy(ParkingSpace $space)
    {
        $space->update(['is_active' => false]);

        return response()->noContent();
    }
}
