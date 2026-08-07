<?php

namespace App\Filament\Pages;

use App\Models\GateLog;
use App\Models\Vehicle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GateOut extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowLeftOnRectangle;
    protected static ?string $navigationLabel = 'Gate Out';
    protected static ?string $title = 'Vehicle Gate Out';

    protected string $view = 'filament.pages.gate-out';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('gate_log_id')
                    ->label('Vehicle Number')
                    ->options(function () {
                        return GateLog::with('vehicle')
                            ->whereNull('time_out')
                            ->get()
                            ->pluck('vehicle.vehicle_number', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $gateLog = GateLog::with('driver')->find($state);
                        if ($gateLog) {
                            $set('driver_name', $gateLog->driver->name);
                            $set('driver_id_number', $gateLog->driver->driver_id);
                            $set('phone_number', $gateLog->driver->phone_number);
                        }
                    }),

                TextInput::make('driver_name')
                    ->label('Driver Name')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('driver_id_number')
                    ->label('Driver ID')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->disabled()
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $gateLog = GateLog::whereNull('time_out')->find($data['gate_log_id']);

        if (! $gateLog) {
            Notification::make()
                ->title('This vehicle is not currently gated in.')
                ->danger()
                ->send();
            return;
        }

        $gateLog->update([
            'time_out' => now(),
            'gated_out_by' => auth()->id(),
        ]);

        Notification::make()
            ->title('Vehicle gated out successfully.')
            ->success()
            ->send();

        $this->form->fill();
    }
}
