<?php

namespace App\Filament\Resources\SalaryStructureResource\Pages;

use App\Filament\Resources\SalaryStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSalaryStructures extends ManageRecords
{
    protected static string $resource = SalaryStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
