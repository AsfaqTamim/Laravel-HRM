<?php

namespace App\Filament\Resources\Transactions\Schema;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class TransactionTable
{
    public static function getSchema(): array
    {
        return [
            TextColumn::make('type')
                ->badge()
                ->color(fn (string $state) => $state === 'income' ? 'success' : 'danger')
                ->formatStateUsing(fn (string $state) => $state === 'income' ? 'Income' : 'Expense')
                ->sortable(),

            TextColumn::make('account.name')
                ->label('Account')
                ->badge()
                ->searchable()
                ->sortable(),

            TextColumn::make('title')
                ->searchable()
                ->sortable(),

            TextColumn::make('amount')
                ->money('BDT')
                ->sortable(),

            TextColumn::make('date')
                ->date()
                ->sortable(),

            ImageColumn::make('receipt')
                ->square()
                ->size(40)
                ->defaultImageUrl(null),

            TextColumn::make('createdBy.name')
                ->label('Recorded By')
                ->sortable(),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('type')
                ->options(['income' => 'Income', 'expense' => 'Expense']),

            SelectFilter::make('account_id')
                ->label('Account')
                ->relationship('account', 'name')
                ->searchable()
                ->preload(),

            Filter::make('date_range')
                ->form([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])
                ->query(fn (Builder $q, array $data) => $q
                    ->when($data['from'], fn ($q, $d) => $q->where('date', '>=', $d))
                    ->when($data['until'], fn ($q, $d) => $q->where('date', '<=', $d))),
        ];
    }
}
