<?php

namespace App\Filament\Resources\Moulds;

use App\Filament\Resources\Moulds\Pages\CreateMould;
use App\Filament\Resources\Moulds\Pages\EditMould;
use App\Filament\Resources\Moulds\Pages\ListMoulds;
use App\Filament\Resources\Moulds\Schemas\MouldForm;
use App\Filament\Resources\Moulds\Tables\MouldsTable;
use App\Models\Mould;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MouldResource extends Resource
{
    protected static ?string $model = Mould::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MouldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MouldsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Mould belongs to many Machines and Orders
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMoulds::route('/'),
            'create' => CreateMould::route('/create'),
            'edit' => EditMould::route('/{record}/edit'),
        ];
    }
}
