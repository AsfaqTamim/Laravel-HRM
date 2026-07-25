<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class MyLeavesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Leave::forEmployee(Auth::id())
                    ->with('leaveType')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('leaveType.name')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('start_date')
                    ->label('From')
                    ->date(),
                TextColumn::make('end_date')
                    ->label('To')
                    ->date(),
                TextColumn::make('total_days')
                    ->label('Days')
                    ->alignCenter(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->heading('My Leaves')
            ->defaultSort('created_at', 'desc');
    }
}
