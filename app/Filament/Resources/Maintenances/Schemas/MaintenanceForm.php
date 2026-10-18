<?php

namespace App\Filament\Resources\Maintenances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MaintenanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->searchable()
                    ->preload()
                    ->rules(['required_without:mould_id']),
                Select::make('mould_id')
                    ->relationship('mould', 'name')
                    ->searchable()
                    ->preload()
                    ->rules(['required_without:machine_id']),
                DatePicker::make('date')
                    ->required(),
                Select::make('type')
                    ->options([
                        'preventive' => 'Preventive',
                        'corrective' => 'Corrective',
                    ])
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }
}
