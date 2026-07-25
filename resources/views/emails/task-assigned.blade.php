<x-mail::message>
# New Task Assigned

Hello **{{ $task->assignedTo?->name }}**,

A new task has been assigned to you.

| Detail | Info |
|---|---|
| **Title** | {{ $task->title }} |
| **Project** | {{ $task->project?->name ?? 'General' }} |
| **Priority** | {{ ucfirst($task->priority) }} |
| **Deadline** | {{ $task->deadline?->format('d/m/Y') ?? 'No deadline' }} |
| **Description** | {{ Str::limit($task->description, 200) }} |

<x-mail::button :url="config('app.url')">
View My Tasks
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
