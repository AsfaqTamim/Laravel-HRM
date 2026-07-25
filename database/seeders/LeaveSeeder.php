<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHolidays();
        $this->seedLeaveTypes();
        $this->seedInitialBalances();
    }

    private function seedHolidays(): void
    {
        $currentYear = now()->year;
        $holidays = [
            // Fixed/recurring national holidays
            ['name' => 'International Mother Language Day', 'date' => "{$currentYear}-02-21", 'type' => 'national', 'year' => $currentYear, 'is_recurring' => true],
            ['name' => 'Independence Day', 'date' => "{$currentYear}-03-26", 'type' => 'national', 'year' => $currentYear, 'is_recurring' => true],
            ['name' => 'Bangla New Year (Pohela Boishakh)', 'date' => "{$currentYear}-04-14", 'type' => 'observance', 'year' => $currentYear, 'is_recurring' => false],
            ['name' => 'May Day', 'date' => "{$currentYear}-05-01", 'type' => 'national', 'year' => $currentYear, 'is_recurring' => true],
            ['name' => 'National Mourning Day', 'date' => "{$currentYear}-08-15", 'type' => 'national', 'year' => $currentYear, 'is_recurring' => true],
            ['name' => 'Victory Day', 'date' => "{$currentYear}-12-16", 'type' => 'national', 'year' => $currentYear, 'is_recurring' => true],
            ['name' => 'Christmas Day', 'date' => "{$currentYear}-12-25", 'type' => 'religious', 'year' => $currentYear, 'is_recurring' => true],

            // Add next year's recurring holidays too
        ];

        // Add next year
        $nextYear = $currentYear + 1;
        $holidays = array_merge($holidays, [
            ['name' => 'International Mother Language Day', 'date' => "{$nextYear}-02-21", 'type' => 'national', 'year' => $nextYear, 'is_recurring' => true],
            ['name' => 'Independence Day', 'date' => "{$nextYear}-03-26", 'type' => 'national', 'year' => $nextYear, 'is_recurring' => true],
            ['name' => 'May Day', 'date' => "{$nextYear}-05-01", 'type' => 'national', 'year' => $nextYear, 'is_recurring' => true],
            ['name' => 'National Mourning Day', 'date' => "{$nextYear}-08-15", 'type' => 'national', 'year' => $nextYear, 'is_recurring' => true],
            ['name' => 'Victory Day', 'date' => "{$nextYear}-12-16", 'type' => 'national', 'year' => $nextYear, 'is_recurring' => true],
            ['name' => 'Christmas Day', 'date' => "{$nextYear}-12-25", 'type' => 'religious', 'year' => $nextYear, 'is_recurring' => true],
        ]);

        // Religious holidays (date varies, year-specific)
        $religious = [
            ['name' => 'Eid-ul-Fitr', 'date' => "{$currentYear}-04-10", 'type' => 'religious', 'year' => $currentYear, 'is_recurring' => false],
            ['name' => 'Eid-ul-Azha', 'date' => "{$currentYear}-06-17", 'type' => 'religious', 'year' => $currentYear, 'is_recurring' => false],
            ['name' => 'Eid-ul-Fitr', 'date' => "{$nextYear}-03-30", 'type' => 'religious', 'year' => $nextYear, 'is_recurring' => false],
            ['name' => 'Eid-ul-Azha', 'date' => "{$nextYear}-06-06", 'type' => 'religious', 'year' => $nextYear, 'is_recurring' => false],
        ];

        foreach (array_merge($holidays, $religious) as $holiday) {
            Holiday::firstOrCreate(
                ['name' => $holiday['name'], 'year' => $holiday['year']],
                $holiday + ['description' => null, 'is_active' => true]
            );
        }

        $this->command?->info('✅ Holidays seeded: ' . Holiday::count() . ' entries');
    }

    private function seedLeaveTypes(): void
    {
        $types = [
            ['name' => 'Sick Leave',       'code' => 'SL', 'default_days_per_year' => 14, 'sort_order' => 1, 'description' => 'For medical appointments, illness, or recovery'],
            ['name' => 'Casual Leave',     'code' => 'CL', 'default_days_per_year' => 10, 'sort_order' => 2, 'description' => 'For personal errands, family events, or emergencies'],
            ['name' => 'Annual Leave',     'code' => 'AL', 'default_days_per_year' => 15, 'sort_order' => 3, 'description' => 'Pre-planned vacation or time off'],
            ['name' => 'Maternity Leave',  'code' => 'ML', 'default_days_per_year' => 120, 'sort_order' => 4, 'description' => 'For childbirth and postnatal care'],
            ['name' => 'Paternity Leave',  'code' => 'PL', 'default_days_per_year' => 7, 'sort_order' => 5, 'description' => 'For fathers following childbirth'],
            ['name' => 'Leave Without Pay', 'code' => 'LWP', 'default_days_per_year' => 30, 'sort_order' => 6, 'description' => 'Unpaid leave beyond paid entitlement'],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(
                ['code' => $type['code']],
                $type + ['is_active' => true]
            );
        }

        $this->command?->info('✅ Leave types seeded: ' . LeaveType::count() . ' types');
    }

    private function seedInitialBalances(): void
    {
        $currentYear = now()->year;
        $employees = User::where('is_active', true)->where('id', '!=', 1)->get();
        $admin = User::first(); // also give admin balances

        $all = collect([$admin])->concat($employees)->filter();

        $count = 0;
        foreach ($all as $employee) {
            foreach (LeaveType::active()->get() as $type) {
                LeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $type->id,
                        'year' => $currentYear,
                    ],
                    [
                        'allocated_days' => $type->default_days_per_year,
                        'used_days' => 0,
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("✅ Leave balances initialized for {$all->count()} employees ({$count} records)");
    }
}
