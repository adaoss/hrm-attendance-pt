<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AttendanceTrendChart extends ChartWidget
{
    protected ?string $heading = '30-Day Attendance Trend';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $teamId = auth()->user()->team_id;
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(30);
        
        $attendanceData = [];
        $dates = [];
        
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dates[] = $current->format('M d');
            
            $count = Attendance::whereHas('employee', function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })
                ->whereDate('date', $current)
                ->where('status', 'present')
                ->count();
            
            $attendanceData[] = $count;
            $current->addDay();
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $attendanceData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
