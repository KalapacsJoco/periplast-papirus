<?php

namespace App\Filament\Resources\Inserts\Pages;

use App\Filament\Resources\Inserts\InsertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInserts extends ListRecords
{
    protected static string $resource = InsertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
