<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;
use App\Models\Kategori;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PengajuanStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->hasRole('personel')) {
            return [];
        }

        // 1. Tentukan tahun saat ini
        $currentYear = Carbon::now()->year;

        // 2. Inisialisasi Query Dasar (Akses Semua)
        $query = Pengajuan::query();
        
        // Jika user adalah RENMIN → filter berdasarkan satkernya
        if ($user->hasRole('renmin')) {
            // Ambil kode satker renmin (asumsi sudah ada relasi user->satker)
            $userSatker = $user->satker->kode_satker ?? null;

            if ($userSatker) {
                 $query->whereHas('personel', function ($q) use ($userSatker) {
                    $q->where('kode_satker', $userSatker);
                });
            } else {
                // Jika renmin tidak punya satker, kosongkan query
                return [
                    Stat::make('Menunggu Verifikasi', 0)->icon('heroicon-s-clock')->color('warning'),
                ];
            }
        } 
        // Jika user adalah BAGWAT PERS → akses semua data
        else {
            $query = Pengajuan::query();
        }

        $query->where('periode_tahun', $currentYear);

        return [
            Stat::make(
                'Menunggu Verifikasi', 
                $query->clone()->where('status', 'Menunggu Verifikasi')->count()
            )
                ->icon('heroicon-s-clock', IconPosition::Before)
                ->color('warning'),

            Stat::make(
                'Terverifikasi', 
                $query->clone()->where('status', 'Terverifikasi')->count()
            )
                ->icon('heroicon-s-check-circle', IconPosition::Before)
                ->color('primary'),

            Stat::make(
                'Proses Pengajuan', 
                $query->clone()->where('status', 'Proses Pengajuan')->count()
            )
                ->icon('heroicon-s-cog', IconPosition::Before)
                ->color('info'),

            Stat::make(
                'Selesai', 
                $query->clone()->where('status', 'Selesai')->count()
            )
                ->icon('heroicon-s-flag', IconPosition::Before)
                ->color('success'),

            Stat::make(
                'Ditolak', 
                $query->clone()->where('status', 'Ditolak')->count()
            )
                ->icon('heroicon-s-x-circle', IconPosition::Before)
                ->color('danger'),
        ];
    }

}
