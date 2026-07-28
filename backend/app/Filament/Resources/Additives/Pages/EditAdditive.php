<?php

namespace App\Filament\Resources\Additives\Pages;

use App\Filament\Resources\Additives\AdditiveResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdditive extends EditRecord
{
    protected static string $resource = AdditiveResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
