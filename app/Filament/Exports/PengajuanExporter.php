<?php

namespace App\Filament\Exports;

use App\Models\Pengajuan;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PengajuanExporter extends Exporter
{
    protected static ?string $model = Pengajuan::class;

    public function getModelQuery(): Builder
    {
        return parent::getModelQuery()
            ->with([
                'personel', 
                'personel.user', 
                'personel.satker',
                'kategori'
            ]);
    }

    public static function getColumns(): array
    {
        return [
            // Kolom 1: Nama Personel
            ExportColumn::make('personel.user.name')
                ->label('Nama')
                ->getStateUsing(fn ($record) => $record->personel?->user?->name ?? 'N/A'),

            // Kolom 2: TMT Pertama
            ExportColumn::make('personel.tmt_pertama')
                ->label('TMT Pertama')
                ->getStateUsing(fn ($record) => $record->personel?->tmt_pertama ? 
                    Carbon::parse($record->personel->tmt_pertama)->format('d-m-Y') : 'N/A'),

            // Kolom 3: Pangkat
            ExportColumn::make('personel.pangkat')
                ->label('Pangkat')
                ->getStateUsing(fn ($record) => $record->personel?->pangkat ?? 'N/A'),

            // Kolom 4: NRP
            ExportColumn::make('personel.nrp')
                ->label('NRP')
                ->getStateUsing(fn ($record) => $record->personel?->nrp ?? 'N/A'),

            // Kolom 5: Jabatan Lengkap
            ExportColumn::make('personel.jabatan')
                ->label('Jabatan Lengkap')
                ->getStateUsing(fn ($record) => $record->personel?->jabatan ?? 'N/A'),
            
            // Kolom 6: Tempat, Tanggal Lahir (Combined)
            ExportColumn::make('tempat_tanggal_lahir')
                ->label('Tempat, Tanggal Lahir')
                ->getStateUsing(function ($record) {
                    $personel = $record->personel;
                    if (!$personel || !$personel->tempat_lahir || !$personel->tanggal_lahir) {
                        return 'N/A';
                    }
                    $tanggalLahirFormatted = Carbon::parse($personel->tanggal_lahir)->format('Y-m-d');
                    return "{$personel->tempat_lahir}, {$tanggalLahirFormatted}";
                }),

            // Kolom 7: Tanhor yang Diajukan
            ExportColumn::make('kategori.nama_kategori')
                ->label('Tanhor yang Diajukan')
                ->getStateUsing(fn ($record) => $record->kategori?->nama_kategori ?? 'N/A'),

            // Kolom 8: Nomor dan tanggal Keppres Tanda Kehormatan Pengabdian Sebelumnya
            ExportColumn::make('surat_tanda_kehormatan')
                ->label('Nomor dan tanggal Keppres Tanda Kehormatan Pengabdian Sebelumnya')
                ->getStateUsing(fn ($record) => $record->surat_tanda_kehormatan ?? '-'),
            
            // Kolom 9: Satker
            ExportColumn::make('personel.satker.nama_satker')
                ->label('Satker')
                ->getStateUsing(fn ($record) => $record->personel?->satker?->nama_satker ?? 'N/A'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your pengajuan export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
