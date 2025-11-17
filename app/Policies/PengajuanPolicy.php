<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PengajuanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pengajuan $pengajuan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya role personel
        if (! $user->hasRole('personel')) {
            return false;
        }

        // Ambil periode yang statusnya Buka dan tanggalnya valid
        $periodeAktif = \App\Models\Periode::where('status', 'Buka')
            ->whereDate('tanggalAwal', '<=', today())
            ->whereDate('tanggalAkhir', '>=', today())
            ->first();

        // Jika tidak ada periode yang aktif pada rentang tanggal
        if (! $periodeAktif) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pengajuan $pengajuan): bool
    {
        // Ambil nrp personel dari user yang login
        $loggedInNrp = $user->personel->nrp ?? null;

        return $user->hasRole('personel')
            && $loggedInNrp !== null
            && $pengajuan->personel_nrp === $loggedInNrp
            && in_array($pengajuan->status, ['Menunggu Verifikasi', 'Ditolak']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pengajuan $pengajuan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pengajuan $pengajuan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pengajuan $pengajuan): bool
    {
        return true;
    }
}
