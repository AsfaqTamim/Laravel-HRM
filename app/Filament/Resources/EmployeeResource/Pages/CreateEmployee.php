<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function afterCreate(): void
    {
        $role = $this->data['role'] ?? null;
        if ($role) {
            $this->record->assignRole($role);
        }
    }
}
