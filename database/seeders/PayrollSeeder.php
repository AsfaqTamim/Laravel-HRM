<?php

namespace Database\Seeders;

use App\Models\SalaryStructure;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('is_active', true)->where('id', '>', 1)->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Skipping salary structures.');
            return;
        }

        $count = 0;
        foreach ($employees as $employee) {
            $basic = match (true) {
                $employee->id % 3 === 0 => 35000,
                $employee->id % 2 === 0 => 25000,
                default => 15000,
            };

            $hra = round($basic * 0.40, 2); // 40% house rent
            $ma = round($basic * 0.10, 2);  // 10% medical
            $ca = round($basic * 0.05, 2);  // 5% conveyance
            $gross = $basic + $hra + $ma + $ca;

            SalaryStructure::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'effective_from' => $employee->hire_date ?? now()->subYear()->startOfYear(),
                ],
                [
                    'basic_salary' => $basic,
                    'house_rent_allowance' => $hra,
                    'medical_allowance' => $ma,
                    'conveyance_allowance' => $ca,
                    'other_allowance' => 0,
                    'gross_salary' => $gross,
                    'provident_fund_percent' => 5,
                    'tax_percent' => 0,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->command->info("✅ Salary structures seeded for {$count} employees");
    }
}
