<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class EmployeesByDepartmentChart extends ChartWidget
{
    protected ?string $heading = 'Employees by Department';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $teamId = auth()->user()->team_id ?? null;
        
        $departments = Department::when($teamId, function ($query, $teamId) {
            return $query->where('team_id', $teamId);
        })
        ->withCount('employees')
        ->having('employees_count', '>', 0)
        ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $departments->pluck('employees_count')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                        'rgb(168, 85, 247)',
                        'rgb(236, 72, 153)',
                        'rgb(14, 165, 233)',
                    ],
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
        ];
    }
}
