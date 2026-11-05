<?php

namespace App\Filament\Resources\Moulds\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class MouldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('products')
                    ->label('Kompatibilis termékek')
                    ->relationship('products', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('A szerszám termék nélkül is létrehozható; a termékhez később is hozzárendelhető.'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('number')
                    ->required(),
                TextInput::make('cavity_count')
                    ->label('Feszek szama')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('active_cavityes')
                    ->label('Aktív fészkek száma')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(fn (callable $get) => $get('cavity_count') ?: null)
                    ->default(fn (callable $get) => $get('cavity_count'))
                    ->helperText('Üresen hagyva megegyezik a fészekszámmal; nem lehet nagyobb annál.'),
                TextInput::make('open_mm')
                    ->label('Nyitas (mm)')
                    ->numeric()
                    ->suffix('mm')
                    ->required(),
                TextInput::make('height_mm')
                    ->label('Szerszámmagasság (mm)')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->nullable(),

                TextInput::make('cooling_water_channel')
                    ->label('Hűtővízkörök száma')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->nullable(),

                Toggle::make('hot_channel')
                    ->label('Melegbeömlős szerszám')
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?bool $state): void {
                        if (! $state) {
                            $set('hot_channel_count', null);
                            $set('hot_channel_5a', false);
                            $set('hot_channel_220v', false);
                        }
                    }),

                TextInput::make('hot_channel_count')
                    ->label('Melegbeömlős csatornák száma')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->required(fn(Get $get): bool => (bool) $get('hot_channel'))
                    ->visible(fn(Get $get): bool => (bool) $get('hot_channel')),

                Toggle::make('hot_channel_5a')
                    ->label('5 A-es szabályozó')
                    ->default(false)
                    ->visible(fn(Get $get): bool => (bool) $get('hot_channel')),

                Toggle::make('hot_channel_220v')
                    ->label('220 V-os szabályozó')
                    ->default(false)
                    ->visible(fn(Get $get): bool => (bool) $get('hot_channel')),

                Toggle::make('temper')
                    ->label('Temperálás szükséges')
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?bool $state): void {
                        if (! $state) {
                            $set('temper_water_circuit', null);
                        }
                    }),

                TextInput::make('temper_water_circuit')
                    ->label('Temperálás helye / vízköre')
                    ->placeholder('Pl. Kidobó oldal, beömlő oldal, pofák')
                    ->maxLength(255)
                    ->required(fn(Get $get): bool => (bool) $get('temper'))
                    ->visible(fn(Get $get): bool => (bool) $get('temper')),
                Select::make('machines')
                    ->relationship('machines', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->open_mm} mm)")
                // ->modifyQueryUsing(fn ($query, $get) => $query->when(
                //     filled($get('open_mm')),
                //     fn ($q) => $q->where('open_mm', '>=', (float) $get('open_mm'))
                // ))
                ,
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
