<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;

class EmployeesByDepartmentChart extends ChartWidget
{
    protected ?string $heading = 'Employees by Department';
    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        $teamId = auth()->user()->team_id;
        
        $departments = Department::where('team_id', $teamId)
            ->withCount('employees')
            ->get();
        
        $colors = [
            '#3b82f6', // blue
            '#10b981', // green
            '#f59e0b', // amber
            '#ef4444', // red
            '#8b5cf6', // purple
            '#ec4899', // pink
            '#06b6d4', // cyan
            '#f97316', // orange
        ];
        
        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $departments->pluck('employees_count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $departments->count()),
                ],
            ],
            'labels' => $departments->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}
