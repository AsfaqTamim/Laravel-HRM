<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'house_rent_allowance',
        'medical_allowance',
        'conveyance_allowance',
        'other_allowance',
        'gross_salary',
        'provident_fund_percent',
        'tax_percent',
        'effective_from',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'is_active' => 'boolean',
            'basic_salary' => 'decimal:2',
            'house_rent_allowance' => 'decimal:2',
            'medical_allowance' => 'decimal:2',
            'conveyance_allowance' => 'decimal:2',
            'other_allowance' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'provident_fund_percent' => 'decimal:2',
            'tax_percent' => 'decimal:2',
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
}
