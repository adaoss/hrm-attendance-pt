<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AttendanceTrendChart extends ChartWidget
{
    protected ?string $heading = 'Attendance Trend (Last 30 Days)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $teamId = auth()->user()->team_id ?? null;
        
        // Get last 30 days of attendance data
        $dates = collect();
        $attendanceCounts = collect();
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates->push($date->format('M d'));
            
            $count = Attendance::whereHas('employee', function ($query) use ($teamId) {
                if ($teamId) {
                    $query->where('team_id', $teamId);
                }
            })
            ->whereDate('date', $date)
            ->count();
            
            $attendanceCounts->push($count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Attendances',
                    'data' => $attendanceCounts->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
                    'display' => true,
                ],
            ],
        ];
    }
}
