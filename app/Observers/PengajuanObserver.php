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
