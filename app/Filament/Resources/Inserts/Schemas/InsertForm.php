<?php

namespace App\Filament\Resources\Inserts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InsertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('mould_id')
                    ->relationship('mould', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'code')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('number')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'active'       => 'Aktiv',
                        'standby'      => 'Keszenletben',
                        'warning'      => 'Figyelmeztetés',
                        'needs_repair' => 'Javitasra szorul',
                        'under_repair' => 'Javitas alatt',
                    ])
                    ->required()
                    ->default('standby'),
            ]);
    }
}
