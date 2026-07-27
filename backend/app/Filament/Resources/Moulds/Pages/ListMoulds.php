<?php

namespace App\Filament\Resources\Moulds\Pages;

use App\Filament\Resources\Moulds\MouldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMoulds extends ListRecords
{
    protected static string $resource = MouldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
