<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'status',
        'total_employees',
        'total_gross',
        'total_deductions',
        'total_net',
        'processed_by',
        'processed_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::create()->month($this->month)->format('F');
    }

    public function getLabelAttribute(): string
    {
        return $this->month_name . ' ' . $this->year;
    }
}
