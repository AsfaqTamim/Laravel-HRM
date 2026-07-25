<?php

namespace App\Filament\Pages\Reports;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class EmployeeDirectory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Employees';
    protected static ?string $navigationLabel = 'Directory';
    protected static ?int $navigationSort = 2;

    public ?int $department_id = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->when($this->department_id, fn ($q) => $q->where('department_id', $this->department_id))
                    ->where('is_active', true)
                    ->with('department', 'designation')
            )
            ->columns([
                TextColumn::make('employee_code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('designation.name')
                    ->label('Designation')
                    ->sortable(),
                TextColumn::make('email')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->is_active ? 'Active' : 'Inactive')
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'danger'),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public function getView(): string
    {
        return 'filament.pages.reports.employee-directory';
    }

    public function getTitle(): string
    {
        return 'Employee Directory';
    }

    public static function getNavigationLabel(): string
    {
        return 'Directory';
    }
}
