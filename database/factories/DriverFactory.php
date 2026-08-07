<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'driver_id' => strtoupper($this->faker->unique()->bothify('DL####??')),
            'phone_number' => '07' . $this->faker->numerify('########'),
        ];
    }
}
