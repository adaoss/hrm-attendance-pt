<?php

namespace App\Filament\Resources\LeaveResource;

use App\Models\Leave;
use App\Services\PortugueseLaborLawService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class LeaveResourceSchema
{
    public static function schema(): Schema
    {
        return Schema::make()
            ->components([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'first_name')
                    ->searchable()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->employee_number})"),
                
                Forms\Components\Select::make('leave_type')
                    ->options(Leave::getLeaveTypes())
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $entitlement = (new PortugueseLaborLawService())->getLeaveEntitlement($state);
                        if ($entitlement) {
                            Notification::make()
                                ->title($entitlement['description'])
                                ->info()
                                ->send();
                        }
                    }),

                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->reactive(),
                
                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                        $startDate = $get('start_date');
                        if ($startDate && $state) {
                            $laborService = new PortugueseLaborLawService();
                            $days = $laborService->calculateWorkingDays(
                                \Carbon\Carbon::parse($startDate),
                                \Carbon\Carbon::parse($state)
                            );
                            $set('days_requested', $days);
                        }
                    }),

                Forms\Components\TextInput::make('days_requested')
                    ->numeric()
                    ->required()
                    ->disabled()
                    ->helperText('Calculated based on working days (excluding weekends and holidays)'),

                Forms\Components\Textarea::make('reason')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }
}
