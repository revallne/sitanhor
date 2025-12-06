<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;
    protected ?string $heading = 'Pengajuan Baru';

    protected ?string $subheading = 'Formulir Pengajuan Tanda Kehormatan Polri';

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
