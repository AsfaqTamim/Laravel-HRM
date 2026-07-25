<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayAttendanceWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AttendanceRecord::today()
                    ->with('employee.department')
                    ->latest()
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable(),
                TextColumn::make('employee.department.name')
                    ->label('Department'),
                TextColumn::make('check_in')
                    ->label('Check In')
                    ->time('H:i')
                    ->placeholder('—'),
                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->time('H:i')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'half_day' => 'gray',
                        'absent' => 'danger',
                        'holiday' => 'info',
                        'leave' => 'info',
                        'weekend' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('late_minutes')
                    ->label('Late (min)')
                    ->alignCenter()
                    ->placeholder('0'),
                TextColumn::make('overtime_minutes')
                    ->label('OT (min)')
                    ->alignCenter()
                    ->placeholder('0'),
            ])
            ->heading("Today's Attendance (" . now()->format('d M Y') . ')')
            ->defaultSort('check_in', 'asc');
    }
}
