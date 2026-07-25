<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        $user = auth()->user();

        if ($user && $user->hasRole('super_admin')) {
            return [
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\PendingLeaveWidget::class,
                \App\Filament\Widgets\LatestTasksWidget::class,
            ];
        }

        // Employee view
        return [
            \App\Filament\Widgets\AttendanceCheckWidget::class,
            \App\Filament\Widgets\EmployeeStatsWidget::class,
            \App\Filament\Widgets\MyTasksWidget::class,
            \App\Filament\Widgets\MyLeavesWidget::class,
        ];
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }
}
