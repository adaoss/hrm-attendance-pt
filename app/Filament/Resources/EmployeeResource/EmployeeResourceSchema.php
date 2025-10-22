<?php

namespace App\Filament\Resources\EmployeeResource;

use Filament\Forms;
use Filament\Schemas\Schema;

class EmployeeResourceSchema
{
    public static function schema(): Schema
    {
        return Schema::make()
            ->components([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('employee_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Employee Number'),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth'),
                    ])->columns(2),

                Forms\Components\Section::make('Portuguese Identification')
                    ->schema([
                        Forms\Components\TextInput::make('nif')
                            ->label('NIF (Tax ID)')
                            ->mask('999999999')
                            ->length(9)
                            ->helperText('Portuguese Tax Identification Number'),
                        Forms\Components\TextInput::make('niss')
                            ->label('NISS (Social Security)')
                            ->mask('99999999999')
                            ->length(11)
                            ->helperText('Portuguese Social Security Number'),
                    ])->columns(2),

                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\DatePicker::make('hire_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('department_id')
                            ->relationship('department', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('position')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('contract_type')
                            ->options([
                                'permanent' => 'Permanent',
                                'fixed_term' => 'Fixed Term',
                                'temporary' => 'Temporary',
                            ])
                            ->required()
                            ->default('permanent'),
                        Forms\Components\Select::make('work_schedule_id')
                            ->relationship('workSchedule', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('weekly_hours')
                            ->numeric()
                            ->default(40)
                            ->minValue(0)
                            ->maxValue(40)
                            ->helperText('Maximum 40 hours per week (Portuguese law)'),
                    ])->columns(2),

                Forms\Components\Section::make('ZKTeco Integration')
                    ->schema([
                        Forms\Components\TextInput::make('zkteco_user_id')
                            ->label('ZKTeco User ID')
                            ->helperText('User ID in ZKTeco attendance device'),
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
