<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // User ID 1 is the admin arafat.122260@gmail.com
        $adminId = 1;

        // Today's check-in (so the widget has something to show)
        AttendanceRecord::create([
            'employee_id' => $adminId,
            'date' => now()->toDateString(),
            'check_in' => now()->subHours(3),
            'check_out' => null,
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        // Past records for context
        foreach (range(1, 10) as $daysAgo) {
            $date = now()->subDays($daysAgo);
            $in = (clone $date)->setTime(9, random_int(0, 30));
            $out = (clone $date)->setTime(17, random_int(30, 59));

            $late = $in->diffInMinutes((clone $date)->setTime(9, 0));
            $status = $late > 5 ? 'late' : 'present';

            AttendanceRecord::create([
                'employee_id' => $adminId,
                'date' => $date->toDateString(),
                'check_in' => $in,
                'check_out' => $out,
                'status' => $status,
                'late_minutes' => $late,
                'overtime_minutes' => 0,
            ]);
        }
    }
}
