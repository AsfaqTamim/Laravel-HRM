<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeTimeResource\Pages;
use App\Models\OfficeTime;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeTimeResource extends Resource
{
    protected static ?string $model = OfficeTime::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?int $navigationSort = 3;

    protected static ?string $pluralLabel = 'Office Times';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Office Time Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Shift Name')
                            ->placeholder('e.g. General Shift')
                            ->helperText('A descriptive name for this office time configuration'),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'default' => 'Default (Applies to All)',
                                'employee' => 'Employee-Based',
                            ])
                            ->default('default')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('employee_id', null))
                            ->native(false),

                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->options(User::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'employee')
                            ->required(fn (Forms\Get $get) => $get('type') === 'employee')
                            ->native(false),

                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->label('Office Start Time')
                            ->seconds(false)
                            ->default('09:00'),

                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->label('Office End Time')
                            ->seconds(false)
                            ->default('18:00'),

                        Forms\Components\TextInput::make('late_count')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(99)
                            ->default(0)
                            ->label('Allowed Late Count')
                            ->helperText('Number of late arrivals allowed before disciplinary action')
                            ->suffix('times'),

                        Forms\Components\TextInput::make('max_break_minutes')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(999)
                            ->default(60)
                            ->label('Maximum Break Time')
                            ->helperText('Maximum break duration per day')
                            ->suffix('minutes'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (OfficeTime $record) => match ($record->type) {
                        'default' => 'Default shift',
                        'employee' => $record->employee?->name ?? '—',
                        default => '',
                    }),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'default' => 'success',
                        'employee' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable()
                    ->label('Start'),

                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable()
                    ->label('End'),

                Tables\Columns\TextColumn::make('late_count')
                    ->badge()
                    ->color(fn (int $state) => match (true) {
                        $state === 0 => 'success',
                        $state <= 3 => 'warning',
                        default => 'danger',
                    })
                    ->label('Late Allowed')
                    ->suffix('×'),

                Tables\Columns\TextColumn::make('max_break_minutes')
                    ->label('Max Break')
                    ->suffix(' min'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->defaultSort('type', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'default' => 'Default',
                        'employee' => 'Employee-Based',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageOfficeTimes::route('/'),
        ];
    }
}
