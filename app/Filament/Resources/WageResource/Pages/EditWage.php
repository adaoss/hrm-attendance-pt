<?php

namespace App\Filament\Resources\WageResource\Pages;

use App\Filament\Resources\WageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWage extends EditRecord
{
    protected static string $resource = WageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
