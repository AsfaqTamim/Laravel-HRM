<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'salary_structure_id',
        'basic_salary',
        'house_rent_allowance',
        'medical_allowance',
        'conveyance_allowance',
        'other_allowance',
        'gross_pay',
        'provident_fund',
        'tax',
        'other_deduction',
        'total_deductions',
        'unpaid_leave_days',
        'unpaid_leave_deduction',
        'net_pay',
        'status',
        'remarks',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }
}
