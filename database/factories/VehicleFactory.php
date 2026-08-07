<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'vehicle_number' => strtoupper('K' . $this->faker->bothify('??? ###')),
            'make' => $this->faker->randomElement(['Toyota', 'Nissan', 'Subaru', 'Mazda', 'Isuzu']),
            'model' => $this->faker->word(),
            'color' => $this->faker->safeColorName(),
        ];
    }
}
