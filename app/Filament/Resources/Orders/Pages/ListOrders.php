<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /** @var array<string, list<string>> */
    private const STATUS_GROUPS = [
        'waiting' => ['waiting', 'pending'],
        'active' => ['in_progress', 'in-progress', 'paused'],
        'interrupted' => ['interrupted', 'cancelled', 'canceled'],
        'completed' => ['completed'],
    ];

    public function getTabs(): array
    {
        return [
            'waiting' => $this->makeStatusTab('Várakozó megrendelők', self::STATUS_GROUPS['waiting']),
            'active' => $this->makeStatusTab('Aktív megrendelők', self::STATUS_GROUPS['active']),
            'interrupted' => $this->makeStatusTab('Megszakított megrendelők', self::STATUS_GROUPS['interrupted']),
            'completed' => $this->makeStatusTab('Befejezett megrendelők', self::STATUS_GROUPS['completed']),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    private function makeStatusTab(string $label, array $statuses): Tab
    {
        return Tab::make($label)
            ->badge(fn (): int => OrderResource::getEloquentQuery()->whereIn('status', $statuses)->count())
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', $statuses));
    }
}
