<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PortugueseLaborLawService;
use App\Models\Employee;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PortugueseLaborLawServiceTest extends TestCase
{
    use RefreshDatabase;

    private PortugueseLaborLawService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PortugueseLaborLawService();
        
        // Seed leave types for testing
        $this->seed(\Database\Seeders\LeaveTypeSeeder::class);
    }

    public function test_overtime_rate_calculation_first_two_hours(): void
    {
        $result = $this->service->calculateOvertimeRate(2, false);
        
        $this->assertEquals(2, $result['hours']);
        $this->assertEquals(1.5, $result['rate']);
    }

    public function test_overtime_rate_calculation_additional_hours(): void
    {
        $result = $this->service->calculateOvertimeRate(4, false);
        
        $this->assertArrayHasKey('hours_first_2', $result);
        $this->assertArrayHasKey('hours_additional', $result);
        $this->assertEquals(2, $result['hours_first_2']);
        $this->assertEquals(1.5, $result['rate_first_2']);
        $this->assertEquals(2, $result['hours_additional']);
        $this->assertEquals(1.75, $result['rate_additional']);
    }

    public function test_overtime_rate_weekend_holiday(): void
    {
        $result = $this->service->calculateOvertimeRate(3, true);
        
        $this->assertEquals(3, $result['hours']);
        $this->assertEquals(2.0, $result['rate']);
    }

    public function test_working_days_calculation_excludes_weekends(): void
    {
        $startDate = Carbon::parse('2024-01-01'); // Monday
        $endDate = Carbon::parse('2024-01-07'); // Sunday

        $workingDays = $this->service->calculateWorkingDays($startDate, $endDate);

        $this->assertEquals(5, $workingDays); // Monday to Friday
    }

    public function test_is_working_day_weekend(): void
    {
        $saturday = Carbon::parse('2024-01-06'); // Saturday
        $sunday = Carbon::parse('2024-01-07'); // Sunday

        $this->assertFalse($this->service->isWorkingDay($saturday));
        $this->assertFalse($this->service->isWorkingDay($sunday));
    }

    public function test_leave_entitlement_maternity(): void
    {
        $entitlement = $this->service->getLeaveEntitlement('maternity');

        $this->assertNotNull($entitlement);
        $this->assertEquals(150, $entitlement['days']);
        $this->assertTrue($entitlement['paid']);
    }

    public function test_leave_entitlement_paternity(): void
    {
        $entitlement = $this->service->getLeaveEntitlement('paternity');

        $this->assertNotNull($entitlement);
        $this->assertEquals(28, $entitlement['days']);
        $this->assertTrue($entitlement['paid']);
    }
}
