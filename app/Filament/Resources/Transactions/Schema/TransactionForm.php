<?php

namespace App\Filament\Resources\Transactions\Schema;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;

class TransactionForm
{
    public static function getSchema(): array
    {
        return [
            ToggleButtons::make('type')
                ->options(['income' => 'Income', 'expense' => 'Expense'])
                ->icons([
                    'income' => 'heroicon-o-arrow-trending-up',
                    'expense' => 'heroicon-o-arrow-trending-down',
                ])
                ->colors(['income' => 'success', 'expense' => 'danger'])
                ->inline()
                ->required()
                ->live()
                ->default('income'),

            Select::make('account_id')
                ->label('Account')
                ->relationship(
                    'account',
                    'name',
                    fn ($query, Get $get) => $query->active()->where('type', $get('type'))
                )
                ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->code}] {$record->name}")
                ->required()
                ->live()
                ->native(false)
                ->createOptionForm([
                    Select::make('type')
                        ->options(['income' => 'Income', 'expense' => 'Expense'])
                        ->required()
                        ->native(false)
                        ->default(fn (Get $get) => $get('../../type')),
                    TextInput::make('code')->required()->maxLength(20),
                    TextInput::make('name')->required()->maxLength(255),
                    ToggleButtons::make('is_active')
                        ->options(['1' => 'Active'])
                        ->boolean()
                        ->default(true),
                ])
                ->createOptionUsing(fn (array $data) => \App\Models\Account::create($data)),

            TextInput::make('title')
                ->required()
                ->maxLength(255),

            TextInput::make('amount')
                ->numeric()
                ->required()
                ->prefix('৳')
                ->minValue(0),

            DatePicker::make('date')
                ->default(now())
                ->required(),

            Textarea::make('description')
                ->nullable()
                ->rows(3),

            FileUpload::make('receipt')
                ->image()
                ->directory('receipts')
                ->imagePreviewHeight(150)
                ->maxSize(2048)
                ->nullable()
                ->label('Receipt / Proof'),

            Select::make('created_by')
                ->relationship('createdBy', 'name')
                ->default(fn () => auth()->id())
                ->disabled()
                ->dehydrated()
                ->label('Recorded By'),
        ];
    }
}
