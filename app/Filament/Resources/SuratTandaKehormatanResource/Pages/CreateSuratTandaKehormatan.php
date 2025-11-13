<?php

namespace App\Filament\Resources\SuratTandaKehormatanResource\Pages;

use App\Filament\Resources\SuratTandaKehormatanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSuratTandaKehormatan extends CreateRecord
{
    protected static string $resource = SuratTandaKehormatanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $nrp = $data['nrp'] ?? null;
        $periode = $data['periode_tahun'] ?? null;
        $kategori = $data['kategori_kode_kategori'] ?? null;

        if ($nrp && $periode && $kategori) {
            $pengajuan = \App\Models\Pengajuan::where('personel_nrp', $nrp)
                ->where('periode_tahun', $periode)
                ->where('kategori_kode_kategori', $kategori)
                ->first();

            if ($pengajuan) {
                $data['pengajuan_id'] = $pengajuan->id;
            } else {
                throw new \Exception("Pengajuan dengan kombinasi NRP, periode, dan kategori tersebut tidak ditemukan.");
            }
        } else {
            throw new \Exception("NRP, periode, dan kategori wajib diisi.");
        }
        unset($data['nrp'], $data['periode_tahun'], $data['kategori_kode_kategori']);
        return $data;
    }

}
