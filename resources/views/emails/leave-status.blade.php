<x-mail::message>
# Leave Request {{ ucfirst($status) }}

Hello **{{ $leave->employee->name }}**,

Your leave request has been **{{ $status }}**.

| Detail | Info |
|---|---|
| **Type** | {{ $leave->leaveType?->name }} |
| **From** | {{ $leave->start_date->format('d/m/Y') }} |
| **To** | {{ $leave->end_date->format('d/m/Y') }} |
| **Days** | {{ $leave->total_days }} |
| **Reason** | {{ $leave->reason }} |
@if($leave->rejection_notes)
| **Rejection Note** | {{ $leave->rejection_notes }} |
@endif

<x-mail::button :url="config('app.url')">
View Leave Requests
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
