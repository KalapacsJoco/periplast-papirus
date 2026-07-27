<?php

namespace App\Filament\Resources\Machines\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MachineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('open_mm')
                    ->numeric()
                    ->suffix('mm'),
                TextInput::make('screw_diameter_cm')
                    ->numeric()
                    ->suffix('cm'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'maintenance' => 'In Maintenance',
                    ])
                    ->required()
                    ->default('active'),
            ]);
    }
}
