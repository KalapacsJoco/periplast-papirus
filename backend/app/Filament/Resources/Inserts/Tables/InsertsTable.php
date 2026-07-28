<?php

namespace App\Filament\Resources\Inserts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InsertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mould.name')
                    ->label('Mould')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('product.code')
                    ->label('Product cikkszam')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('number')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'       => 'success',
                        'standby'      => 'gray',
                        'warning'      => 'warning',
                        'needs_repair' => 'danger',
                        'under_repair' => 'info',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'       => 'Aktiv',
                        'standby'      => 'Keszenletben',
                        'warning'      => 'Figyelmeztetés',
                        'needs_repair' => 'Javitasra szorul',
                        'under_repair' => 'Javitas alatt',
                        default        => $state,
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
