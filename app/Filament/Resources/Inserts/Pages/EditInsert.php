<?php

namespace App\Filament\Resources\Inserts\Pages;

use App\Filament\Resources\Inserts\InsertResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInsert extends EditRecord
{
    protected static string $resource = InsertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
