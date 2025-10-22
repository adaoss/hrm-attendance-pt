<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_is_created_successfully()
    {
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'description' => 'A test company',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);
    }

    public function test_employee_belongs_to_team()
    {
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);

        $employee = Employee::create([
            'employee_number' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => now(),
            'position' => 'Developer',
            'team_id' => $team->id,
        ]);

        $this->assertEquals($team->id, $employee->team_id);
        $this->assertInstanceOf(Team::class, $employee->team);
    }

    public function test_department_belongs_to_team()
    {
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);

        $department = Department::create([
            'name' => 'IT',
            'code' => 'IT',
            'team_id' => $team->id,
        ]);

        $this->assertEquals($team->id, $department->team_id);
        $this->assertInstanceOf(Team::class, $department->team);
    }

    public function test_work_schedule_belongs_to_team()
    {
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);

        $workSchedule = WorkSchedule::create([
            'name' => 'Standard Schedule',
            'monday_start' => '09:00',
            'monday_end' => '17:00',
            'weekly_hours' => 40,
            'team_id' => $team->id,
        ]);

        $this->assertEquals($team->id, $workSchedule->team_id);
        $this->assertInstanceOf(Team::class, $workSchedule->team);
    }

    public function test_team_has_many_employees()
    {
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);

        Employee::create([
            'employee_number' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'hire_date' => now(),
            'position' => 'Developer',
            'team_id' => $team->id,
        ]);

        Employee::create([
            'employee_number' => 'EMP002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'hire_date' => now(),
            'position' => 'Designer',
            'team_id' => $team->id,
        ]);

        $this->assertEquals(2, $team->employees()->count());
    }

    public function test_team_has_many_departments()
    {
        $team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
        ]);

        Department::create([
            'name' => 'IT',
            'code' => 'IT',
            'team_id' => $team->id,
        ]);

        Department::create([
            'name' => 'HR',
            'code' => 'HR',
            'team_id' => $team->id,
        ]);

        $this->assertEquals(2, $team->departments()->count());
    }
}
