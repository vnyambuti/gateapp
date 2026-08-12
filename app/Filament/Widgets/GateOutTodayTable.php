<?php

namespace App\Filament\Widgets;

use App\Models\GateLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class GateOutTodayTable extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Gated Out Today')
            ->query(
                GateLog::query()
                    ->whereNotNull('time_out')
                    ->whereDate('time_out', today())
                    ->with(['vehicle', 'driver', 'gatedOutBy'])
            )
            ->columns([
                TextColumn::make('vehicle.vehicle_number')->label('Vehicle')->searchable(),
                TextColumn::make('driver.name')->label('Driver')->searchable(),
                TextColumn::make('driver.phone_number')->label('Phone'),
                TextColumn::make('time_out')->label('Gated Out At')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('gatedOutBy.name')->label('Gated Out By'),
            ])
            ->defaultSort('time_out', 'desc')
            ->paginated([5, 10, 25]);
    }
}
