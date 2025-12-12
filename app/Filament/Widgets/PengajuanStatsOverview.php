<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;
use Carbon\Carbon;

class PengajuanStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // Personel tidak melihat dashboard
        if ($user->hasRole('personel')) {
            return [];
        }

        $currentYear = Carbon::now()->year;

        // -------------------------------
        // BASE QUERY
        // -------------------------------
        $query = Pengajuan::query()->where('periode_tahun', $currentYear);

        // Jika RENMIN → filter satker
        if ($user->hasRole('renmin')) {
            $satker = $user->satker->kode_satker ?? null;

            if (! $satker) {
                return [
                    Stat::make('Menunggu Verifikasi', 0)
                        ->icon('heroicon-s-clock')
                        ->color('warning'),
                ];
            }

            $query->whereHas('personel', function ($q) use ($satker) {
                $q->where('kode_satker', $satker);
            });
        }

        // -------------------------------
        // Hitung semua status (lebih bersih)
        // -------------------------------
        $total     = $query->clone()->count();
        $menunggu  = $query->clone()->where('status', 'Menunggu Verifikasi')->count();
        $terverif  = $query->clone()->where('status', 'Terverifikasi')->count();
        $proses    = $query->clone()->where('status', 'Proses Pengajuan')->count();
        $selesai   = $query->clone()->where('status', 'Selesai')->count();
        $ditolak   = $query->clone()->where('status', 'Ditolak')->count();

        // -------------------------------
        // Tampilan lebih cantik + warna konsisten
        // -------------------------------
        return [


            Stat::make('Menunggu Verifikasi', $menunggu)
                ->icon('heroicon-s-clock', IconPosition::Before)
                ->color('warning')
                ->description('Menunggu validasi Renmin')
                ->extraAttributes(['class' => 'shadow-md border']),

            Stat::make('Terverifikasi', $terverif)
                ->icon('heroicon-s-check-circle', IconPosition::Before)
                ->color('success')
                ->description('Sudah diverifikasi oleh Renmin')
                ->extraAttributes(['class' => 'shadow-md border']),

            Stat::make('Proses Pengajuan', $proses)
                ->icon('heroicon-s-cog', IconPosition::Before)
                ->color('info')
                ->description('Sedang diajukan ke Mabes Polri')
                ->extraAttributes(['class' => 'shadow-md border']),

            Stat::make('Selesai', $selesai)
                ->icon('heroicon-s-flag', IconPosition::Before)
                ->color('success')
                ->description('Pengajuan selesai')
                ->extraAttributes(['class' => 'shadow-md border']),

            Stat::make('Ditolak', $ditolak)
                ->icon('heroicon-s-x-circle', IconPosition::Before)
                ->color('danger')
                ->description('Pengajuan ditolak')
                ->extraAttributes(['class' => 'shadow-md border']),

            Stat::make('Total Pengajuan', $total)
                ->description('Jumlah semua pengajuan tahun ini')
                ->icon('heroicon-s-clipboard-document-check')
                ->color('primary')
                ->extraAttributes(['class' => 'shadow-md border']),
        ];
    }
}
