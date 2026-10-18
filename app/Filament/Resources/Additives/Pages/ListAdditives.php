<?php

namespace App\Filament\Resources\Additives\Pages;

use App\Filament\Resources\Additives\AdditiveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdditives extends ListRecords
{
    protected static string $resource = AdditiveResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
