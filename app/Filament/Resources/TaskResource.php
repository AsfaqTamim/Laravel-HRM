<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'Tasks';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->latest();

        // Non-admin sees only their own tasks + tasks assigned to them
        if (!Auth::user()?->hasRole('super_admin')) {
            $query->where(function (Builder $q) {
                $q->where('assigned_to', Auth::id())
                  ->orWhere('created_by', Auth::id());
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $isAdmin = Auth::user()?->hasRole('super_admin');

        return $form
            ->schema([
                Forms\Components\Section::make('Task Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('project_id')
                            ->label('Project')
                            ->placeholder('No Project (General Task)')
                            ->options(
                                $isAdmin
                                    ? Project::where('status', 'active')->pluck('name', 'id')
                                    : Auth::user()?->assignedProjects()
                                        ->where('projects.status', 'active')
                                        ->pluck('projects.name', 'projects.id')
                            )
                            ->searchable()
                            ->native(false)
                            ->nullable(),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Assign To')
                            ->options(
                                $isAdmin
                                    ? User::where('is_active', true)->pluck('name', 'id')
                                    : User::where('id', Auth::id())->pluck('name', 'id')
                            )
                            ->searchable()
                            ->native(false)
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'todo' => 'To Do',
                                'in_progress' => 'In Progress',
                                'done' => 'Done',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('todo')
                            ->native(false)
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium')
                            ->native(false)
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->native(false)
                            ->nullable(),

                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => Auth::id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = Auth::user()?->hasRole('super_admin');

        return $table
            ->heading('Tasks')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assignee')
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'low' => 'heroicon-m-arrow-down',
                        'medium' => 'heroicon-m-minus',
                        'high' => 'heroicon-m-arrow-up',
                        'urgent' => 'heroicon-m-exclamation-triangle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->date('d M')
                    ->sortable()
                    ->color(fn (Task $record): string =>
                        $record->due_date && $record->due_date->isPast() && !in_array($record->status, ['done', 'cancelled'])
                            ? 'danger' : 'gray'
                    ),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'todo' => 'gray',
                        'in_progress' => 'info',
                        'done' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d/m')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'todo' => 'To Do',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                        'cancelled' => 'Cancelled',
                    ])
                    ->native(false),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->native(false),
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                        ->whereNotIn('status', ['done', 'cancelled'])
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $isAdmin)
                    ->icon('heroicon-m-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $isAdmin),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
