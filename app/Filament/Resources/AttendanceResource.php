<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\AttendanceRecord;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Attendance';

    protected static ?string $navigationLabel = 'Records';

    protected static ?string $pluralLabel = 'Attendance Records';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Employees see only their own records; admins see all
        if (! auth()->user()?->hasRole('super_admin')) {
            $query->where('employee_id', auth()->id());
        }

        return $query->latest('date')->latest('check_in');
    }

    public static function form(Form $form): Form
    {
        $isAdmin = auth()->user()?->hasRole('super_admin');

        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Record')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(User::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->disabled(! $isAdmin)
                            ->hidden(! $isAdmin)
                            ->default(fn () => $isAdmin ? null : auth()->id()),

                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('check_in')
                            ->label('Check In')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('check_out')
                            ->label('Check Out')
                            ->seconds(false)
                            ->after('check_in'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'late' => 'Late',
                                'half_day' => 'Half Day',
                                'absent' => 'Absent',
                            ])
                            ->required()
                            ->default('present')
                            ->native(false),

                        Forms\Components\TextInput::make('late_minutes')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('min'),

                        Forms\Components\TextInput::make('overtime_minutes')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('min'),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = auth()->user()?->hasRole('super_admin');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Employee'),

                Tables\Columns\TextColumn::make('date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->dateTime('H:i')
                    ->sortable()
                    ->label('In')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('check_out')
                    ->dateTime('H:i')
                    ->sortable()
                    ->label('Out')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'half_day' => 'danger',
                        'absent' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('late_minutes')
                    ->label('Late')
                    ->suffix(' min')
                    ->color(fn (int $state) => $state > 0 ? 'danger' : '')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('overtime_minutes')
                    ->label('OT')
                    ->suffix(' min')
                    ->color(fn (int $state) => $state > 0 ? 'success' : '')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('break_minutes')
                    ->label('Break')
                    ->suffix(' min')
                    ->alignCenter(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(User::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->hidden(! $isAdmin),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'late' => 'Late',
                        'half_day' => 'Half Day',
                        'absent' => 'Absent',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn ($q, $d) => $q->whereDate('date', '>=', $d))
                        ->when($data['until'], fn ($q, $d) => $q->whereDate('date', '<=', $d)))
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible($isAdmin),
                Tables\Actions\DeleteAction::make()
                    ->visible($isAdmin),
            ])
            ->bulkActions($isAdmin ? [
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ] : []);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAttendance::route('/'),
        ];
    }
}
