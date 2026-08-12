<?php

namespace App\Filament\Pages;

use App\Models\Driver;
use App\Models\GateLog;
use App\Models\Vehicle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Actions\Action;

class GateIn extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedArrowRightOnRectangle;
    protected static ?string $navigationLabel = 'Gate In';
    protected static ?string $title = 'Vehicle Gate In';

    protected string $view = 'filament.pages.gate-in';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vehicle_id')
                    ->label('Vehicle Number')
                    ->options(fn() => Vehicle::pluck('vehicle_number', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('vehicle_number')->required()->unique('vehicles', 'vehicle_number'),
                    ])
                    ->createOptionUsing(fn(array $data) => Vehicle::create($data)->id),

                Select::make('driver_id')
                    ->label('Driver Name')
                    ->options(fn() => Driver::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($driver = Driver::find($state)) {
                            $set('driver_id_number', $driver->driver_id);
                            $set('phone_number', $driver->phone_number);
                        }
                    })
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('driver_id')->label('Driver ID')->required()->unique('drivers', 'driver_id'),
                        TextInput::make('phone_number')->label('Phone Number')->required()->tel(),
                    ])
                    ->createOptionUsing(fn(array $data) => Driver::create($data)->id),

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


        $alreadyIn = GateLog::where('vehicle_id', $data['vehicle_id'])
            ->whereNull('time_out')
            ->exists();

        if ($alreadyIn) {
            Notification::make()
                ->title('This vehicle is already gated in.')
                ->danger()
                ->send();
            return;
        }

        GateLog::create([
            'vehicle_id' => $data['vehicle_id'],
            'driver_id' => $data['driver_id'],
            'time_in' => now(),
            'gated_in_by' => auth()->id(),
        ]);

        Notification::make()
            ->title('Vehicle gated in successfully.')
            ->success()
            ->send();

        $this->form->fill();
    }
    public function gateInAction(): Action
    {
        return Action::make('gateIn')
            ->label('Gate In')
            ->action(fn() => $this->create());
    }
}
