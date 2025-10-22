<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test team
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'description' => 'Test company for dashboard widgets',
            'is_active' => true,
        ]);

        // Create an admin user
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'team_id' => $team->id,
        ]);

        // Create departments
        $departments = [
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Sales', 'code' => 'SALES'],
            ['name' => 'Marketing', 'code' => 'MKT'],
        ];

        foreach ($departments as $deptData) {
            Department::create([
                'name' => $deptData['name'],
                'code' => $deptData['code'],
                'team_id' => $team->id,
            ]);
        }

        $depts = Department::all();

        // Create employees
        $employees = [];
        foreach ($depts as $dept) {
            for ($i = 1; $i <= 5; $i++) {
                $employee = Employee::create([
                    'employee_number' => $dept->code . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'first_name' => 'Employee',
                    'last_name' => $dept->code . '-' . $i,
                    'email' => strtolower($dept->code) . $i . '@example.com',
                    'phone' => '912345' . rand(100, 999),
                    'date_of_birth' => Carbon::now()->subYears(rand(25, 50)),
                    'hire_date' => Carbon::now()->subMonths(rand(6, 36)),
                    'department_id' => $dept->id,
                    'position' => 'Staff',
                    'nif' => '1234567' . rand(10, 99),
                    'niss' => '12345678' . rand(100, 999),
                    'contract_type' => 'permanent',
                    'weekly_hours' => 40,
                    'is_active' => true,
                    'team_id' => $team->id,
                ]);
                $employees[] = $employee;
            }
        }

        // Create attendance records for the last 30 days
        foreach ($employees as $employee) {
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::today()->subDays($i);
                
                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }
                
                // 90% attendance rate
                if (rand(1, 10) > 1) {
                    $clockIn = $date->copy()->setTime(9, rand(0, 30));
                    $clockOut = $date->copy()->setTime(18, rand(0, 30));
                    
                    $attendance = Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => 'present',
                    ]);
                    
                    $attendance->calculateHours();
                    $attendance->save();
                    
                    // Create overtime for some records
                    if ($attendance->overtime_hours > 0 && rand(1, 3) === 1) {
                        Overtime::create([
                            'employee_id' => $employee->id,
                            'attendance_id' => $attendance->id,
                            'date' => $date,
                            'hours' => $attendance->overtime_hours,
                            'rate_multiplier' => 1.5,
                            'status' => rand(1, 2) === 1 ? 'pending' : 'approved',
                        ]);
                    }
                }
            }
        }

        // Create some leave requests
        $leaveTypes = ['vacation', 'sick', 'maternity', 'paternity', 'marriage', 'bereavement'];
        foreach ($employees as $employee) {
            // Create 1-2 leave requests per employee
            for ($i = 0; $i < rand(1, 2); $i++) {
                $startDate = Carbon::today()->addDays(rand(1, 60));
                $days = rand(1, 10);
                
                Leave::create([
                    'employee_id' => $employee->id,
                    'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addDays($days),
                    'days_requested' => $days,
                    'status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                    'reason' => 'Test leave request',
                ]);
            }
        }

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Admin credentials:');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
    }
}
