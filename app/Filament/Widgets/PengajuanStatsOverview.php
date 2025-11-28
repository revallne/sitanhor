<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;
use App\Models\Kategori;
use Filament\Widgets\ChartWidget;

class PengajuanStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->hasRole('personel')) {
            return [];
        }
        
        // Jika user adalah RENMIN → filter berdasarkan satkernya
        if ($user->hasRole('renmin')) {
            $userSatker = $user->satker->kode_satker;

            $query = Pengajuan::whereHas('personel', function ($q) use ($userSatker) {
                $q->where('kode_satker', $userSatker);
            });
        } 
        // Jika user adalah BAGWAT PERS → akses semua data
        else {
            $query = Pengajuan::query();
        }

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
