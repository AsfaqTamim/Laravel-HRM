<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'employee_code' => 'EMP-001',
                'is_active' => true,
            ]
        );

        // Assign super_admin role if Spatie permissions is configured
        if (method_exists($admin, 'assignRole')) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => 'super_admin', 'guard_name' => 'web']
            );
            $admin->assignRole($role);
        }
    }
}
