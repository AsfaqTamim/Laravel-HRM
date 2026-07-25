<?php

namespace Database\Seeders;

use App\Models\OfficeTime;
use Illuminate\Database\Seeder;

class OfficeTimeSeeder extends Seeder
{
    public function run(): void
    {
        OfficeTime::create([
            'name' => 'General Shift',
            'type' => 'default',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'late_count' => 3,
            'max_break_minutes' => 60,
            'is_active' => true,
        ]);
    }
}
