<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceRecord;
use App\Models\Leave;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        $pendingTasks = Task::where('assigned_to', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $myLeaves = Leave::forEmployee($userId)->count();

        $todayAttendance = AttendanceRecord::today()
            ->where('employee_id', $userId)
            ->first();

        $todayStatus = $todayAttendance
            ? ucfirst($todayAttendance->status)
            : 'Not marked';

        return [
            Stat::make('My Pending Tasks', $pendingTasks)
                ->description($pendingTasks > 0 ? 'Needs attention' : 'All clear ✓')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($pendingTasks > 0 ? 'warning' : 'success'),

            Stat::make('My Leaves', $myLeaves)
                ->description('Total applications')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Today', $todayStatus)
                ->description($todayAttendance?->check_in ?? 'No check-in yet')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($todayAttendance && $todayAttendance->status === 'present' ? 'success' : 'gray'),
        ];
    }
}
