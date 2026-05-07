<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermanentReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user'         => new UserResource($this->whenLoaded('user')),
            'parking_space' => new ParkingSpaceResource($this->whenLoaded('parkingSpace')),
            'starts_on'    => $this->starts_on?->toDateString(),
            'ends_on'      => $this->ends_on?->toDateString(),
            'admin_notes'  => $this->admin_notes,
        ];
    }
}
