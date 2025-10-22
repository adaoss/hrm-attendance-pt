<?php

namespace App\Filament\Resources\WageResource;

use Filament\Tables;
use Filament\Tables\Table;

class WageResourceTable
{
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
}
