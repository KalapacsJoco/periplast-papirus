<?php

namespace App\Filament\Resources\Moulds\Pages;

use App\Filament\Resources\Moulds\MouldResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMould extends EditRecord
{
    protected static string $resource = MouldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
