<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => 'HQ',
            'address' => fake()->address(),
            'timezone' => 'Europe/Belgrade',
        ];
    }
}
