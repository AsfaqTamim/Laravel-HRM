<?php

namespace App\Filament\Pages\Reports;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class AttendanceReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Attendance';
    protected static ?string $navigationLabel = 'Attendance';
    protected static ?int $navigationSort = 2;

    public ?int $month = null;
    public ?int $year = null;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::where('is_active', true))
            ->columns([
                TextColumn::make('name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee_code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('present_days')
                    ->label('Present')
                    ->getStateUsing(fn (User $record): int => $record->attendanceRecords()
                        ->whereMonth('date', $this->month)
                        ->whereYear('date', $this->year)
                        ->where('status', 'present')
                        ->count())
                    ->alignCenter(),
                TextColumn::make('late_days')
                    ->label('Late')
                    ->getStateUsing(fn (User $record): int => $record->attendanceRecords()
                        ->whereMonth('date', $this->month)
                        ->whereYear('date', $this->year)
                        ->where('status', 'late')
                        ->count())
                    ->alignCenter()
                    ->color('warning'),
                TextColumn::make('half_days')
                    ->label('Half Day')
                    ->getStateUsing(fn (User $record): int => $record->attendanceRecords()
                        ->whereMonth('date', $this->month)
                        ->whereYear('date', $this->year)
                        ->where('status', 'half_day')
                        ->count())
                    ->alignCenter()
                    ->color('gray'),
                TextColumn::make('absent_days')
                    ->label('Absent')
                    ->getStateUsing(fn (User $record): int => $record->attendanceRecords()
                        ->whereMonth('date', $this->month)
                        ->whereYear('date', $this->year)
                        ->where('status', 'absent')
                        ->count())
                    ->alignCenter()
                    ->color('danger'),
                TextColumn::make('total')
                    ->label('Total')
                    ->getStateUsing(fn (User $record): int => $record->attendanceRecords()
                        ->whereMonth('date', $this->month)
                        ->whereYear('date', $this->year)
                        ->count())
                    ->alignCenter(),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public function getView(): string
    {
        return 'filament.pages.reports.attendance-report';
    }

    public function getTitle(): string
    {
        $monthName = now()->setMonth((int)($this->month ?? now()->month))->format('F');
        return "Attendance Report — {$monthName} {$this->year}";
    }

    public static function getNavigationLabel(): string
    {
        return 'Attendance';
    }
}
