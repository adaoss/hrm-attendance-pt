<?php

namespace App\Filament\Resources\LeaveResource;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|UnitEnum|null $navigationGroup = 'Attendance';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return LeaveResourceSchema::schema();
    }

    public static function table(Table $table): Table
    {
        return LeaveResourceTable::table($table);
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
