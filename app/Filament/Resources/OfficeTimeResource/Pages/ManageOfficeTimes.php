<?php

namespace App\Filament\Resources\OfficeTimeResource\Pages;

use App\Filament\Resources\OfficeTimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOfficeTimes extends ManageRecords
{
    protected static string $resource = OfficeTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
