<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\ParkingSpace;


/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'parking_space_id' => ParkingSpace::factory(),
            'date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'status' => 'confirmed',
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn() => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Test cancellation',
        ]);
    }
}
