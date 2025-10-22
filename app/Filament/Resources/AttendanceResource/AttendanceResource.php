<?php

namespace App\Filament\Resources\AttendanceResource;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Attendance';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AttendanceResourceSchema::schema();
    }

    public static function table(Table $table): Table
    {
        return AttendanceResourceTable::table($table);
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
