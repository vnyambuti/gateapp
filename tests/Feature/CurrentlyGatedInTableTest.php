<?php

use App\Filament\Widgets\CurrentlyGatedInTable;
use App\Models\GateLog;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    livewire(CurrentlyGatedInTable::class)->assertSuccessful();
});

it('only lists vehicles that are currently gated in', function () {
    $inLog = GateLog::factory()->create(['time_out' => null]);
    $outLog = GateLog::factory()->goneOut()->create();

    livewire(CurrentlyGatedInTable::class)
        ->assertCanSeeTableRecords([$inLog])
        ->assertCanNotSeeTableRecords([$outLog]);
});

it('is searchable by vehicle number and driver name', function () {
    $log = GateLog::factory()->create(['time_out' => null]);

    livewire(CurrentlyGatedInTable::class)
        ->searchTable($log->vehicle->vehicle_number)
        ->assertCanSeeTableRecords([$log]);

    livewire(CurrentlyGatedInTable::class)
        ->searchTable($log->driver->name)
        ->assertCanSeeTableRecords([$log]);
});
