<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class EmployeeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Employees';

    protected static ?string $navigationLabel = 'Employees';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralLabel = 'Employees';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Employees see only themselves; admins see all
        if (! auth()->user()?->hasRole('super_admin')) {
            $query->where('id', auth()->id());
        }

        return $query->latest();
    }

    public static function form(Form $form): Form
    {
        $isAdmin = auth()->user()?->hasRole('super_admin');

        return $form
            ->schema([
                Forms\Components\Section::make('Account Info')
                    ->schema([
                        Forms\Components\TextInput::make('employee_code')
                            ->label('Employee ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('EMP-001')
                            ->disabled(! $isAdmin),

                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(! $isAdmin),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255)
                            ->label(fn ($operation) => $operation === 'edit' ? 'New Password (leave blank to keep)' : 'Password'),

                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(Role::pluck('name', 'name'))
                            ->searchable()
                            ->native(false)
                            ->visible($isAdmin)
                            ->exists('roles', 'name')
                            ->dehydrated(fn ($state) => filled($state))
                            ->afterStateHydrated(function (Forms\Components\Select $component, $state) {
                                $record = $component->getRecord();
                                if ($record && $record->roles->isNotEmpty()) {
                                    $component->state($record->roles->first()->name);
                                }
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Employment Details')
                    ->visible($isAdmin)
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->options(Department::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('designation_id', null)),

                        Forms\Components\Select::make('designation_id')
                            ->label('Designation')
                            ->options(function (callable $get) {
                                $deptId = $get('department_id');
                                if ($deptId) {
                                    return Designation::where('department_id', $deptId)
                                        ->where('is_active', true)
                                        ->pluck('name', 'id');
                                }
                                return Designation::where('is_active', true)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->native(false),

                        Forms\Components\DatePicker::make('hire_date')
                            ->label('Hire Date')
                            ->native(false),

                        Forms\Components\TextInput::make('salary')
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Employee')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Address')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = auth()->user()?->hasRole('super_admin');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->email),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('designation.name')
                    ->label('Designation')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('hire_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters($isAdmin ? [
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::pluck('name', 'id'))
                    ->searchable()
                    ->native(false),

                Tables\Filters\SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->options(Designation::pluck('name', 'id'))
                    ->searchable()
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->default(true),
            ] : [])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
