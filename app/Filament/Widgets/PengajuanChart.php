<?php

namespace App\Filament\Widgets;

use App\Models\Pengajuan;
use App\Models\Kategori;
use Filament\Widgets\ChartWidget;

class PengajuanChart extends ChartWidget
{
    protected static ?string $heading = 'Pengajuan per Kategori';

    protected function getData(): array
    {
        $kategoris = Kategori::orderBy('kode_kategori')->get(); // sesuaikan nama kolom
        $labels = $kategoris->pluck('nama_kategori')->toArray();

        $data = $kategoris->map(function ($kategori) {
            // sesuaikan nama kolom di pengajuans yang menunjuk kategori
            return Pengajuan::where('kategori_kode_kategori', $kategori->kode_kategori)->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pengajuan',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
