<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\Reports;
use App\Filament\Resources\Transactions\Schema\TransactionForm;
use App\Filament\Resources\Transactions\Schema\TransactionTable;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(TransactionForm::getSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TransactionTable::getSchema())
            ->filters(TransactionTable::getFilters())
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
            'reports' => Reports::route('/reports'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationItems(): array
    {
        return [
            parent::getNavigationItems()[0],
            NavigationItem::make('Financial Reports')
                ->icon('heroicon-o-chart-bar-square')
                ->group('Finance')
                ->sort(2)
                ->url(static::getUrl('reports')),
        ];
    }
}
