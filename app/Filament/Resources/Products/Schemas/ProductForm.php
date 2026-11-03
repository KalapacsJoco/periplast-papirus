<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('customer_code'),
                Select::make('material_id')
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('color_id')
                    ->relationship('color', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('color_percentage')
                    ->label('Szín aránya')
                    ->numeric()
                    ->step('0.01')
                    ->minValue(0)
                    ->suffix('%'),
                Repeater::make('additiveSettings')
                    ->label('Adalékanyagok')
                    ->relationship()
                    ->schema([
                        Select::make('additive_id')
                            ->label('Adalékanyag')
                            ->relationship('additive', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('percentage')
                            ->label('Arány')
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0)
                            ->suffix('%'),
                    ])
                    ->columns(2)
                    ->defaultItems(0),
                Select::make('moulds')
                    ->relationship('moulds', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
