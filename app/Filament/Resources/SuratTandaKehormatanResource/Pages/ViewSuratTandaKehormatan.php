<?php

namespace App\Filament\Resources\SuratTandaKehormatanResource\Pages;

use App\Filament\Resources\SuratTandaKehormatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSuratTandaKehormatan extends ViewRecord
{
    protected static string $resource = SuratTandaKehormatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
