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
                        'active' => 'Active (Working on order)',
                        'stopped' => 'Stopped (Order expired/other)',
                        'warning' => 'Warning (Maintenance needed soon)',
                        'broken' => 'Broken (Not operational)',
                    ])
                    ->required()
                    ->default('active'),
                Textarea::make('problem_description')
                    ->label('Problem Description')
                    ->placeholder('Describe the issue or maintenance needed...')
                    ->columnSpanFull()
                    ->visible(fn ($get) => in_array($get('status'), ['warning', 'broken'])),
            ]);
    }
}
