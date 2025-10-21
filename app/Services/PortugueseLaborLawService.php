<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Holiday;
use Carbon\Carbon;

/**
 * Portuguese Labor Law Service
 * 
 * Implements Portuguese labor law requirements:
 * - Working hours: 8h/day, 40h/week (Código do Trabalho, Article 203)
 * - Overtime rates: 50% for first 2 hours, 75% for additional (Article 268)
 * - Rest periods: 11 consecutive hours between shifts (Article 214)
 * - Weekly rest: 1 day per week, preferably Sunday (Article 232)
 * - Vacation: 22 working days per year (Article 238)
 */
class PortugueseLaborLawService
{
    /**
     * Calculate overtime hours and apply correct rate
     * Article 268 - Overtime compensation rates
     */
    public function calculateOvertimeRate(float $overtimeHours, bool $isWeekendOrHoliday = false): array
    {
        if ($isWeekendOrHoliday) {
            // Weekends and holidays: 100% extra (2.0x rate)
            return [
                'hours' => $overtimeHours,
                'rate' => 2.0,
                'description' => 'Weekend/Holiday rate (100% extra)'
            ];
        }

        // Weekday overtime
        if ($overtimeHours <= 2) {
            // First 2 hours: 50% extra (1.5x rate)
            return [
                'hours' => $overtimeHours,
                'rate' => 1.5,
                'description' => 'First 2 hours (50% extra)'
            ];
        } else {
            // Additional hours: 75% extra (1.75x rate)
            return [
                'hours_first_2' => 2,
                'rate_first_2' => 1.5,
                'hours_additional' => $overtimeHours - 2,
                'rate_additional' => 1.75,
                'description' => 'Mixed rate (50% for first 2h, 75% for additional)'
            ];
        }
    }

    /**
     * Validate working hours don't exceed legal limits
     * Article 203 - Normal working period
     */
    public function validateWorkingHours(Employee $employee, Carbon $weekStart): array
    {
        $weekEnd = $weekStart->copy()->addDays(6);
        
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $totalHours = $attendances->sum('total_hours');
        $regularHours = $attendances->sum('regular_hours');
        $overtimeHours = $attendances->sum('overtime_hours');

        $violations = [];

        // Check weekly limit (40 hours regular + reasonable overtime)
        if ($regularHours > 40) {
            $violations[] = [
                'type' => 'weekly_hours_exceeded',
                'message' => "Regular weekly hours ({$regularHours}h) exceed legal limit of 40h",
                'severity' => 'high'
            ];
        }

        // Check daily limits in each attendance
        foreach ($attendances as $attendance) {
            if ($attendance->regular_hours > 8) {
                $violations[] = [
                    'type' => 'daily_hours_exceeded',
                    'date' => $attendance->date->format('Y-m-d'),
                    'message' => "Daily hours ({$attendance->regular_hours}h) exceed legal limit of 8h",
                    'severity' => 'high'
                ];
            }

            // Check rest period (11 hours)
            if (!$attendance->hasAdequateRestPeriod()) {
                $violations[] = [
                    'type' => 'insufficient_rest',
                    'date' => $attendance->date->format('Y-m-d'),
                    'message' => 'Less than 11 hours rest between shifts (Article 214)',
                    'severity' => 'critical'
                ];
            }
        }

        return [
            'is_compliant' => empty($violations),
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'violations' => $violations
        ];
    }

    /**
     * Calculate vacation entitlement
     * Article 238 - Duration of vacation (22 working days)
     */
    public function calculateVacationEntitlement(Employee $employee, int $year): array
    {
        $hireDate = $employee->hire_date;
        $hireYear = $hireDate->year;

        // First year - proportional calculation
        if ($year == $hireYear) {
            $monthsWorked = 12 - $hireDate->month + 1;
            $entitledDays = floor(($monthsWorked / 12) * 22);
            
            return [
                'entitled_days' => (int) $entitledDays,
                'is_first_year' => true,
                'months_worked' => $monthsWorked,
                'description' => 'Proportional vacation days for first year'
            ];
        }

        // Subsequent years - full entitlement
        return [
            'entitled_days' => 22,
            'is_first_year' => false,
            'description' => 'Full annual vacation entitlement'
        ];
    }

    /**
     * Calculate used and remaining vacation days
     */
    public function getVacationBalance(Employee $employee, int $year): array
    {
        $entitlement = $this->calculateVacationEntitlement($employee, $year);
        
        $usedDays = Leave::where('employee_id', $employee->id)
            ->where('leave_type', Leave::TYPE_VACATION)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_requested');

        $remainingDays = $entitlement['entitled_days'] - $usedDays;

        return [
            'year' => $year,
            'entitled_days' => $entitlement['entitled_days'],
            'used_days' => $usedDays,
            'remaining_days' => max(0, $remainingDays),
            'is_first_year' => $entitlement['is_first_year']
        ];
    }

    /**
     * Check if date is a working day (not weekend or holiday)
     */
    public function isWorkingDay(Carbon $date): bool
    {
        // Check if weekend
        if ($date->isWeekend()) {
            return false;
        }

        // Check if national holiday
        return !Holiday::isHoliday($date->toDateString());
    }

    /**
     * Calculate number of working days between two dates
     */
    public function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if ($this->isWorkingDay($current)) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Get leave entitlement for specific leave type
     * Based on Portuguese Labor Code
     */
    public function getLeaveEntitlement(string $leaveType): ?array
    {
        return match($leaveType) {
            Leave::TYPE_VACATION => [
                'days' => 22,
                'description' => 'Annual vacation (Article 238)',
                'paid' => true
            ],
            Leave::TYPE_MATERNITY => [
                'days' => 150,
                'min_days' => 120,
                'description' => 'Maternity leave (120-150 days, Article 40)',
                'paid' => true
            ],
            Leave::TYPE_PATERNITY => [
                'days' => 28,
                'description' => 'Paternity leave (28 days, Article 43)',
                'paid' => true
            ],
            Leave::TYPE_MARRIAGE => [
                'days' => 15,
                'description' => 'Marriage leave (15 days, Article 252)',
                'paid' => true
            ],
            Leave::TYPE_BEREAVEMENT => [
                'days' => 5,
                'description' => 'Bereavement leave (5 days, Article 252)',
                'paid' => true
            ],
            default => null
        };
    }
}
