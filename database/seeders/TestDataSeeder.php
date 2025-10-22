<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates test data for development and testing purposes.
     */
    public function run(): void
    {
        // Create test team
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'description' => 'Test organization for development and testing',
            'is_active' => true,
        ]);

        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'team_id' => $team->id,
        ]);

        // Create work schedule (40 hours per week, standard Portuguese schedule)
        $workSchedule = WorkSchedule::create([
            'name' => 'Standard Schedule',
            'description' => 'Monday to Friday, 9:00 AM to 6:00 PM',
            'monday_start' => '09:00:00',
            'monday_end' => '18:00:00',
            'tuesday_start' => '09:00:00',
            'tuesday_end' => '18:00:00',
            'wednesday_start' => '09:00:00',
            'wednesday_end' => '18:00:00',
            'thursday_start' => '09:00:00',
            'thursday_end' => '18:00:00',
            'friday_start' => '09:00:00',
            'friday_end' => '18:00:00',
            'break_duration' => 60, // 1 hour lunch break
            'weekly_hours' => 40,
            'is_active' => true,
            'team_id' => $team->id,
        ]);

        // Create 4 departments
        $departments = [
            'Engineering' => 'ENG',
            'HR' => 'HR',
            'Sales' => 'SAL',
            'Marketing' => 'MKT',
        ];

        $departmentModels = [];
        foreach ($departments as $name => $code) {
            $departmentModels[$name] = Department::create([
                'name' => $name,
                'code' => $code,
                'description' => "$name Department",
                'team_id' => $team->id,
            ]);
        }

        // Create 20 employees across departments
        $employees = [];
        $firstNames = ['João', 'Maria', 'Pedro', 'Ana', 'Carlos', 'Sofia', 'Miguel', 'Rita', 'Tiago', 'Inês',
                       'André', 'Catarina', 'Ricardo', 'Paula', 'Bruno', 'Teresa', 'David', 'Patrícia', 'Rui', 'Isabel'];
        $lastNames = ['Silva', 'Santos', 'Ferreira', 'Rodrigues', 'Pereira', 'Costa', 'Martins', 'Oliveira', 'Sousa', 'Alves'];

        foreach ($firstNames as $index => $firstName) {
            $department = $departmentModels[array_rand($departmentModels)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            $employee = Employee::create([
                'employee_number' => 'EMP' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $index . '@example.com'),
                'phone' => '+351 9' . rand(10000000, 99999999),
                'date_of_birth' => Carbon::now()->subYears(rand(25, 55))->subDays(rand(1, 365)),
                'hire_date' => Carbon::now()->subYears(rand(1, 10))->subDays(rand(1, 365)),
                'department_id' => $department->id,
                'position' => $this->getRandomPosition($department->name),
                'work_schedule_id' => $workSchedule->id,
                'nif' => rand(100000000, 999999999),
                'niss' => rand(10000000000, 99999999999),
                'contract_type' => ['permanent', 'fixed-term', 'temporary'][rand(0, 2)],
                'weekly_hours' => 40,
                'is_active' => true,
                'team_id' => $team->id,
            ]);
            
            $employees[] = $employee;
        }

        // Generate 30 days of attendance records with 90% attendance rate
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            // Only create attendance for weekdays
            if ($current->isWeekday()) {
                foreach ($employees as $employee) {
                    // 90% attendance rate
                    if (rand(1, 100) <= 90) {
                        // Random clock in time between 08:45 and 09:15
                        $clockIn = $current->copy()->setTime(9, 0)->subMinutes(rand(0, 15));
                        // Regular work day: 8 hours + break
                        $clockOut = $clockIn->copy()->addHours(9); // 8 hours work + 1 hour break
                        
                        // Occasionally add overtime (20% chance)
                        if (rand(1, 100) <= 20) {
                            $clockOut->addHours(rand(1, 3));
                        }
                        
                        $attendance = Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $current->toDateString(),
                            'clock_in' => $clockIn,
                            'clock_out' => $clockOut,
                            'break_start' => $clockIn->copy()->addHours(4),
                            'break_end' => $clockIn->copy()->addHours(5),
                            'status' => 'present',
                            'notes' => null,
                        ]);
                        
                        // Calculate hours
                        $attendance->calculateHours();
                        $attendance->save();
                        
                        // Create overtime record if applicable
                        if ($attendance->overtime_hours > 0) {
                            Overtime::create([
                                'employee_id' => $employee->id,
                                'attendance_id' => $attendance->id,
                                'date' => $current->toDateString(),
                                'hours' => $attendance->overtime_hours,
                                'rate_multiplier' => Overtime::calculateRateMultiplier($attendance->overtime_hours),
                                'status' => ['pending', 'approved'][rand(0, 1)],
                                'notes' => 'Overtime work on project deadline',
                            ]);
                        }
                    }
                }
            }
            $current->addDay();
        }

        // Create leave requests in various states
        $leaveTypes = ['vacation', 'sick', 'maternity', 'paternity', 'marriage', 'bereavement'];
        $statuses = ['pending', 'approved', 'rejected'];

        for ($i = 0; $i < 15; $i++) {
            $employee = $employees[array_rand($employees)];
            $leaveType = $leaveTypes[array_rand($leaveTypes)];
            $startDate = Carbon::now()->addDays(rand(1, 60));
            $endDate = $startDate->copy()->addDays($this->getLeaveTypeDays($leaveType));
            
            Leave::create([
                'employee_id' => $employee->id,
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_requested' => $startDate->diffInWeekdays($endDate) + 1,
                'status' => $statuses[array_rand($statuses)],
                'reason' => $this->getLeaveReason($leaveType),
                'approved_by' => rand(0, 1) ? $employees[array_rand($employees)]->id : null,
                'approved_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 10)) : null,
            ]);
        }

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
    }

    /**
     * Get a random position based on department
     */
    private function getRandomPosition(string $department): string
    {
        $positions = [
            'Engineering' => ['Software Engineer', 'Senior Developer', 'Tech Lead', 'QA Engineer'],
            'HR' => ['HR Manager', 'HR Specialist', 'Recruiter', 'HR Assistant'],
            'Sales' => ['Sales Manager', 'Account Executive', 'Sales Representative', 'Business Development'],
            'Marketing' => ['Marketing Manager', 'Content Specialist', 'Social Media Manager', 'Marketing Analyst'],
        ];

        return $positions[$department][array_rand($positions[$department])] ?? 'Employee';
    }

    /**
     * Get typical leave type duration
     */
    private function getLeaveTypeDays(string $leaveType): int
    {
        return match ($leaveType) {
            'vacation' => rand(5, 15),
            'sick' => rand(1, 5),
            'maternity' => rand(120, 150),
            'paternity' => 28,
            'marriage' => 15,
            'bereavement' => 5,
            default => rand(1, 10),
        };
    }

    /**
     * Get leave reason based on type
     */
    private function getLeaveReason(string $leaveType): string
    {
        return match ($leaveType) {
            'vacation' => 'Annual vacation',
            'sick' => 'Medical appointment',
            'maternity' => 'Maternity leave',
            'paternity' => 'Paternity leave',
            'marriage' => 'Marriage ceremony',
            'bereavement' => 'Family bereavement',
            default => 'Personal reasons',
        };
    }
}
