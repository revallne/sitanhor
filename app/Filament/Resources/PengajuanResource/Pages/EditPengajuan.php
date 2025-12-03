<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Lihat Detail'),
            Actions\DeleteAction::make()->label('Hapus Pengajuan'),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set status kembali ke "Menunggu Verifikasi" saat diedit
        $data['status'] = 'Menunggu Verifikasi';
        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan'), // Ubah label di sini
            
            $this->getCancelFormAction()
                ->label('Batalkan Edit'), // Ubah label di sini
        ];
    }
}
