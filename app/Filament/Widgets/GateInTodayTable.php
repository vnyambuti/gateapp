<?php

namespace App\Filament\Widgets;

use App\Models\GateLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class GateInTodayTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Gated In Today')
            ->query(
                GateLog::query()
                    ->whereDate('time_in', today())
                    ->with(['vehicle', 'driver', 'gatedInBy'])
            )
            ->columns([
                TextColumn::make('vehicle.vehicle_number')->label('Vehicle')->searchable(),
                TextColumn::make('driver.name')->label('Driver')->searchable(),
                TextColumn::make('driver.phone_number')->label('Phone'),
                TextColumn::make('time_in')->label('Gated In At')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('gatedInBy.name')->label('Gated In By'),
            ])
            ->defaultSort('time_in', 'desc')
            ->paginated([5, 10, 25]);
    }
}
