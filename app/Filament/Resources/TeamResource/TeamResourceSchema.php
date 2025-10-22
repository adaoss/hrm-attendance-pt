<?php

namespace App\Filament\Resources\TeamResource;

use Filament\Forms;
use Filament\Schemas\Schema;

class TeamResourceSchema
{
    public static function schema(): Schema
    {
        return Schema::make()
            ->components([
                Forms\Components\Section::make('Team Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for the team'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->keyLabel('Setting Name')
                            ->valueLabel('Setting Value')
                            ->helperText('Team-specific configuration settings'),
                    ]),
            ]);
    }
}
