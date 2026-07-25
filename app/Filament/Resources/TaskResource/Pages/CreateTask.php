<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Mail\TaskAssignedMail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function afterCreate(): void
    {
        $task = $this->record;

        if ($task->assigned_to && $task->assignee?->email) {
            Mail::to($task->assignee->email)
                ->queue(new TaskAssignedMail($task));
        }
    }
}
