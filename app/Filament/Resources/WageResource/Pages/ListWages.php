<?php

namespace App\Filament\Resources\WageResource\Pages;

use App\Filament\Resources\WageResource\WageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWages extends ListRecords
{
    protected static string $resource = WageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
