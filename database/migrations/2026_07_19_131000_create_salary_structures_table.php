<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('house_rent_allowance', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('conveyance_allowance', 12, 2)->default(0);
            $table->decimal('other_allowance', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0)->comment('basic + all allowances');
            $table->decimal('provident_fund_percent', 5, 2)->default(0)->comment('% of basic salary');
            $table->decimal('tax_percent', 5, 2)->default(0)->comment('% of gross salary');
            $table->date('effective_from');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['employee_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_structures');
    }
};
