<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('machine.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mould.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cycle_time')
                    ->label('Cycle (ms)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('net_weight')
                    ->label('Net Weight (g)')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                TextColumn::make('gross_weight')
                    ->label('Gross Weight (g)')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'in_progress',
                        'danger' => 'paused',
                        'success' => 'completed',
                    ]),
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

