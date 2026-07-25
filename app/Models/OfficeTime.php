<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeTime extends Model
{
    /** @use HasFactory<\Database\Factories\OfficeTimeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'employee_id',
        'start_time',
        'end_time',
        'late_count',
        'max_break_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'late_count' => 'integer',
            'max_break_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
