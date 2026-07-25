<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected static string $view = 'filament.resources.task-resource.pages.kanban-tasks';

    public ?string $viewMode = 'kanban';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getKanbanTasks(): array
    {
        $query = Task::query()
            ->with(['project', 'assignee'])
            ->when(!Auth::user()?->hasRole('super_admin'), function (Builder $q) {
                $q->where(function (Builder $q) {
                    $q->where('assigned_to', Auth::id())
                      ->orWhere('created_by', Auth::id());
                });
            })
            ->orderBy('created_at', 'desc');

        $tasks = $query->get()->groupBy('status');

        return [
            'todo' => $tasks->get('todo', collect()),
            'in_progress' => $tasks->get('in_progress', collect()),
            'done' => $tasks->get('done', collect()),
            'cancelled' => $tasks->get('cancelled', collect()),
        ];
    }

    public function updateTaskStatus(int $taskId, string $status): void
    {
        $query = Task::query();

        if (!Auth::user()?->hasRole('super_admin')) {
            $query->where(function (Builder $q) {
                $q->where('assigned_to', Auth::id())
                  ->orWhere('created_by', Auth::id());
            });
        }

        $task = $query->findOrFail($taskId);
        $task->update(['status' => $status]);
    }

    public function deleteTask(int $taskId): void
    {
        $query = Task::query();

        if (!Auth::user()?->hasRole('super_admin')) {
            $query->where(function (Builder $q) {
                $q->where('assigned_to', Auth::id())
                  ->orWhere('created_by', Auth::id());
            });
        }

        $task = $query->findOrFail($taskId);
        $task->delete();

        Notification::make()
            ->title('Task deleted')
            ->success()
            ->send();
    }

    public function toggleView(): void
    {
        $this->viewMode = $this->viewMode === 'kanban' ? 'table' : 'kanban';
    }
}
