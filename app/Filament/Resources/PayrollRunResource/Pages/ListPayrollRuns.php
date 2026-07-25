<?php

namespace App\Filament\Resources\PayrollRunResource\Pages;

use App\Filament\Resources\PayrollRunResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListPayrollRuns extends ListRecords
{
    protected static string $resource = PayrollRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn ($q) => $q->where('status', 'draft')),
            'finalized' => Tab::make('Finalized')
                ->modifyQueryUsing(fn ($q) => $q->whereIn('status', ['finalized', 'paid'])),
            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(fn ($q) => $q->where('status', 'paid')),
        ];
    }
}
