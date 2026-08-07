<?php

use App\Filament\Pages\GateOut;
use App\Models\Driver;
use App\Models\GateLog;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders successfully', function () {
    livewire(GateOut::class)->assertSuccessful();
});

it('requires a vehicle to be selected', function () {
    livewire(GateOut::class)
        ->fillForm(['gate_log_id' => null])
        ->call('create')
        ->assertHasFormErrors(['gate_log_id']);
});

it('auto-populates driver name, id and phone number when a vehicle is selected', function () {
    $driver = Driver::factory()->create();
    $gateLog = GateLog::factory()->create([
        'driver_id' => $driver->id,
        'time_out' => null,
    ]);

    livewire(GateOut::class)
        ->set('data.gate_log_id', $gateLog->id)
        ->assertSet('data.driver_name', $driver->name)
        ->assertSet('data.driver_id_number', $driver->driver_id)
        ->assertSet('data.phone_number', $driver->phone_number);
});

it('gates out a vehicle and auto-captures time out and the gating-out user', function () {
    $gateLog = GateLog::factory()->create(['time_out' => null]);


    $before = now()->subSecond();

    livewire(GateOut::class)
        ->fillForm(['gate_log_id' => $gateLog->id])
        ->call('create')
        ->assertHasNoFormErrors();

    $gateLog->refresh();

    expect($gateLog->time_out)->not->toBeNull()
        ->and($gateLog->time_out->greaterThanOrEqualTo($before))->toBeTrue()
        ->and($gateLog->gated_out_by)->toBe($this->user->id);
});

it('resets the form after a successful gate out', function () {
    $gateLog = GateLog::factory()->create(['time_out' => null]);

    livewire(GateOut::class)
        ->fillForm(['gate_log_id' => $gateLog->id])
        ->call('create')
        ->assertSet('data.gate_log_id', null);
});

it('does not re-process a log that has already been gated out', function () {

    $gateLog = GateLog::factory()->create(['time_out' => null]);
    $originalOutUser = User::factory()->create();
    $gateLog->update(['time_out' => now()->subMinute(), 'gated_out_by' => $originalOutUser->id]);

    livewire(GateOut::class)
        ->fillForm(['gate_log_id' => $gateLog->id])
        ->call('create');

    $gateLog->refresh();


    expect($gateLog->gated_out_by)->toBe($originalOutUser->id);
});
