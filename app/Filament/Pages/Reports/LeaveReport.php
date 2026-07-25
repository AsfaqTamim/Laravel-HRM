<?php

namespace App\Filament\Pages\Reports;

use App\Models\Leave;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class LeaveReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Leave';
    protected static ?string $navigationLabel = 'Report';
    protected static ?int $navigationSort = 3;

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = now()->year;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Leave::query()
                    ->whereYear('start_date', $this->year ?? now()->year)
                    ->with('employee', 'leaveType')
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.employee_code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Sick') => 'danger',
                        str_contains($state, 'Annual') || str_contains($state, 'Casual') => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('total_days')
                    ->label('Days')
                    ->getStateUsing(fn (Leave $record): float => $record->start_date->diffInDays($record->end_date) + 1)
                    ->alignCenter(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('start_date')
                    ->label('From')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('To')
                    ->date()
                    ->sortable(),
                TextColumn::make('reason')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->striped();
    }

    public function getView(): string
    {
        return 'filament.pages.reports.leave-report';
    }

    public function getTitle(): string
    {
        return "Leave Report — {$this->year}";
    }

    public static function getNavigationLabel(): string
    {
        return 'Leaves';
    }
}
