<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'total_days' => 'decimal:1',
            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForEmployee($query, int $userId)
    {
        return $query->where('employee_id', $userId);
    }

    public function scopeForYear($query, ?int $year = null)
    {
        $year ??= now()->year;
        return $query->whereYear('start_date', $year);
    }

    /**
     * Approve this leave request.
     */
    public function approve(int $approvedBy): self
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        // Update leave balance
        LeaveBalance::updateUsedDays($this->employee_id, $this->leave_type_id, now()->year);

        return $this->fresh();
    }

    /**
     * Reject this leave request.
     */
    public function reject(int $approvedBy, ?string $notes = null): self
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);

        return $this->fresh();
    }

    /**
     * Calculate total days between two dates (excluding holidays/weekends later).
     */
    public static function calculateDays(Carbon|string $start, Carbon|string $end): float
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        return $start->diffInDays($end) + 1; // inclusive
    }

    /**
     * Auto-set total_days based on start/end.
     */
    public static function booted(): void
    {
        static::creating(function (Leave $leave) {
            if (!$leave->total_days) {
                $leave->total_days = self::calculateDays($leave->start_date, $leave->end_date);
            }
        });
    }
}
