<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
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
                Select::make('additives')
                    ->relationship('additives', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
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
