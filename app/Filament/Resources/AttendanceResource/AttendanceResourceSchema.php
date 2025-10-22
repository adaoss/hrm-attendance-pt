<?php

namespace App\Filament\Resources\AttendanceResource;

use Filament\Forms;
use Filament\Schemas\Schema;

class AttendanceResourceSchema
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
                
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->default(now()),
                
                Forms\Components\Section::make('Time Records')
                    ->schema([
                        Forms\Components\DateTimePicker::make('clock_in')
                            ->label('Clock In'),
                        Forms\Components\DateTimePicker::make('clock_out')
                            ->label('Clock Out'),
                        Forms\Components\DateTimePicker::make('break_start')
                            ->label('Break Start'),
                        Forms\Components\DateTimePicker::make('break_end')
                            ->label('Break End'),
                    ])->columns(2),

                Forms\Components\Section::make('Calculated Hours')
                    ->schema([
                        Forms\Components\TextInput::make('total_hours')
                            ->numeric()
                            ->disabled()
                            ->helperText('Automatically calculated'),
                        Forms\Components\TextInput::make('regular_hours')
                            ->numeric()
                            ->disabled()
                            ->helperText('Maximum 8 hours per day'),
                        Forms\Components\TextInput::make('overtime_hours')
                            ->numeric()
                            ->disabled()
                            ->helperText('Hours exceeding 8 per day'),
                    ])->columns(3),

                Forms\Components\Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'early_departure' => 'Early Departure',
                    ])
                    ->required()
                    ->default('present'),

                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
