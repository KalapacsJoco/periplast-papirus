<?php

namespace App\Filament\Resources\Inserts;

use App\Filament\Resources\Inserts\Pages\CreateInsert;
use App\Filament\Resources\Inserts\Pages\EditInsert;
use App\Filament\Resources\Inserts\Pages\ListInserts;
use App\Filament\Resources\Inserts\Schemas\InsertForm;
use App\Filament\Resources\Inserts\Tables\InsertsTable;
use App\Models\Insert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InsertResource extends Resource
{
    protected static ?string $model = Insert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    public static function form(Schema $schema): Schema
    {
        return InsertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InsertsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInserts::route('/'),
            'create' => CreateInsert::route('/create'),
            'edit' => EditInsert::route('/{record}/edit'),
        ];
    }
}
