<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $it = Department::firstOrCreate(['name' => 'Information Technology'], ['description' => 'IT Department', 'is_active' => true]);
        $hr = Department::firstOrCreate(['name' => 'Human Resources'], ['description' => 'HR Department', 'is_active' => true]);
        $finance = Department::firstOrCreate(['name' => 'Finance & Accounts'], ['description' => 'Finance Department', 'is_active' => true]);
        $marketing = Department::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing Department', 'is_active' => true]);
        $operations = Department::firstOrCreate(['name' => 'Operations'], ['description' => 'Operations Department', 'is_active' => true]);

        // Designations
        $devLead = Designation::firstOrCreate(
            ['department_id' => $it->id, 'name' => 'Developer Lead'],
            ['description' => 'Leads the development team', 'is_active' => true]
        );
        $dev = Designation::firstOrCreate(
            ['department_id' => $it->id, 'name' => 'Developer'],
            ['description' => 'Software Developer', 'is_active' => true]
        );
        $designationHr = Designation::firstOrCreate(
            ['department_id' => $hr->id, 'name' => 'HR Manager'],
            ['description' => 'Manages HR operations', 'is_active' => true]
        );
        Designation::firstOrCreate(['department_id' => $hr->id, 'name' => 'HR Executive'], ['description' => 'HR Executive', 'is_active' => true]);
        Designation::firstOrCreate(['department_id' => $finance->id, 'name' => 'Accountant'], ['description' => 'Accountant', 'is_active' => true]);
        Designation::firstOrCreate(['department_id' => $marketing->id, 'name' => 'Marketing Manager'], ['description' => 'Marketing Manager', 'is_active' => true]);
        Designation::firstOrCreate(['department_id' => $operations->id, 'name' => 'Operations Manager'], ['description' => 'Operations Manager', 'is_active' => true]);

        // Update admin user
        $admin = User::find(1);
        if ($admin) {
            $admin->update([
                'employee_code' => 'EMP-001',
                'department_id' => $it->id,
                'designation_id' => $devLead->id,
                'phone' => '+880-1712345678',
                'hire_date' => '2024-01-15',
                'salary' => 120000,
                'is_active' => true,
            ]);
        }

        // Create a sample employee
        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Sample Employee',
                'password' => bcrypt('password'),
                'employee_code' => 'EMP-002',
                'department_id' => $it->id,
                'designation_id' => $dev->id,
                'phone' => '+880-1712345679',
                'hire_date' => '2025-06-01',
                'salary' => 60000,
                'is_active' => true,
            ]
        );
    }
}
