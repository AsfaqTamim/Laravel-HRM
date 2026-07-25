<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PendingLeaveWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Leave::pending()
                    ->with('employee', 'leaveType')
                    ->latest()
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable(),
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
                TextColumn::make('reason')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Leave $record): void {
                        $record->approve(Auth::id());
                        Notification::make()
                            ->success()
                            ->title("Leave approved for {$record->employee->name}")
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Rejection reason'),
                    ])
                    ->action(function (Leave $record, array $data): void {
                        $record->reject(Auth::id(), $data['notes'] ?? null);
                        Notification::make()
                            ->warning()
                            ->title("Leave rejected for {$record->employee->name}")
                            ->send();
                    }),
            ])
            ->heading('Pending Leave Requests')
            ->defaultSort('created_at', 'desc');
    }
}
