<?php

namespace App\Filament\Resources\WageResource;

use App\Filament\Resources\WageResource\Pages;
use App\Models\Wage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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
        return WageResourceSchema::schema();
    }

    public static function table(Table $table): Table
    {
        return WageResourceTable::table($table);
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
