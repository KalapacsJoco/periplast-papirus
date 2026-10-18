<?php

namespace App\Filament\Resources\Moulds\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MouldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('products')
                    ->relationship('products', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('standard'),
                TextInput::make('number')
                    ->required(),
                TextInput::make('cavity_count')
                    ->label('Feszek szama')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('open_mm')
                    ->label('Nyitas (mm)')
                    ->numeric()
                    ->suffix('mm')
                    ->required(),
                Select::make('machines')
                    ->relationship('machines', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->open_mm} mm)")
                    ->modifyQueryUsing(fn ($query, $get) => $query->when(
                        filled($get('open_mm')),
                        fn ($q) => $q->where('open_mm', '>=', (float) $get('open_mm'))
                    )),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'active'      => 'Aktiv',
                        'standby'     => 'Keszenletben',
                        'warning'     => 'Figyelmeztetés',
                        'needs_repair' => 'Javitasra szorul',
                        'under_repair' => 'Javitas alatt',
                    ])
                    ->required()
                    ->default('standby'),
            ]);
    }
}
