<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeaveBalanceWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $teamId = auth()->user()->team_id ?? null;
        $currentYear = Carbon::now()->year;

        // Total vacation days entitled (22 per employee as per Portuguese law)
        $activeEmployees = Employee::when($teamId, function ($query, $teamId) {
            return $query->where('team_id', $teamId);
        })->where('is_active', true)->count();
        
        $totalVacationDays = $activeEmployees * 22;

        // Vacation days used this year
        $vacationUsed = Leave::whereHas('employee', function ($query) use ($teamId) {
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
        })
        ->where('leave_type', 'vacation')
        ->where('status', 'approved')
        ->whereYear('start_date', $currentYear)
        ->sum('days_requested');

        // Sick leave days this year
        $sickDays = Leave::whereHas('employee', function ($query) use ($teamId) {
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
        })
        ->where('leave_type', 'sick')
        ->where('status', 'approved')
        ->whereYear('start_date', $currentYear)
        ->sum('days_requested');

        // Other leave types
        $otherLeaves = Leave::whereHas('employee', function ($query) use ($teamId) {
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
        })
        ->whereNotIn('leave_type', ['vacation', 'sick'])
        ->where('status', 'approved')
        ->whereYear('start_date', $currentYear)
        ->sum('days_requested');

        $vacationRemaining = $totalVacationDays - $vacationUsed;
        $vacationUsagePercent = $totalVacationDays > 0 
            ? round(($vacationUsed / $totalVacationDays) * 100, 1) 
            : 0;

        return [
            Stat::make('Total Vacation Entitlement', $totalVacationDays . ' days')
                ->description($activeEmployees . ' employees × 22 days')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            
            Stat::make('Vacation Used', $vacationUsed . ' days')
                ->description("{$vacationUsagePercent}% of total")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($vacationUsagePercent > 75 ? 'warning' : 'success'),
            
            Stat::make('Vacation Remaining', $vacationRemaining . ' days')
                ->description('Available for use')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
            
            Stat::make('Sick Leave', $sickDays . ' days')
                ->description('Taken this year')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning'),
            
            Stat::make('Other Leaves', $otherLeaves . ' days')
                ->description('Maternity, paternity, etc.')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),
        ];
    }
}
