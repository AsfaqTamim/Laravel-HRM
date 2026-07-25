<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';

    protected static ?string $navigationGroup = 'Leave';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Leave Requests';

    protected static ?string $pluralLabel = 'Leave Requests';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leave Request')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(User::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->default(fn () => Auth::user()?->id),

                        Forms\Components\Select::make('leave_type_id')
                            ->label('Leave Type')
                            ->options(LeaveType::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $type = LeaveType::find($state);
                                    $set('leave_type_name', $type?->name ?? '');
                                }
                            }),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set, callable $get, $state) =>
                                self::updateTotalDays($set, $get)),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_date')
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set, callable $get, $state) =>
                                self::updateTotalDays($set, $get)),

                        Forms\Components\TextInput::make('total_days')
                            ->label('Total Days')
                            ->numeric()
                            ->required()
                            ->dehydrated()
                            ->disabled()
                            ->suffix('day(s)'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(2000)
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Please describe the reason for leave...'),

                        Forms\Components\Hidden::make('status')
                            ->default('pending'),
                    ])->columns(2),
            ]);
    }

    protected static function updateTotalDays(callable $set, callable $get): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        if ($start && $end) {
            try {
                $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
                $set('total_days', $days);
            } catch (\Exception $e) {
                $set('total_days', 0);
            }
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Employee')
                    ->description(fn (Leave $record) => $record->employee?->employee_code ?? ''),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->badge()
                    ->color('gray')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('From'),

                Tables\Columns\TextColumn::make('end_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('To'),

                Tables\Columns\TextColumn::make('total_days')
                    ->label('Days')
                    ->suffix('d')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Approved At')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(User::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->native(false),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->options(LeaveType::pluck('name', 'id'))
                    ->native(false),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn ($q, $d) => $q->whereDate('start_date', '>=', $d))
                        ->when($data['until'], fn ($q, $d) => $q->whereDate('end_date', '<=', $d)))
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Leave $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Leave $record) {
                        $record->approve(Auth::id());
                        \Illuminate\Support\Facades\Mail::to($record->employee->email)
                            ->queue(new \App\Mail\LeaveStatusMail($record, 'approved'));
                        Notification::make()
                            ->title('Leave approved successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Leave $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (Leave $record, array $data) {
                        $record->reject(Auth::id(), $data['notes']);
                        \Illuminate\Support\Facades\Mail::to($record->employee->email)
                            ->queue(new \App\Mail\LeaveStatusMail($record, 'rejected'));
                        Notification::make()
                            ->title('Leave rejected')
                            ->danger()
                            ->send();
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
