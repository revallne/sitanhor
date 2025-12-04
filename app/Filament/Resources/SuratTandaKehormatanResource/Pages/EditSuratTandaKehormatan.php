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
            Actions\DeleteAction::make()->label('Hapus'),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ambil record yang sedang diedit
        $record = $this->record;

        // Jika ada relasi pengajuan
        if ($record->pengajuan) {

            $data['nrp'] = $record->pengajuan->personel_nrp;
            $data['periode_tahun'] = $record->pengajuan->periode_tahun;
            $data['kategori_kode_kategori'] = $record->pengajuan->kategori_kode_kategori;
        }

        return $data;
    }


    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     // Jika ada field read-only dari Personel/Pengajuan yang tidak perlu disimpan, 
    //     // hapus dari array $data di sini:
    //     unset($data['personel_nrp']);
    //     unset($data['nama_personel']);
    //     unset($data['kategori_tanhor']);

    //     // Contoh: Logika update status Pengajuan
    //     $suratRecord = $this->getRecord();
    //     $pengajuan = $suratRecord->pengajuan;

    //     if ($pengajuan && $pengajuan->status !== 'Diserahkan') {
    //         // Misalnya, set status pengajuan menjadi 'Diserahkan' setelah surat dibuat/diupdate
    //         // Jika Anda memiliki kolom status pengajuan untuk proses penyerahan
    //         // $pengajuan->update(['status' => 'Diserahkan']); 
    //     }

    //     return $data;
    // }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan Data'), // Ubah label di sini

            $this->getCancelFormAction()
                ->label('Batalkan Edit'), // Ubah label di sini
        ];
    }
}
