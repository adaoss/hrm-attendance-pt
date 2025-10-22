<?php

namespace Tests\Unit\Widgets;

use App\Filament\Widgets\AttendanceTrendChart;
use App\Filament\Widgets\EmployeesByDepartmentChart;
use App\Filament\Widgets\LeaveBalanceWidget;
use App\Filament\Widgets\OvertimeStatsChart;
use App\Filament\Widgets\PendingApprovalsWidget;
use App\Filament\Widgets\TeamStatsOverview;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test team
        $this->team = Team::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'description' => 'Test organization',
            'is_active' => true,
        ]);

        // Create a test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'team_id' => $this->team->id,
            'is_admin' => true,
        ]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    public function test_team_stats_overview_widget_exists()
    {
        $widget = new TeamStatsOverview();
        $this->assertInstanceOf(TeamStatsOverview::class, $widget);
    }

    public function test_employees_by_department_chart_widget_exists()
    {
        $widget = new EmployeesByDepartmentChart();
        $this->assertInstanceOf(EmployeesByDepartmentChart::class, $widget);
    }

    public function test_attendance_trend_chart_widget_exists()
    {
        $widget = new AttendanceTrendChart();
        $this->assertInstanceOf(AttendanceTrendChart::class, $widget);
    }

    public function test_leave_balance_widget_exists()
    {
        $widget = new LeaveBalanceWidget();
        $this->assertInstanceOf(LeaveBalanceWidget::class, $widget);
    }

    public function test_overtime_stats_chart_widget_exists()
    {
        $widget = new OvertimeStatsChart();
        $this->assertInstanceOf(OvertimeStatsChart::class, $widget);
    }

    public function test_pending_approvals_widget_exists()
    {
        $widget = new PendingApprovalsWidget();
        $this->assertInstanceOf(PendingApprovalsWidget::class, $widget);
    }

    public function test_widgets_have_correct_sort_order()
    {
        // Verify widgets have sort property defined
        // This ensures they appear in the correct order on the dashboard
        $reflection = new \ReflectionClass(TeamStatsOverview::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);
        $this->assertEquals(1, $property->getDefaultValue());
        
        $reflection = new \ReflectionClass(EmployeesByDepartmentChart::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);
        $this->assertEquals(2, $property->getDefaultValue());
        
        $reflection = new \ReflectionClass(AttendanceTrendChart::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);
        $this->assertEquals(3, $property->getDefaultValue());
        
        $reflection = new \ReflectionClass(LeaveBalanceWidget::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);
        $this->assertEquals(4, $property->getDefaultValue());
        
        $reflection = new \ReflectionClass(OvertimeStatsChart::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);
        $this->assertEquals(5, $property->getDefaultValue());
        
        $reflection = new \ReflectionClass(PendingApprovalsWidget::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);
        $this->assertEquals(6, $property->getDefaultValue());
    }

    public function test_team_stats_overview_returns_stats_with_no_data()
    {
        $widget = new TeamStatsOverview();
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);
        $stats = $method->invoke($widget);
        
        $this->assertIsArray($stats);
        $this->assertCount(5, $stats); // Should have 5 stats
    }

    public function test_employees_by_department_chart_returns_data_with_departments()
    {
        // Create a department
        Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'team_id' => $this->team->id,
        ]);

        $widget = new EmployeesByDepartmentChart();
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
    }

    public function test_attendance_trend_chart_returns_data()
    {
        $widget = new AttendanceTrendChart();
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
    }

    public function test_leave_balance_widget_returns_stats()
    {
        $widget = new LeaveBalanceWidget();
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);
        $stats = $method->invoke($widget);
        
        $this->assertIsArray($stats);
        $this->assertCount(5, $stats); // Should have 5 stats
    }

    public function test_overtime_stats_chart_returns_data()
    {
        $widget = new OvertimeStatsChart();
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
    }

    public function test_widgets_filter_by_team()
    {
        // Create another team
        $otherTeam = Team::create([
            'name' => 'Other Company',
            'slug' => 'other-company',
            'is_active' => true,
        ]);

        // Create departments for both teams
        $ourDept = Department::create([
            'name' => 'Our Department',
            'code' => 'OUR',
            'team_id' => $this->team->id,
        ]);

        $otherDept = Department::create([
            'name' => 'Other Department',
            'code' => 'OTH',
            'team_id' => $otherTeam->id,
        ]);

        // Check that widget only shows our team's data
        $widget = new EmployeesByDepartmentChart();
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);
        
        $this->assertIsArray($data['labels']);
        $this->assertContains('Our Department', $data['labels']);
        $this->assertNotContains('Other Department', $data['labels']);
    }
}
