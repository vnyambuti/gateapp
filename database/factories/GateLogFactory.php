<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\GateLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class GateLogFactory extends Factory
{
    protected $model = GateLog::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
            'time_in' => now()->subHour(),
            'time_out' => null,
            'gated_in_by' => User::factory(),
            'gated_out_by' => null,
        ];
    }

    /**
     * A gate log for a vehicle that has already left the premises.
     */
    public function goneOut(): static
    {
        return $this->state(fn () => [
            'time_out' => now(),
            'gated_out_by' => User::factory(),
        ]);
    }
}
