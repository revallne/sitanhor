<?php

// namespace App\Filament\Widgets;

// use App\Models\Pengajuan;
// use App\Models\Kategori;
// use Filament\Widgets\ChartWidget;

// class PengajuanChart extends ChartWidget
// {
//     protected static ?string $heading = 'Pengajuan per Kategori';

//     protected int | string | array $columnSpan = '2';

//     protected function getData(): array
//     {
//         $kategoris = Kategori::orderBy('kode_kategori')->get();
//         $labels = $kategoris->pluck('nama_kategori')->toArray();

//         $data = $kategoris->map(function ($kategori) {
//             return Pengajuan::where('kategori_kode_kategori', $kategori->kode_kategori)->count();
//         })->toArray();

//         return [
//             'datasets' => [
//                 [
//                     'label' => 'Jumlah Pengajuan',
//                     'data' => $data,
//                 ],
//             ],
//             'labels' => $labels,
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'bar';
//     }

//     // ⬇⬇ hanya admin yang bisa melihat widget ini
//     public static function canView(): bool
//     {
//         return auth()->user()->hasRole('bagwatpers');
//     }
// }
