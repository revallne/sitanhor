<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuan;
use App\Models\Kategori;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajuanChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Pengajuan Setiap Satker Tahun Ini';

    protected int | string | array $columnSpan = '2';

    protected static ?int $sort = 2; 

    protected function getData(): array
    {
        // Ambil tahun periode saat ini (misalnya: 2025)
        $currentYear = Carbon::now()->year;
        
        // --- Logika Pembatasan Akses untuk Renmin ---
        $user = Auth::user();

        $satkerKode = null;
        
        

        // QUERY: Hitung jumlah pengajuan per Satker
        $query = Pengajuan::query()
            ->select(
                DB::raw('satkers.nama_satker as satker_name'),
                DB::raw('COUNT(pengajuans.id) as count')
            )
            // JOIN: Pengajuan -> Personel -> Satker
            // Menghubungkan pengajuan ke personel, lalu personel ke satker
            ->join('personels', 'pengajuans.personel_nrp', '=', 'personels.nrp')
            ->join('satkers', 'personels.kode_satker', '=', 'satkers.kode_satker')
            // FILTER: Hanya pengajuan di tahun saat ini
            ->where('pengajuans.periode_tahun', $currentYear)
            ->groupBy('satkers.nama_satker');
            
        // Filter tambahan jika user adalah Renmin
        if ($satkerKode) {
            $query->where('satkers.kode_satker', $satkerKode);
        }

        $data = $query->get();
        
        $labels = $data->pluck('satker_name')->all();
        $counts = $data->pluck('count')->all();

        // Jika tidak ada data pengajuan di tahun ini
        if (empty($labels)) {
             return [
                'datasets' => [
                    ['label' => "Total Pengajuan ({$currentYear})", 'data' => [0]],
                ],
                'labels' => ['Belum Ada Pengajuan di Tahun Ini'],
            ];
        }


        return [
            'datasets' => [
                [
                    'label' => "Total Pengajuan ({$currentYear})",
                    'data' => $counts,
                    // ✨ MODIFIKASI INI ✨
                    // Ganti array warna dengan satu nilai Hex warna Primary Anda
                    'backgroundColor' => '#7E481C', 
                    
                    // Tambahkan border agar lebih jelas (opsional)
                    'borderColor' => '#7E481C', 
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    // ⬇⬇ hanya admin yang bisa melihat widget ini
    public static function canView(): bool
    {
        return auth()->user()->hasRole('bagwatpers');
    }
}
