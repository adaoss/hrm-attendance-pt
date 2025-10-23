<?php

namespace App\Filament\Widgets;

use App\Models\Overtime;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OvertimeStatsChart extends ChartWidget
{
    protected ?string $heading = 'Top 10 Overtime Hours (Current Month)';
    protected static ?int $sort = 5;
    
    protected function getData(): array
    {
        $teamId = auth()->user()->team_id;
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get top 10 employees with most overtime hours for current month
        $overtimeData = Overtime::whereHas('employee', function ($query) use ($teamId) {
            $query->where('team_id', $teamId);
        })
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'rejected')
            ->select('employee_id', DB::raw('SUM(hours) as total_hours'))
            ->groupBy('employee_id')
            ->orderByDesc('total_hours')
            ->limit(10)
            ->with('employee')
            ->get();
        
        $labels = [];
        $hours = [];
        
        foreach ($overtimeData as $record) {
            if ($record->employee) {
                $labels[] = $record->employee->first_name . ' ' . substr($record->employee->last_name, 0, 1) . '.';
                $hours[] = (float) $record->total_hours;
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Overtime Hours',
                    'data' => $hours,
                    'backgroundColor' => [
                        '#f59e0b', '#fb923c', '#fdba74', '#fed7aa', '#ffedd5',
                        '#fef3c7', '#fde68a', '#fcd34d', '#fbbf24', '#f59e0b',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Hours',
                    ],
                ],
            ],
        ];
    }
}
