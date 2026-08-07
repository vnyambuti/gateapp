<?php

use App\Models\Driver;
use App\Models\GateLog;
use App\Models\User;
use App\Models\Vehicle;

it('belongs to a vehicle and a driver', function () {
    $gateLog = GateLog::factory()->create();

    expect($gateLog->vehicle)->toBeInstanceOf(Vehicle::class)
        ->and($gateLog->driver)->toBeInstanceOf(Driver::class);
});

it('tracks who gated the vehicle in and, separately, who gated it out', function () {
    $inUser = User::factory()->create();
    $outUser = User::factory()->create();

    $gateLog = GateLog::factory()->create(['gated_in_by' => $inUser->id]);
    $gateLog->update(['time_out' => now(), 'gated_out_by' => $outUser->id]);

    expect($gateLog->gatedInBy->is($inUser))->toBeTrue()
        ->and($gateLog->gatedOutBy->is($outUser))->toBeTrue();
});

it('casts time_in and time_out to Carbon instances', function () {
    $gateLog = GateLog::factory()->goneOut()->create();

    expect($gateLog->time_in)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($gateLog->time_out)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('deletes gate logs when the related vehicle is deleted', function () {
    $vehicle = Vehicle::factory()->create();
    $gateLog = GateLog::factory()->create(['vehicle_id' => $vehicle->id]);

    $vehicle->delete();

    expect(GateLog::find($gateLog->id))->toBeNull();
});

it('deletes gate logs when the related driver is deleted', function () {
    $driver = Driver::factory()->create();
    $gateLog = GateLog::factory()->create(['driver_id' => $driver->id]);

    $driver->delete();

    expect(GateLog::find($gateLog->id))->toBeNull();
});
