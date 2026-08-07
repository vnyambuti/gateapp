<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\GateLog;

class GateStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Currently Gated In', GateLog::whereNull('time_out')->count())
                ->description('Vehicles on-site right now')
                ->color('warning')
                ->icon('heroicon-o-truck'),

            Stat::make('Gated In Today', GateLog::whereDate('time_in', today())->count())
                ->description('Total entries today')
                ->color('success')
                ->icon('heroicon-o-arrow-right-on-rectangle'),

            Stat::make('Gated Out Today', GateLog::whereDate('time_out', today())->count())
                ->description('Total exits today')
                ->color('info')
                ->icon('heroicon-o-arrow-left-on-rectangle'),
        ];
    }
}
