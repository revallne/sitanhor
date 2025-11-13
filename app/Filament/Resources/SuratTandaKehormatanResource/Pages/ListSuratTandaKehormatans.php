<?php

namespace App\Filament\Resources\SuratTandaKehormatanResource\Pages;

use App\Filament\Resources\SuratTandaKehormatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratTandaKehormatans extends ListRecords
{
    protected static string $resource = SuratTandaKehormatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
