<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WageResource\Pages;
use App\Models\Wage;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class WageResource extends Resource
{
    protected static ?string $model = Wage::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-euro';
    protected static string|UnitEnum|null $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Wages';

    public static function form(Schema $schema): Schema
    {
        return $schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1)))
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_salary')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->suffix(' h')
                    ->sortable(),
                Tables\Columns\TextColumn::make('overtime_pay')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_pay')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_pay')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'calculated',
                        'success' => 'paid',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name'),
                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        $currentYear = date('Y');
                        return array_combine(
                            range($currentYear - 5, $currentYear + 1),
                            range($currentYear - 5, $currentYear + 1)
                        );
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'calculated' => 'Calculated',
                        'paid' => 'Paid',
                    ]),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWages::route('/'),
            'create' => Pages\CreateWage::route('/create'),
            'edit' => Pages\EditWage::route('/{record}/edit'),
        ];
    }
}
