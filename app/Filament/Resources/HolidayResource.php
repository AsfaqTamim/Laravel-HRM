<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?string $navigationLabel = 'Holidays';

    protected static ?string $pluralLabel = 'Holidays';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Holiday Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Holiday Name'),

                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'national' => 'National',
                                'religious' => 'Religious',
                                'observance' => 'Observance',
                            ])
                            ->default('national')
                            ->native(false),

                        Forms\Components\TextInput::make('year')
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2100)
                            ->default(now()->year),

                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Recurring (every year)')
                            ->default(false)
                            ->helperText('Enable if this holiday falls on the same date every year'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->columnSpanFull(),

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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'national' => 'success',
                        'religious' => 'warning',
                        'observance' => 'info',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('year')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_recurring')
                    ->boolean()
                    ->label('Recurring')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'national' => 'National',
                        'religious' => 'Religious',
                        'observance' => 'Observance',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('year')
                    ->options(fn () => collect(range(now()->year - 2, now()->year + 2))
                        ->mapWithKeys(fn ($y) => [$y => $y]))
                    ->default(now()->year)
                    ->native(false),

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
            'index' => Pages\ManageHolidays::route('/'),
        ];
    }
}
