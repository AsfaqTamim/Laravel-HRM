<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class FinanceOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $now = now();
        $monthStart = $now->startOfMonth()->format('Y-m-d');
        $monthEnd = $now->endOfMonth()->format('Y-m-d');
        $today = $now->format('Y-m-d');

        // This month
        $monthIncome = (float) Transaction::income()->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');
        $monthExpense = (float) Transaction::expense()->whereBetween('date', [$monthStart, $monthEnd])->sum('amount');

        // Today
        $todayIncome = (float) Transaction::income()->where('date', $today)->sum('amount');
        $todayExpense = (float) Transaction::expense()->where('date', $today)->sum('amount');

        // Overall
        $totalIncome = (float) Transaction::income()->sum('amount');
        $totalExpense = (float) Transaction::expense()->sum('amount');

        return [
            Stat::make('This Month Income', '৳ ' . Number::format($monthIncome, precision: 2))
                ->description('vs ' . '৳ ' . Number::format($todayIncome, precision: 2) . ' today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('This Month Expense', '৳ ' . Number::format($monthExpense, precision: 2))
                ->description('vs ' . '৳ ' . Number::format($todayExpense, precision: 2) . ' today')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Net Balance', '৳ ' . Number::format($totalIncome - $totalExpense, precision: 2))
                ->description('Total Income: ৳' . Number::format($totalIncome, precision: 0) . ' · Expense: ৳' . Number::format($totalExpense, precision: 0))
                ->descriptionIcon('heroicon-m-scale')
                ->color(($totalIncome - $totalExpense) >= 0 ? 'success' : 'danger'),
        ];
    }
}
