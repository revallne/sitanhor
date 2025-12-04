<?php

namespace App\Filament\Resources\PeriodeResource\Pages;

use App\Filament\Resources\PeriodeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePeriode extends CreateRecord
{
    protected static string $resource = PeriodeResource::class;

    protected ?string $heading = 'Buat Periode Baru';

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
