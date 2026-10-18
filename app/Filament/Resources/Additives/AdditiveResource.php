<?php

namespace App\Filament\Resources\Additives;

use App\Filament\Resources\Additives\Pages\CreateAdditive;
use App\Filament\Resources\Additives\Pages\EditAdditive;
use App\Filament\Resources\Additives\Pages\ListAdditives;
use App\Filament\Resources\Additives\Schemas\AdditiveForm;
use App\Filament\Resources\Additives\Tables\AdditivesTable;
use App\Models\Additive;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdditiveResource extends Resource
{
    protected static ?string $model = Additive::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    public static function form(Schema $schema): Schema
    {
        return AdditiveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdditivesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdditives::route('/'),
            'create' => CreateAdditive::route('/create'),
            'edit' => EditAdditive::route('/{record}/edit'),
        ];
    }
}
