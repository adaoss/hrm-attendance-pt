<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Attendance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_in')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_out')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_hours')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h')
                    ->sortable(),
                Tables\Columns\TextColumn::make('regular_hours')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h')
                    ->sortable(),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'warning' : null),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'info' => 'early_departure',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'first_name'),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
