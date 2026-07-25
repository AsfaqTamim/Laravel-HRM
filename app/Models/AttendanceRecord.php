<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'break_minutes',
        'overtime_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'break_start' => 'datetime',
            'break_end' => 'datetime',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'break_minutes' => 'integer',
            'overtime_minutes' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeToday($query)
    {
        return $query->forDate(now());
    }

    public function scopeForEmployee($query, $userId)
    {
        return $query->where('employee_id', $userId);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Get the applicable office-time for this employee.
     * Priority: employee-based > active default.
     */
    public static function getOfficeTimeForEmployee(?int $employeeId): ?OfficeTime
    {
        return OfficeTime::where('type', 'employee')
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->first()
            ??
            OfficeTime::where('type', 'default')
                ->where('is_active', true)
                ->first();
    }

    /**
     * Check the employee in for today.
     */
    public static function checkIn(int $employeeId, ?Carbon $at = null): self
    {
        $at ??= now();

        $officeTime = self::getOfficeTimeForEmployee($employeeId);

        $lateMinutes = 0;
        $status = 'present';

        if ($officeTime && $officeTime->start_time) {
            $start = Carbon::parse($officeTime->start_time->format('H:i'), $at->tz);
            $start->setDateFrom($at);

            if ($at->gt($start)) {
                $lateMinutes = (int) $start->diffInMinutes($at);
                $status = 'late';
            }
        }

        return self::create([
            'employee_id' => $employeeId,
            'date' => $at->toDateString(),
            'check_in' => $at,
            'status' => $status,
            'late_minutes' => $lateMinutes,
        ]);
    }

    /**
     * Check the employee out.
     */
    public function checkOut(?Carbon $at = null): self
    {
        $at ??= now();

        $record = $this->fresh();
        if (!$record || $record->check_out) {
            return $record; // already checked out
        }

        // If currently on break, end it first
        if ($record->onBreak()) {
            $record->breakOut($at);
        }

        $start = $record->check_in ? Carbon::parse($record->check_in) : $at;
        $workedMinutes = (int) $start->diffInMinutes($at) - $record->break_minutes;

        $overtimeMinutes = 0;
        $earlyLeaveMinutes = 0;

        $officeTime = self::getOfficeTimeForEmployee($record->employee_id);
        if ($officeTime && $officeTime->end_time) {
            $end = Carbon::parse($officeTime->end_time->format('H:i'), $at->tz);
            $end->setDateFrom($at);

            // Night shift: if end_time <= start_time, shift wraps to next day
            if ($officeTime->start_time) {
                $shiftStart = Carbon::parse($officeTime->start_time->format('H:i'), $at->tz);
                $shiftStart->setDateFrom($at);
                if ($end->lte($shiftStart)) {
                    $end->addDay();
                }
            }

            if ($end->isPast() && $at->lt($end)) {
                $earlyLeaveMinutes = (int) $at->diffInMinutes($end);
            } elseif ($at->gt($end)) {
                $overtimeMinutes = (int) $end->diffInMinutes($at);
            }
        }

        // Auto-set half-day if worked less than 4 hours
        $status = $record->status;
        if ($workedMinutes < 240 && $status !== 'absent') {
            $status = 'half_day';
        }

        $record->update([
            'check_out' => $at,
            'status' => $status,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes' => $overtimeMinutes,
        ]);

        return $record->fresh();
    }

    /**
     * Start a break.
     */
    public function breakIn(?Carbon $at = null): self
    {
        $at ??= now();

        $record = $this->fresh();
        if (!$record || $record->onBreak()) {
            return $record; // already on break
        }

        $record->update([
            'break_start' => $at,
            'break_end' => null,
        ]);

        return $record->fresh();
    }

    /**
     * End the current break.
     */
    public function breakOut(?Carbon $at = null): self
    {
        $at ??= now();

        $record = $this->fresh();
        if (!$record || !$record->break_start || $record->break_end) {
            return $record; // not on break or already ended
        }

        $duration = (int) Carbon::parse($record->break_start)->diffInMinutes($at);

        $record->update([
            'break_end' => $at,
            'break_minutes' => $record->break_minutes + $duration,
        ]);

        return $record->fresh();
    }

    /**
     * Check if the employee is currently on break.
     */
    public function onBreak(): bool
    {
        return !is_null($this->break_start) && is_null($this->break_end);
    }

    /**
     * Get today's record for an employee, or null.
     */
    public static function todayRecord(int $employeeId): ?self
    {
        return self::where('employee_id', $employeeId)
            ->whereDate('date', now()->toDateString())
            ->first();
    }
}
