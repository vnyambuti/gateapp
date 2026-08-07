<?php

namespace App\Filament\Widgets;

use App\Models\GateLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CurrentlyGatedInTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Vehicles Currently On-Site')
            ->query(
                GateLog::query()
                    ->whereNull('time_out')
                    ->with(['vehicle', 'driver', 'gatedInBy'])
            )
            ->columns([
                TextColumn::make('vehicle.vehicle_number')->label('Vehicle')->searchable(),
                TextColumn::make('driver.name')->label('Driver')->searchable(),
                TextColumn::make('driver.phone_number')->label('Phone'),
                TextColumn::make('time_in')->label('Gated In At')->dateTime('d M Y, H:i'),
                TextColumn::make('gatedInBy.name')->label('Gated In By'),
            ])
            ->paginated([5, 10, 25]);
    }
}
