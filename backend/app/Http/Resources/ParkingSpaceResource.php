<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingSpaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'row' => $this->row,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'is_permanent' => $this->whenLoaded('permanentReservation', fn() => (bool)$this->permanentReservation),
            'permanent_user' => $this->whenLoaded(
                'permanentReservation',
                fn() => $this->permanentReservation ? new UserResource($this->permanentReservation->user) : null
            ),
        ];
    }
}
