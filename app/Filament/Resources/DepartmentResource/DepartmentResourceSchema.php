<?php

namespace App\Filament\Resources\DepartmentResource;

use Filament\Forms;
use Filament\Schemas\Schema;

class DepartmentResourceSchema
{
    public static function schema(): Schema
    {
        return Schema::make()
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('manager_id')
                    ->relationship('manager', 'first_name')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}"),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
