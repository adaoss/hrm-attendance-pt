<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingApprovalsWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        $teamId = auth()->user()->team_id;
        
        return $table
            ->query(
                Leave::query()
                    ->whereHas('employee', function ($query) use ($teamId) {
                        $query->where('team_id', $teamId);
                    })
                    ->where('status', 'pending')
                    ->orderBy('start_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Leave Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vacation' => 'success',
                        'sick' => 'danger',
                        'maternity' => 'info',
                        'paternity' => 'info',
                        'parental' => 'info',
                        'marriage' => 'warning',
                        'bereavement' => 'gray',
                        'unpaid' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vacation' => 'Férias',
                        'sick' => 'Baixa Médica',
                        'maternity' => 'Maternidade',
                        'paternity' => 'Paternidade',
                        'parental' => 'Parental',
                        'marriage' => 'Casamento',
                        'bereavement' => 'Luto',
                        'unpaid' => 'Sem Vencimento',
                        default => ucfirst($state),
                    }),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('days_requested')
                    ->label('Days')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Leave $record): string => \App\Filament\Resources\LeaveResource\LeaveResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])
            ->heading('Pending Leave Approvals')
            ->description('Leave requests awaiting your review and approval')
            ->emptyStateHeading('No Pending Approvals')
            ->emptyStateDescription('All leave requests have been processed.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
