<?php

namespace App\Filament\Resources\SatkerResource\Pages;

use App\Filament\Resources\SatkerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSatker extends CreateRecord
{
    protected static string $resource = SatkerResource::class;

    protected static ?string $title = 'Buat Satker Baru';

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Data Baru'), // Mengubah label "Create"
            
            $this->getCreateAnotherFormAction()
                ->label('Simpan & Buat Lainnya'), // Mengubah label "Create & create another"
                
            $this->getCancelFormAction()
                ->label('Batalkan'), // Mengubah label "Cancel"
        ];
    }
}
