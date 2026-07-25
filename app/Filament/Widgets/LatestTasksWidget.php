<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTasksWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::with('assignee', 'project')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Task')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('assignee.name')
                    ->label('Assigned To'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'pending' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('due_date')
                    ->label('Due')
                    ->date(),
            ])
            ->heading('Latest Tasks')
            ->defaultSort('created_at', 'desc');
    }
}
