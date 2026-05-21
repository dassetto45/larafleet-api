<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plate' => strtoupper($this->faker->bothify('??###??')),
            'brand' => $this->faker->randomElement(['Fiat', 'Ford', 'BMW', 'Volkswagen', 'Toyota']),
            'model' => $this->faker->word(),
            'year' => $this->faker->numberBetween(2015, 2024),
            'km' => $this->faker->numberBetween(0, 150000),
            'status' => 'available',
        ];
    }

    public function unavailable(): static
    {
        return $this->state(['status' => 'in_use']);
    }

    public function inMaintenance(): static
    {
        return $this->state(['status' => 'maintenance']);
    }
}
