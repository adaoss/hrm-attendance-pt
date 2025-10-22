<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeaveBalanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Leave Management Overview';
    protected static ?int $sort = 4;
    
    protected function getStats(): array
    {
        $teamId = auth()->user()->team_id;
        $currentYear = Carbon::now()->year;
        
        // Get active employees count
        $activeEmployees = Employee::where('team_id', $teamId)
            ->where('is_active', true)
            ->count();
        
        // Total vacation entitlement (22 days per employee per Portuguese law)
        $totalVacationDays = $activeEmployees * 22;
        
        // Vacation days used
        $vacationUsed = Leave::whereHas('employee', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
            ->where('leave_type', 'vacation')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('days_requested');
        
        // Vacation days remaining
        $vacationRemaining = $totalVacationDays - $vacationUsed;
        
        // Usage percentage
        $usagePercentage = $totalVacationDays > 0 
            ? round(($vacationUsed / $totalVacationDays) * 100, 1)
            : 0;
        
        // Sick leave
        $sickLeave = Leave::whereHas('employee', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
            ->where('leave_type', 'sick')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('days_requested');
        
        // Other leave types (maternity, paternity, etc.)
        $otherLeave = Leave::whereHas('employee', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
            ->whereNotIn('leave_type', ['vacation', 'sick'])
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('days_requested');
        
        return [
            Stat::make('Total Vacation Entitlement', $totalVacationDays . ' days')
                ->description($activeEmployees . ' employees × 22 days')
                ->icon('heroicon-o-calendar')
                ->color('info'),
            
            Stat::make('Vacation Days Used', $vacationUsed . ' days')
                ->description($usagePercentage . '% of total')
                ->icon('heroicon-o-check-circle')
                ->color($usagePercentage >= 75 ? 'warning' : 'success'),
            
            Stat::make('Vacation Remaining', $vacationRemaining . ' days')
                ->description('Available for team')
                ->icon('heroicon-o-calendar-days')
                ->color($vacationRemaining < ($totalVacationDays * 0.25) ? 'danger' : 'success'),
            
            Stat::make('Sick Leave', $sickLeave . ' days')
                ->description('Current year')
                ->icon('heroicon-o-heart')
                ->color('danger'),
            
            Stat::make('Other Leave', $otherLeave . ' days')
                ->description('Maternity, paternity, etc.')
                ->icon('heroicon-o-document-text')
                ->color('info'),
        ];
    }
}
