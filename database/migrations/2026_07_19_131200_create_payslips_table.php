<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('salary_structure_id')->nullable()->constrained()->nullOnDelete();

            // Income components
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('house_rent_allowance', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('conveyance_allowance', 12, 2)->default(0);
            $table->decimal('other_allowance', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);

            // Deductions
            $table->decimal('provident_fund', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('other_deduction', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);

            // Leave adjustments
            $table->decimal('unpaid_leave_days', 4, 1)->default(0);
            $table->decimal('unpaid_leave_deduction', 12, 2)->default(0);

            // Final
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->string('status')->default('computed'); // computed, finalized
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
