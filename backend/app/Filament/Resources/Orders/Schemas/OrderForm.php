<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->unique(ignoreRecord: true)
                    ->prefix('ORD-')
                    ->required(),
                Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->required(),
                Select::make('mould_id')
                    ->relationship('mould', 'name')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                TextInput::make('cycle_time')
                    ->label('Cycle Time (ms)')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->helperText('Cycle time in milliseconds'),
                TextInput::make('net_weight')
                    ->numeric()
                    ->step('0.001')
                    ->minValue(0)
                    ->required()
                    ->suffix('g')
                    ->helperText('Product weight only'),
                TextInput::make('gross_weight')
                    ->numeric()
                    ->step('0.001')
                    ->minValue(0)
                    ->required()
                    ->suffix('g')
                    ->helperText('Product weight + waste'),
                TextInput::make('color_percentage')
                    ->numeric()
                    ->step('0.01')
                    ->minValue(0)
                    ->suffix('%')
                    ->helperText('Percentage relative to product weight'),
                TextInput::make('additives_percentages')
                    ->helperText('JSON array: [{"additive_id": 1, "percentage": 5.5}, ...]')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'in_progress' => 'In Progress',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                    ])
                    ->required()
                    ->default('waiting')
                    ->live(),
                Textarea::make('pause_description')
                    ->visible(fn(callable $get) => $get('status') === 'paused')
                    ->helperText('Reason for pausing the order')
                    ->columnSpanFull(),
            ]);
    }
}
