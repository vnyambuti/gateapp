<?php

use App\Filament\Pages\GateIn;
use App\Models\Driver;
use App\Models\GateLog;
use App\Models\User;
use App\Models\Vehicle;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders successfully', function () {
    livewire(GateIn::class)->assertSuccessful();
});

it('requires vehicle and driver to be selected', function () {
    livewire(GateIn::class)
        ->fillForm([
            'vehicle_id' => null,
            'driver_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['vehicle_id', 'driver_id']);

    expect(GateLog::count())->toBe(0);
});

it('auto-populates driver id and phone number when a driver is selected', function () {
    $driver = Driver::factory()->create();

    livewire(GateIn::class)
        ->set('data.driver_id', $driver->id)
        ->assertSet('data.driver_id_number', $driver->driver_id)
        ->assertSet('data.phone_number', $driver->phone_number);
});

it('creates a gate log and auto-captures time in and the creating user', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();

    // subSecond() to allow for the dateTime column truncating away the
    // microseconds that now() captures here.
    $before = now()->subSecond();

    livewire(GateIn::class)
        ->fillForm([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $log = GateLog::first();

    expect($log)->not->toBeNull()
        ->and($log->vehicle_id)->toBe($vehicle->id)
        ->and($log->driver_id)->toBe($driver->id)
        ->and($log->gated_in_by)->toBe($this->user->id)
        ->and($log->time_out)->toBeNull()
        ->and($log->time_in)->not->toBeNull()
        ->and($log->time_in->greaterThanOrEqualTo($before))->toBeTrue();
});

it('resets the form after a successful gate in', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();

    livewire(GateIn::class)
        ->fillForm([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
        ])
        ->call('create')
        ->assertSet('data.vehicle_id', null)
        ->assertSet('data.driver_id', null);
});

it('refuses to gate in a vehicle that is already gated in', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();

    GateLog::factory()->create([
        'vehicle_id' => $vehicle->id,
        'time_out' => null,
    ]);

    livewire(GateIn::class)
        ->fillForm([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
        ])
        ->call('create');

    expect(GateLog::where('vehicle_id', $vehicle->id)->count())->toBe(1);
});

it('allows gating in a vehicle again once it has already been gated out', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();

    GateLog::factory()->goneOut()->create([
        'vehicle_id' => $vehicle->id,
    ]);

    livewire(GateIn::class)
        ->fillForm([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(GateLog::where('vehicle_id', $vehicle->id)->whereNull('time_out')->count())->toBe(1);
});
