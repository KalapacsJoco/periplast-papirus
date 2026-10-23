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
                // TextInput::make('open_mm')
                //     ->numeric()
                //     ->suffix('mm'),
                TextInput::make('screw_diameter_mm')
                    ->numeric()
                    ->suffix('mm'),
                TextInput::make('opening')
                    ->numeric()
                    ->suffix('mm'),
                TextInput::make('closing')
                    ->numeric()
                    ->suffix('mm'),
                TextInput::make('pressure')
                    ->numeric()
                    ->suffix('bar'),
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
                Select::make('moulds')
                    ->relationship('moulds', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->open_mm} mm)")
                    // ->modifyQueryUsing(fn ($query, $get) => $query->when(
                    //     filled($get('open_mm')),
                    //     fn ($q) => $q->where('open_mm', '<=', (float) $get('open_mm'))
                    // )
                    ,
                Textarea::make('problem_description')
                    ->label('Problem Description')
                    ->placeholder('Describe the issue or maintenance needed...')
                    ->columnSpanFull()
                    ->visible(fn ($get) => in_array($get('status'), ['warning', 'broken'])),
            ]);
    }
}
