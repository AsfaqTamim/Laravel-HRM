<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'allocated_days' => 'decimal:1',
            'used_days' => 'decimal:1',
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

    /**
     * Get remaining days for this balance.
     */
    public function getRemainingDaysAttribute(): float
    {
        return max(0, (float) $this->allocated_days - (float) $this->used_days);
    }

    /**
     * Update used_days for an employee's leave type by recounting approved leaves.
     */
    public static function updateUsedDays(int $employeeId, int $leaveTypeId, int $year): void
    {
        $used = Leave::forEmployee($employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->approved()
            ->forYear($year)
            ->sum('total_days');

        self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
            ],
            ['used_days' => $used]
        );
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForEmployee($query, int $userId)
    {
        return $query->where('employee_id', $userId);
    }

    /**
     * Initialize balances for an employee for the current year.
     */
    public static function initializeForEmployee(int $employeeId, ?int $year = null): void
    {
        $year ??= now()->year;

        $types = LeaveType::active()->get();

        foreach ($types as $type) {
            self::firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $type->id,
                    'year' => $year,
                ],
                [
                    'allocated_days' => $type->default_days_per_year,
                    'used_days' => 0,
                ]
            );
        }
    }
}
