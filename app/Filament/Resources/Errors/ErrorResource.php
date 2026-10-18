<?php

namespace App\Filament\Resources\Errors;

use App\Filament\Resources\Errors\Pages\CreateError;
use App\Filament\Resources\Errors\Pages\EditError;
use App\Filament\Resources\Errors\Pages\ListErrors;
use App\Filament\Resources\Errors\Schemas\ErrorForm;
use App\Filament\Resources\Errors\Tables\ErrorsTable;
use App\Models\Error;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ErrorResource extends Resource
{
    protected static ?string $model = Error::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ErrorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ErrorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Error belongs to Machine
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListErrors::route('/'),
            'create' => CreateError::route('/create'),
            'edit' => EditError::route('/{record}/edit'),
        ];
    }
}
