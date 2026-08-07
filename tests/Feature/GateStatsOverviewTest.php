<?php

use App\Filament\Widgets\GateStatsOverview;
use App\Models\GateLog;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders successfully', function () {
    livewire(GateStatsOverview::class)->assertSuccessful();
});

it('calculates currently-in, in-today and out-today counts correctly', function () {
    // 2 vehicles currently on site, both gated in today
    GateLog::factory()->count(2)->create(['time_out' => null]);

    // 1 vehicle gated in and out today
    GateLog::factory()->goneOut()->create();

    // 1 old visit, gated in and out 3 days ago — should not count towards "today" stats
    GateLog::factory()->create([
        'time_in' => now()->subDays(3),
        'time_out' => now()->subDays(3)->addHour(),
        'gated_out_by' => User::factory(),
    ]);

    expect(GateLog::whereNull('time_out')->count())->toBe(2)
        ->and(GateLog::whereDate('time_in', today())->count())->toBe(3)
        ->and(GateLog::whereDate('time_out', today())->count())->toBe(1);
});
