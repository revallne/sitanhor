<?php

namespace App\Observers;

use App\Models\Pengajuan;

class PengajuanObserver
{
    public function creating(Pengajuan $pengajuan): void
    {
        $user = auth()->user();

        if ($user && $user->personel) {
            $pengajuan->personel_nrp = $user->personel->nrp;
        }
        $exists = Pengajuan::where('personel_nrp', $pengajuan->personel_nrp)
            ->where('periode_tahun', $pengajuan->periode_tahun)
            ->where('kategori_kode_kategori', $pengajuan->kategori_kode_kategori)
            ->exists();

        if ($exists) {
            throw new \Exception('Anda sudah memiliki pengajuan untuk periode dan kategori ini.');
        }
    }

    public function updating(Pengajuan $pengajuan): void
    {
        // $pengajuan->status = 'Menunggu Verifikasi';
    }
    /**
     * Handle the Pengajuan "created" event.
     */
    public function created(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "updated" event.
     */
    public function updated(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "deleted" event.
     */
    public function deleted(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "restored" event.
     */
    public function restored(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "force deleted" event.
     */
    public function forceDeleted(Pengajuan $pengajuan): void
    {
        //
    }
}
