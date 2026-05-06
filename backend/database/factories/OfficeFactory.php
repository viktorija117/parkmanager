<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    use HasFactory;

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
