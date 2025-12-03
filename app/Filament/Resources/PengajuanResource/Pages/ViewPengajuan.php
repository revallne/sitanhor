<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Pengajuan;

class ViewPengajuan extends ViewRecord
{
    protected static string $resource = PengajuanResource::class;

    protected ?string $heading = 'Detail Pengajuan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Edit Data Pengajuan'),
            Actions\Action::make('verifikasi')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->button()
                ->visible(fn ($record) => auth()->user()->hasRole(['renmin', 'bagwatpers']) && $record->status === 'Menunggu Verifikasi')
                ->requiresConfirmation()
                ->action(function (Pengajuan $record) {
                    $record->update(['status' => 'Terverifikasi']);
                    Notification::make()
                            ->title('Pengajuan berhasil diverifikasi.')
                            ->success()
                            ->send();
                }),
            Actions\Action::make('ajukan')
                ->label('Ajukan')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->button()
                ->visible(fn ($record) => auth()->user()->hasRole('bagwatpers') && $record->status === 'Terverifikasi')
                ->requiresConfirmation()
                ->action(function (Pengajuan $record) {
                    $record->update(['status' => 'Proses Pengajuan']);
                    Notification::make()
                            ->title('Pengajuan berhasil diproses.')
                            ->success()
                            ->send();
                }),
            Actions\Action::make('disetujui')
                ->label('Setujui')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->button()
                ->visible(fn ($record) => auth()->user()->hasRole('bagwatpers') && $record->status === 'Proses Pengajuan')
                ->requiresConfirmation()
                ->action(function (Pengajuan $record) {
                    $record->update(['status' => 'Selesai']);
                    Notification::make()
                            ->title('Pengajuan berhasil diselesaikan.')
                            ->success()
                            ->send();
                }),
            Actions\Action::make('tolak')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->button()
                ->visible(fn ($record) =>
                    auth()->user()->hasRole(['renmin', 'bagwatpers']) &&
                    in_array($record->status, ['Menunggu Verifikasi', 'Proses Pengajuan'])
                )
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('catatan')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(3)
                        ->placeholder('Masukkan alasan penolakan pengajuan ini...'),
                ])
                ->action(function (Pengajuan $record, array $data) {
                    $record->update(['status' => 'Ditolak', 'catatan' => $data['catatan']]);
                    Notification::make()
                            ->title('Pengajuan berhasil ditolak.')
                            ->success()
                            ->send();
                }),
            Actions\Action::make('buatSurat')
                ->label('Buat Surat')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->button()
                ->visible(fn ($record) => auth()->user()->hasRole('renmin') && $record->status === 'Selesai')
                ->action(function ($record, $livewire) {
                    return redirect()->route(
                        'filament.sitanhor.resources.surat-tanda-kehormatans.create',
                        [
                            'pengajuan_id' => $record->id,
                            'nrp'          => $record->personel->nrp,
                            'periode'      => $record->periode->tahun,
                            'kategori'     => $record->kategori->kode_kategori,
                        ]
                    );
                }),

        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            Section::make('Data Pengajuan')
                //->description('Informasi pengajuan tanda kehormatan')
                ->schema([
                    TextEntry::make('personel.user.name')->label('Nama Lengkap'),
                    TextEntry::make('personel_nrp')->label('NRP'),
                    TextEntry::make('personel.tmt_pertama')->label('TMT Pertama')->date('d-m-Y'),
                    TextEntry::make('personel.pangkat')->label('Pangkat'),
                    TextEntry::make('personel.jabatan')->label('Jabatan'),
                    TextEntry::make('personel.satker.nama_satker')->label('Satuan Kerja'),
                    TextEntry::make('periode_tahun')->label('Periode Pengajuan'),
                    TextEntry::make('kategori.nama_kategori')->label('Tanhor'),
                    TextEntry::make('tanggal_pengajuan')->label('Tanggal Pengajuan'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'Menunggu Verifikasi' => 'warning',   // kuning
                            'Ditolak'             => 'danger',    // merah
                            'Terverifikasi'       => 'success',   // hijau
                            'Proses Pengajuan'    => 'info',      // biru
                            'Selesai'             => 'gray',      // abu
                            default               => 'gray',
                        }),
                ])
                ->columns(3),

            Section::make('Lampiran Dokumen')
                //->description('Dokumen wajib yang diunggah pada proses pengajuan')
                ->schema([
                    TextEntry::make('sk_tmt')
                        ->label('SK TMT Pertama')
                        ->formatStateUsing(fn () => '📄 Lihat File')
                        ->url(fn ($record) => asset('storage/' . $record->sk_tmt))
                        ->openUrlInNewTab(),

                    TextEntry::make('sk_pangkat')
                        ->label('SK Pangkat Terakhir')
                        ->formatStateUsing(fn () => '📄 Lihat File')
                        ->url(fn ($record) => asset('storage/' . $record->sk_pangkat))
                        ->openUrlInNewTab(),

                    TextEntry::make('sk_jabatan')
                        ->label('SK Jabatan Terakhir')
                        ->formatStateUsing(fn () => '📄 Lihat File')
                        ->url(fn ($record) => asset('storage/' . $record->sk_jabatan))
                        ->openUrlInNewTab(),

                    TextEntry::make('drh')
                        ->label('Daftar Riwayat Hidup')
                        ->formatStateUsing(fn () => '📄 Lihat File')
                        ->url(fn ($record) => asset('storage/' . $record->drh))
                        ->openUrlInNewTab(),
                ])
                ->columns(4),

            Section::make('Catatan Penolakan')
                ->description('Alasan pengajuan ini ditolak')
                ->schema([
                    TextEntry::make('catatan')
                        ->visible(fn ($record) => $record->status === 'Ditolak')
                        ->placeholder('Tidak ada catatan penolakan'),
                ])
                ->visible(fn ($record) => $record->status === 'Ditolak')
                ->collapsed(false),
        ]);
    }

    
}
