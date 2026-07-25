<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryStructureResource\Pages;
use App\Models\SalaryStructure;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryStructureResource extends Resource
{
    protected static ?string $model = SalaryStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?string $navigationLabel = 'Salary Structures';
    protected static ?string $pluralLabel = 'Salary Structures';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn () => User::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('basic_salary')
                            ->required()
                            ->numeric()
                            ->prefix('৳')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::updateGross($get, $set)),
                        Forms\Components\TextInput::make('house_rent_allowance')
                            ->numeric()
                            ->prefix('৳')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::updateGross($get, $set)),
                        Forms\Components\TextInput::make('medical_allowance')
                            ->numeric()
                            ->prefix('৳')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::updateGross($get, $set)),
                        Forms\Components\TextInput::make('conveyance_allowance')
                            ->numeric()
                            ->prefix('৳')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::updateGross($get, $set)),
                        Forms\Components\TextInput::make('other_allowance')
                            ->numeric()
                            ->prefix('৳')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::updateGross($get, $set)),
                        Forms\Components\TextInput::make('gross_salary')
                            ->numeric()
                            ->prefix('৳')
                            ->disabled()
                            ->dehydrated(),
                    ]),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('provident_fund_percent')
                            ->label('Provident Fund (%)')
                            ->numeric()
                            ->suffix('%'),
                        Forms\Components\TextInput::make('tax_percent')
                            ->label('Tax (%)')
                            ->numeric()
                            ->suffix('%'),
                        Forms\Components\DatePicker::make('effective_from')
                            ->required(),
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSalaryStructures::route('/'),
        ];
    }

    public static function updateGross(Forms\Get $get, Forms\Set $set): void
    {
        $gross = (float) ($get('basic_salary') ?? 0)
            + (float) ($get('house_rent_allowance') ?? 0)
            + (float) ($get('medical_allowance') ?? 0)
            + (float) ($get('conveyance_allowance') ?? 0)
            + (float) ($get('other_allowance') ?? 0);

        $set('gross_salary', number_format($gross, 2, '.', ''));
    }
}
