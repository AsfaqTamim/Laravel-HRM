<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveBalanceResource\Pages;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveBalanceResource extends Resource
{
    protected static ?string $model = LeaveBalance::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Leave';

    protected static ?string $navigationLabel = 'Leave Balances';

    protected static ?string $pluralLabel = 'Leave Balances';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leave Balance')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(User::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('leave_type_id')
                            ->label('Leave Type')
                            ->options(LeaveType::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->disabledOn('edit'),

                        Forms\Components\TextInput::make('year')
                            ->label('Year')
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2100)
                            ->default(now()->year)
                            ->disabledOn('edit'),

                        Forms\Components\TextInput::make('allocated_days')
                            ->label('Allocated Days')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->suffix('days'),

                        Forms\Components\TextInput::make('used_days')
                            ->label('Used Days')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->suffix('days'),

                        Forms\Components\Placeholder::make('remaining_days')
                            ->label('Remaining Days')
                            ->content(fn (LeaveBalance $record): string => (string) $record->remaining_days)
                            ->visible(fn (string $operation) => $operation === 'edit'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Employee')
                    ->description(fn (LeaveBalance $record) => $record->employee?->employee_code ?? ''),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->badge()
                    ->color('gray')
                    ->label('Leave Type'),

                Tables\Columns\TextColumn::make('year')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('allocated_days')
                    ->label('Allocated')
                    ->suffix('d')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('used_days')
                    ->label('Used')
                    ->suffix('d')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn (float $state, LeaveBalance $record): string =>
                        $state > $record->allocated_days ? 'danger' : ''),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('Remaining')
                    ->suffix('d')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn (float $state, LeaveBalance $record): string =>
                        $state <= 0 ? 'danger' : ($state <= ($record->allocated_days / 2) ? 'warning' : 'success')),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->options(fn () => LeaveBalance::distinct()->pluck('year', 'year')->sortDesc())
                    ->default(now()->year)
                    ->native(false),

                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(User::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->native(false),

                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->options(LeaveType::where('is_active', true)->pluck('name', 'id'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('initialize')
                    ->label('Init')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->tooltip('Initialize balance from leave type defaults')
                    ->action(function (LeaveBalance $record) {
                        $type = LeaveType::find($record->leave_type_id);
                        if ($type) {
                            $record->update(['allocated_days' => $type->default_days_per_year]);
                            Notification::make()
                                ->title('Balance initialized from leave type')
                                ->success()
                                ->send();
                        }
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveBalances::route('/'),
        ];
    }
}
