<?php

namespace App\Filament\Resources\WageResource;

use Filament\Forms;
use Filament\Schemas\Schema;

class WageResourceSchema
{
    public static function schema(): Schema
    {
        return Schema::make()
            ->components([
                Forms\Components\Section::make('Employee & Period')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->employee_number})"),
                        Forms\Components\Select::make('year')
                            ->required()
                            ->options(function () {
                                $currentYear = date('Y');
                                return array_combine(
                                    range($currentYear - 5, $currentYear + 1),
                                    range($currentYear - 5, $currentYear + 1)
                                );
                            })
                            ->default(date('Y')),
                        Forms\Components\Select::make('month')
                            ->required()
                            ->options([
                                1 => 'January', 2 => 'February', 3 => 'March',
                                4 => 'April', 5 => 'May', 6 => 'June',
                                7 => 'July', 8 => 'August', 9 => 'September',
                                10 => 'October', 11 => 'November', 12 => 'December',
                            ])
                            ->default(date('n')),
                    ])->columns(3),

                Forms\Components\Section::make('Salary Details')
                    ->schema([
                        Forms\Components\TextInput::make('base_salary')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('regular_hours_worked')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('h'),
                        Forms\Components\TextInput::make('overtime_hours')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('h'),
                        Forms\Components\TextInput::make('overtime_pay')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('bonuses')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->default(0),
                        Forms\Components\TextInput::make('deductions')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Totals')
                    ->schema([
                        Forms\Components\TextInput::make('gross_pay')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('net_pay')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\DatePicker::make('payment_date'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'calculated' => 'Calculated',
                                'paid' => 'Paid',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
