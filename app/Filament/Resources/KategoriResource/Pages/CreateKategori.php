<?php

namespace App\Filament\Resources\KategoriResource\Pages;

use App\Filament\Resources\KategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKategori extends CreateRecord
{
    protected static string $resource = KategoriResource::class;
    protected static ?string $title = 'Buat Kategori Baru';

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
