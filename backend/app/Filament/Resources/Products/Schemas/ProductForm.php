<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                    ->unique(ignoreRecord: true),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
