<?php

namespace App\Filament\Resources\Errors\Pages;

use App\Filament\Resources\Errors\ErrorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListErrors extends ListRecords
{
    protected static string $resource = ErrorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
