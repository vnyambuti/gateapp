<?php

use App\Models\GateLog;
use App\Models\Vehicle;



it('only exposes gate logs without a time_out as available for gate out', function () {
    $openLog = GateLog::factory()->create(['time_out' => null]);
    $closedLog = GateLog::factory()->goneOut()->create();

    $availableIds = GateLog::whereNull('time_out')->pluck('id');

    expect($availableIds)->toContain($openLog->id)
        ->and($availableIds)->not->toContain($closedLog->id);
});

it('Vehicle::currentlyGatedIn only returns vehicles with an open gate log', function () {
    $inVehicle = Vehicle::factory()->create();
    $outVehicle = Vehicle::factory()->create();
    $neverGatedVehicle = Vehicle::factory()->create();

    GateLog::factory()->create(['vehicle_id' => $inVehicle->id, 'time_out' => null]);
    GateLog::factory()->goneOut()->create(['vehicle_id' => $outVehicle->id]);

    $ids = Vehicle::currentlyGatedIn()->pluck('id');

    expect($ids)->toContain($inVehicle->id)
        ->and($ids)->not->toContain($outVehicle->id)
        ->and($ids)->not->toContain($neverGatedVehicle->id);
});

it('a vehicle with multiple past visits but no open log is not currently gated in', function () {
    $vehicle = Vehicle::factory()->create();

    GateLog::factory()->goneOut()->create(['vehicle_id' => $vehicle->id]);
    GateLog::factory()->goneOut()->create(['vehicle_id' => $vehicle->id]);

    expect(Vehicle::currentlyGatedIn()->pluck('id'))->not->toContain($vehicle->id);
});
