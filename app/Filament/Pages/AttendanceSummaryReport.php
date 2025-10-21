<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Employee;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use UnitEnum;
use Carbon\Carbon;

class AttendanceSummaryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.attendance-summary-report';

    public ?int $year = null;
    public ?int $month = null;

    public function mount(): void
    {
        $this->year = (int) date('Y');
        $this->month = (int) date('n');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->with(['attendances' => function ($query) {
                        $query->whereYear('date', $this->year)
                            ->whereMonth('date', $this->month);
                    }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_present')
                    ->label('Days Present')
                    ->getStateUsing(function (Employee $record) {
                        return $record->attendances
                            ->where('status', 'present')
                            ->count();
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_absent')
                    ->label('Days Absent')
                    ->getStateUsing(function (Employee $record) {
                        return $record->attendances
                            ->where('status', 'absent')
                            ->count();
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_arrivals')
                    ->label('Late')
                    ->getStateUsing(function (Employee $record) {
                        return $record->attendances
                            ->where('status', 'late')
                            ->count();
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Total Hours')
                    ->getStateUsing(function (Employee $record) {
                        return number_format($record->attendances->sum('total_hours'), 2);
                    })
                    ->suffix(' h')
                    ->sortable(),
                Tables\Columns\TextColumn::make('regular_hours')
                    ->label('Regular Hours')
                    ->getStateUsing(function (Employee $record) {
                        return number_format($record->attendances->sum('regular_hours'), 2);
                    })
                    ->suffix(' h')
                    ->sortable(),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label('Overtime')
                    ->getStateUsing(function (Employee $record) {
                        return number_format($record->attendances->sum('overtime_hours'), 2);
                    })
                    ->suffix(' h')
                    ->sortable()
                    ->color(fn ($state) => floatval($state) > 0 ? 'warning' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
            ])
            ->defaultSort('total_hours', 'desc');
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('year')
                ->label('Year')
                ->options(function () {
                    $currentYear = date('Y');
                    return array_combine(
                        range($currentYear - 5, $currentYear + 1),
                        range($currentYear - 5, $currentYear + 1)
                    );
                })
                ->default(date('Y'))
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->year = $state),
            
            Forms\Components\Select::make('month')
                ->label('Month')
                ->options([
                    1 => 'January', 2 => 'February', 3 => 'March',
                    4 => 'April', 5 => 'May', 6 => 'June',
                    7 => 'July', 8 => 'August', 9 => 'September',
                    10 => 'October', 11 => 'November', 12 => 'December',
                ])
                ->default(date('n'))
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->month = $state),
        ];
    }

    protected function getPeriod(): string
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}
