<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AttendanceCheckWidget extends Widget
{
    protected static string $view = 'filament.widgets.attendance-check-widget';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public ?AttendanceRecord $todayRecord = null;

    public function mount(): void
    {
        $this->refresh();
    }

    public function checkIn(): void
    {
        $userId = Auth::id();
        $existing = AttendanceRecord::todayRecord($userId);

        if ($existing) {
            Notification::make()
                ->warning()
                ->title('Already checked in at ' . $existing->check_in->format('H:i'))
                ->send();
            $this->refresh();
            return;
        }

        $record = AttendanceRecord::checkIn($userId);
        $statusText = $record->status === 'late'
            ? "Late by {$record->late_minutes} min"
            : 'On time ✓';

        Notification::make()
            ->success()
            ->title("Checked in at {$record->check_in->format('H:i')}")
            ->body($statusText)
            ->send();

        $this->refresh();
    }

    public function checkOut(): void
    {
        $userId = Auth::id();
        $record = AttendanceRecord::todayRecord($userId);

        if (!$record) {
            Notification::make()
                ->warning()
                ->title('You must check in first')
                ->send();
            return;
        }

        if ($record->check_out) {
            Notification::make()
                ->warning()
                ->title('Already checked out at ' . $record->check_out->format('H:i'))
                ->send();
            $this->refresh();
            return;
        }

        $record->checkOut();

        $fresh = $record->fresh();
        $msg = $fresh->overtime_minutes > 0
            ? "Overtime: {$fresh->overtime_minutes} min"
            : "Work complete ✓";

        Notification::make()
            ->success()
            ->title("Checked out at " . now()->format('H:i'))
            ->body($msg)
            ->send();

        $this->refresh();
    }

    public function breakIn(): void
    {
        $userId = Auth::id();
        $record = AttendanceRecord::todayRecord($userId);

        if (!$record || !$record->check_in) {
            Notification::make()
                ->warning()
                ->title('You must check in first')
                ->send();
            return;
        }

        if ($record->check_out) {
            Notification::make()
                ->warning()
                ->title('Already checked out — break unavailable')
                ->send();
            return;
        }

        if ($record->onBreak()) {
            Notification::make()
                ->warning()
                ->title('Already on break since ' . $record->break_start->format('H:i'))
                ->send();
            $this->refresh();
            return;
        }

        $record->breakIn();

        Notification::make()
            ->info()
            ->title("Break started at " . now()->format('H:i'))
            ->send();

        $this->refresh();
    }

    public function breakOut(): void
    {
        $userId = Auth::id();
        $record = AttendanceRecord::todayRecord($userId);

        if (!$record || !$record->break_start || $record->break_end) {
            Notification::make()
                ->warning()
                ->title('Not currently on break')
                ->send();
            $this->refresh();
            return;
        }

        $duration = Carbon::parse($record->break_start)->diffInMinutes(now());
        $record->breakOut();

        Notification::make()
            ->success()
            ->title("Break ended — took {$duration} min")
            ->send();

        $this->refresh();
    }

    protected function refresh(): void
    {
        $this->todayRecord = AttendanceRecord::todayRecord(Auth::id());
    }

    public static function canView(): bool
    {
        return Auth::check();
    }
}
