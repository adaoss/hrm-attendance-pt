<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\WorkSchedule;
use App\Models\Holiday;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default work schedule (9-18h, Monday-Friday)
        WorkSchedule::create([
            'name' => 'Standard 40h/week',
            'description' => 'Monday to Friday, 9:00 - 18:00',
            'monday_start' => '09:00',
            'monday_end' => '18:00',
            'tuesday_start' => '09:00',
            'tuesday_end' => '18:00',
            'wednesday_start' => '09:00',
            'wednesday_end' => '18:00',
            'thursday_start' => '09:00',
            'thursday_end' => '18:00',
            'friday_start' => '09:00',
            'friday_end' => '18:00',
            'break_duration' => 60,
            'weekly_hours' => 40.00,
            'is_active' => true,
        ]);

        // Create sample departments
        Department::create([
            'name' => 'Human Resources',
            'code' => 'HR',
            'description' => 'Human Resources Department',
        ]);

        Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'description' => 'IT Department',
        ]);

        Department::create([
            'name' => 'Finance',
            'code' => 'FIN',
            'description' => 'Finance Department',
        ]);

        // Seed Portuguese holidays for current year
        $currentYear = date('Y');
        $holidays = Holiday::getPortugueseNationalHolidays($currentYear);
        
        foreach ($holidays as $holidayData) {
            Holiday::create([
                'name' => $holidayData['name'],
                'date' => $holidayData['date'],
                'is_national' => $holidayData['is_national'],
                'is_recurring' => false,
            ]);
        }

        // Seed holidays for next year
        $nextYear = $currentYear + 1;
        $nextYearHolidays = Holiday::getPortugueseNationalHolidays($nextYear);
        
        foreach ($nextYearHolidays as $holidayData) {
            Holiday::create([
                'name' => $holidayData['name'],
                'date' => $holidayData['date'],
                'is_national' => $holidayData['is_national'],
                'is_recurring' => false,
            ]);
        }
    }
}
