<?php

namespace App\Filament\Resources\SuratTandaKehormatanResource\Pages;

use App\Filament\Resources\SuratTandaKehormatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratTandaKehormatan extends EditRecord
{
    protected static string $resource = SuratTandaKehormatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
