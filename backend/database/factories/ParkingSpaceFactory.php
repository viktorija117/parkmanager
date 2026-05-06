<?php

namespace Database\Factories;

use App\Models\ParkingSpace;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Office;

/**
 * @extends Factory<ParkingSpace>
 */
class ParkingSpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'office_id' => Office::factory(),
            'label' => fake()->unique()->bothify('?-##'),
            'row' => 'Row ' . fake()->numberBetween(1, 10),
            'notes' => fake()->randomElement(['Near elevator', 'Covered', 'Outside', null]),
            'is_active' => true,
        ];
    }
}
