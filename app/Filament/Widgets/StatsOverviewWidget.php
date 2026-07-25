<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceRecord;
use App\Models\Leave;
use App\Models\PayrollRun;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $totalEmployees = User::where('is_active', true)->count();

        $todayPresent = AttendanceRecord::today()
            ->whereIn('status', ['present', 'late'])
            ->count();

        $todayAbsent = AttendanceRecord::today()
            ->where('status', 'absent')
            ->count();

        $pendingLeaves = Leave::pending()->count();

        $latestPayroll = PayrollRun::latest()->first();
        $payrollLabel = $latestPayroll
            ? $latestPayroll->label . ' — ' . number_format($latestPayroll->total_net, 0) . ' BDT'
            : 'No runs yet';

        return [
            Stat::make('Active Employees', $totalEmployees)
                ->description('Total active workforce')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Present Today', $todayPresent)
                ->description($todayAbsent > 0
                    ? "{$todayAbsent} absent today"
                    : ($todayPresent > 0 ? 'All present ✓' : 'No attendance yet'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending Leaves', $pendingLeaves)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingLeaves > 0 ? 'warning' : 'success'),

            Stat::make('Latest Payroll', $payrollLabel)
                ->description($latestPayroll?->status ?? '—')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
