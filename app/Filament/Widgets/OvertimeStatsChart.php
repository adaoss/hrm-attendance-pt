<?php

namespace App\Filament\Widgets;

use App\Models\Overtime;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class OvertimeStatsChart extends ChartWidget
{
    protected ?string $heading = 'Overtime Hours (Current Month)';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $teamId = auth()->user()->team_id ?? null;
        
        // Get employees with overtime in current month
        $overtimeData = Overtime::whereHas('employee', function ($query) use ($teamId) {
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
        })
        ->whereMonth('date', Carbon::now()->month)
        ->whereYear('date', Carbon::now()->year)
        ->selectRaw('employee_id, SUM(hours) as total_hours')
        ->groupBy('employee_id')
        ->with('employee')
        ->get();

        // Get top 10 employees with most overtime
        $topOvertime = $overtimeData->sortByDesc('total_hours')->take(10);

        return [
            'datasets' => [
                [
                    'label' => 'Overtime Hours',
                    'data' => $topOvertime->pluck('total_hours')->toArray(),
                    'backgroundColor' => 'rgba(251, 146, 60, 0.7)',
                    'borderColor' => 'rgb(251, 146, 60)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $topOvertime->map(function ($item) {
                if ($item->employee) {
                    return $item->employee->first_name . ' ' . substr($item->employee->last_name, 0, 1) . '.';
                } else {
                    return 'Unknown';
                }
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
