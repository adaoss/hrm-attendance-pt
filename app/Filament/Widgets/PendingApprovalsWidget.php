<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use App\Models\Overtime;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingApprovalsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending Approvals';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $teamId = auth()->user()->team_id ?? null;

        return $table
            ->query(
                Leave::query()
                    ->whereHas('employee', function ($query) use ($teamId) {
                        if ($teamId) {
                            $query->where('team_id', $teamId);
                        }
                    })
                    ->where('status', 'pending')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('leave_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'vacation' => 'success',
                        'sick' => 'warning',
                        'maternity', 'paternity' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('days_requested')
                    ->label('Days')
                    ->alignCenter()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Leave $record): string => route('filament.admin.resources.leaves.edit', $record)),
            ]);
    }
}
