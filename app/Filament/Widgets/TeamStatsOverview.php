<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeamStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get current team from session
        $teamId = auth()->user()->team_id ?? null;
        
        // Total active employees
        $totalEmployees = Employee::when($teamId, function ($query, $teamId) {
            return $query->where('team_id', $teamId);
        })->where('is_active', true)->count();

        // Total departments
        $totalDepartments = Department::when($teamId, function ($query, $teamId) {
            return $query->where('team_id', $teamId);
        })->count();

        // Today's attendance
        $todayAttendance = Attendance::whereHas('employee', function ($query) use ($teamId) {
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
        })->whereDate('date', Carbon::today())->count();

        // Pending leave requests
        $pendingLeaves = Leave::whereHas('employee', function ($query) use ($teamId) {
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
        })->where('status', 'pending')->count();

        // Calculate attendance rate for current month
        $workingDays = Carbon::now()->startOfMonth()->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, Carbon::now());
        
        $expectedAttendances = $totalEmployees * $workingDays;
        $actualAttendances = Attendance::whereHas('employee', function ($query) use ($teamId) {
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
        })
        ->whereMonth('date', Carbon::now()->month)
        ->whereYear('date', Carbon::now()->year)
        ->count();

        $attendanceRate = $expectedAttendances > 0 
            ? round(($actualAttendances / $expectedAttendances) * 100, 1) 
            : 0;

        // Calculate employee counts for the past 8 months
        $employeeCounts = [];
        for ($i = 7; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Employee::when($teamId, function ($query, $teamId) {
                    return $query->where('team_id', $teamId);
                })
                ->where('is_active', true)
                ->whereDate('created_at', '<=', $month->endOfMonth())
                ->count();
            $employeeCounts[] = $count;
        }

        return [
            Stat::make('Total Employees', $totalEmployees)
                ->description('Active employees in the team')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart($employeeCounts),
            
            Stat::make('Departments', $totalDepartments)
                ->description('Organizational units')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
            
            Stat::make('Today\'s Attendance', $todayAttendance)
                ->description("Out of {$totalEmployees} employees")
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
            
            Stat::make('Pending Leave Requests', $pendingLeaves)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingLeaves > 5 ? 'warning' : 'success'),
            
            Stat::make('Attendance Rate', $attendanceRate . '%')
                ->description('Current month attendance')
                ->descriptionIcon($attendanceRate >= 90 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($attendanceRate >= 90 ? 'success' : ($attendanceRate >= 75 ? 'warning' : 'danger')),
        ];
    }
}
