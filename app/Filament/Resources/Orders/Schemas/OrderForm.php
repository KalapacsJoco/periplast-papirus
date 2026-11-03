<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Additive;
use App\Models\Color;
use App\Models\Material;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

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
                DatePicker::make('date')
                    ->label('Expected production start')
                    ->default(now())
                    ->required(),
                DatePicker::make('planned_end_date')
                    ->label('Expected production end')
                    ->afterOrEqual('date'),
                TextInput::make('customer')
                // ->required()
                ,
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->live()
                    ->afterStateUpdated(function (string|int|null $state, Set $set): void {
                        self::applyProductDefaults($state, $set);
                    })
                    ->required(),
                Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->required(),
                Select::make('mould_id')
                    ->label('Szerszám')
                    ->options(fn (Get $get): array => self::mouldOptions($get))
                    ->visible(fn (Get $get): bool => filled($get('product_id')) && count(self::mouldOptions($get)) !== 1)
                    ->dehydratedWhenHidden()
                    ->required(fn (Get $get): bool => filled($get('product_id')))
                    ->disabled(fn (Get $get): bool => self::mouldOptions($get) === [])
                    ->rules(fn (Get $get): array => filled($get('product_id'))
                        ? [Rule::exists('mould_product', 'mould_id')->where('product_id', $get('product_id'))]
                        : [])
                    ->helperText(fn (Get $get): ?string => match (count(self::mouldOptions($get))) {
                        0 => 'A kiválasztott termékhez még nincs szerszám rendelve.',
                        1 => 'A termék egyetlen szerszáma automatikusan kiválasztva.',
                        default => 'Válassza ki, melyik szerszámmal készüljön a termék.',
                    }),
                Select::make('color')
                    ->label('Szín')
                    ->options(fn () => Color::query()->pluck('code', 'code')->toArray())
                    ->searchable()
                    ->nullable(),
                Select::make('material')
                    ->label('Anyag')
                    ->options(fn () => Material::query()->pluck('code', 'code')->toArray())
                    ->searchable()
                    ->nullable(),
                TextInput::make('quantity')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
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
                    ->label('Szín aránya')
                    ->numeric()
                    ->step('0.01')
                    ->minValue(0)
                    ->suffix('%')
                    ->helperText('Percentage relative to product weight'),
                Repeater::make('additives_percentages')
                    ->label('Adalékanyagok')
                    ->schema([
                        Select::make('additive_id')
                            ->label('Adalékanyag')
                            ->options(fn (): array => Additive::query()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required(),
                        TextInput::make('percentage')
                            ->label('Arány')
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0)
                            ->suffix('%'),
                    ])
                    ->columns(2)
                    ->default([])
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
                    ->visible(fn (callable $get) => $get('status') === 'paused')
                    ->helperText('Reason for pausing the order')
                    ->columnSpanFull(),
            ]);
    }

    private static function mouldOptions(Get $get): array
    {
        $productId = $get('product_id');

        if (blank($productId)) {
            return [];
        }

        return Product::query()
            ->find($productId)
            ?->moulds()
            ->pluck('name', 'moulds.id')
            ->toArray() ?? [];
    }

    public static function applyProductDefaults(string|int|null $productId, Set $set): void
    {
        $product = filled($productId)
            ? Product::query()->with(['material', 'color', 'moulds', 'additiveSettings'])->find($productId)
            : null;

        $set('material', $product?->material?->code);
        $set('color', $product?->color?->code);
        $set('color_percentage', $product?->color_percentage);
        $set('additives_percentages', $product?->additiveSettings
            ->map(fn ($setting): array => [
                'additive_id' => $setting->additive_id,
                'percentage' => $setting->percentage,
            ])
            ->all() ?? []);
        $moulds = $product?->moulds;
        $set('mould_id', $moulds?->count() === 1 ? $moulds->first()->id : null);
    }
}
