<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LeavesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|UnitEnum|null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.leaves-report';

    public ?int $year = null;

    public function mount(): void
    {
        $this->year = (int) date('Y');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()
                    ->with(['leaves' => function ($query) {
                        $query->where('status', 'approved')
                            ->whereYear('start_date', $this->year);
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
                Tables\Columns\TextColumn::make('vacation_days_used')
                    ->label('Vacation Days')
                    ->getStateUsing(function (Employee $record) {
                        return $record->leaves
                            ->where('leave_type', 'vacation')
                            ->sum('days_requested');
                    })
                    ->suffix(' days'),
                Tables\Columns\TextColumn::make('sick_days_used')
                    ->label('Sick Days')
                    ->getStateUsing(function (Employee $record) {
                        return $record->leaves
                            ->where('leave_type', 'sick')
                            ->sum('days_requested');
                    })
                    ->suffix(' days'),
                Tables\Columns\TextColumn::make('other_leave_days')
                    ->label('Other Leaves')
                    ->getStateUsing(function (Employee $record) {
                        return $record->leaves
                            ->whereNotIn('leave_type', ['vacation', 'sick'])
                            ->sum('days_requested');
                    })
                    ->suffix(' days'),
                Tables\Columns\TextColumn::make('total_leave_days')
                    ->label('Total Days')
                    ->getStateUsing(function (Employee $record) {
                        return $record->leaves->sum('days_requested');
                    })
                    ->suffix(' days')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
            ])
            ->defaultSort('total_leave_days', 'desc');
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('year')
                ->label('Select Year')
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
        ];
    }
}
