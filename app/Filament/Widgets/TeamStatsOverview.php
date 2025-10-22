<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeamStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $teamId = auth()->user()->team_id;
        
        // Total active employees
        $activeEmployees = Employee::where('team_id', $teamId)
            ->where('is_active', true)
            ->count();
        
        // Total departments
        $departments = Department::where('team_id', $teamId)->count();
        
        // Today's attendance count
        $todayAttendance = Attendance::whereHas('employee', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
            ->whereDate('date', today())
            ->where('status', 'present')
            ->count();
        
        // Pending leave requests
        $pendingLeaves = Leave::whereHas('employee', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
            ->where('status', 'pending')
            ->count();
        
        // Monthly attendance rate
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $workingDays = $this->getWorkingDays($startOfMonth, $endOfMonth);
        
        $monthlyAttendances = Attendance::whereHas('employee', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'present')
            ->count();
        
        $expectedAttendances = $activeEmployees * $workingDays;
        $attendanceRate = $expectedAttendances > 0 
            ? round(($monthlyAttendances / $expectedAttendances) * 100, 1)
            : 0;
        
        return [
            Stat::make('Active Employees', $activeEmployees)
                ->description('Total active team members')
                ->icon('heroicon-o-users')
                ->color('success'),
            
            Stat::make('Departments', $departments)
                ->description('Organizational units')
                ->icon('heroicon-o-building-office-2')
                ->color('info'),
            
            Stat::make('Today\'s Attendance', $todayAttendance)
                ->description('Present today')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            
            Stat::make('Pending Approvals', $pendingLeaves)
                ->description('Leave requests awaiting approval')
                ->icon('heroicon-o-clock')
                ->color($pendingLeaves > 5 ? 'warning' : 'info'),
            
            Stat::make('Monthly Attendance Rate', $attendanceRate . '%')
                ->description('Current month')
                ->icon('heroicon-o-chart-bar')
                ->color($attendanceRate >= 95 ? 'success' : ($attendanceRate >= 85 ? 'warning' : 'danger')),
        ];
    }
    
    /**
     * Calculate working days between two dates (excluding weekends)
     */
    private function getWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;
        $current = $start->copy();
        
        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }
}
