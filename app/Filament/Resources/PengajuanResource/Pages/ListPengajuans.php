<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
// use Filament\Actions\Exports\ExportAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\PengajuanExporter;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

use Illuminate\Support\HtmlString;

use Illuminate\Validation\Rule;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use League\CommonMark\Xml\FallbackNodeXmlRenderer;
use Symfony\Component\Routing\Matcher\Dumper\StaticPrefixCollection;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\SelectFilter;

use App\Models\Personel;
use App\Models\Kategori;



class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected ?string $heading = 'Pengajuan Tanda Kehormatan';

    protected ?string $subheading = 'Daftar Pengajuan yang Telah Dibuat';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat Pengajuan Baru'),
            ExportAction::make()
                ->exporter(PengajuanExporter::class)
                ->color('primary')
                ->label('Unduh Data Pengajuan')
                //->visible(fn () => auth()->user()->hasRole('bagwatpers'))
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'Proses Pengajuan')
                )
                // ✨ KONDISI VISIBLE BERDASARKAN TAB AKTIF ✨
                ->visible(function ($livewire) {
                    $user = auth()->user();

                    // 1. Cek Role (hanya bagwatpers yang boleh export)
                    $isAllowedRole = $user && $user->hasRole('bagwatpers');

                    // 2. Cek Tab Aktif
                    $activeTab = $livewire->activeTab; // PERBAIKAN: Mengakses properti $activeTab
                    $isProsesTab = ($activeTab === 'proses'); // Cek key tab 'proses'

                    // Action hanya terlihat jika Role diizinkan DAN Tab adalah 'Proses Pengajuan'
                    return $isAllowedRole && $isProsesTab;
                }),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        // Jika role personel → tidak pakai tabs sama sekali
        if ($user->hasRole('personel')) {
            return [];
        }

        return [
            // -----------------------
            // ▶ TABS STATUS
            // -----------------------
            'all' => Tab::make('Semua'),

            'menunggu' => Tab::make('Menunggu Verifikasi')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'Menunggu Verifikasi')
                ),

            'terverifikasi' => Tab::make('Terverifikasi')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'Terverifikasi')
                ),

            'proses' => Tab::make('Proses Pengajuan')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'Proses Pengajuan')
                ),

            'ditolak' => Tab::make('Ditolak')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'Ditolak')
                ),

            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'Selesai')
                ),
        ];
    }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('personel_nrp')
                    ->label('NRP')
                    ->searchable()
                    ->visible(fn() => auth()->user()->hasRole(['bagwatpers', 'renmin']))
                    ->wrap(),

                Tables\Columns\TextColumn::make('personel.user.name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable()
                    ->visible(fn() => auth()->user()->hasRole(['bagwatpers', 'renmin']))
                    ->wrap(),

                Tables\Columns\TextColumn::make('periode_tahun')
                    ->label('Periode')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Tanda Kehormatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(), // penting

                // Tables\Columns\TextColumn::make('surat_tanda_kehormatan')
                //     ->label('Nomor dan Tanggal Keppres')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->wrap(),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label("Tanggal\nPengajuan") // 2 baris otomatis
                    ->alignCenter()
                    ->wrap()
                    ->formatStateUsing(function ($state) {
                        if (! $state) return null;
                        return Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('d M Y');
                    })
                    ->sortable()
                    ->visible(fn($record, $livewire) => ! in_array(
                        $livewire->activeTab,
                        ['menunggu', 'terverifikasi', 'proses']
                    )),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Menunggu Verifikasi' => 'warning',
                        'Ditolak'             => 'danger',
                        'Terverifikasi'       => 'success',
                        'Proses Pengajuan'    => 'info',
                        'Selesai'             => 'gray',
                        default               => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()->label('Data yang Dihapus')->visible(fn() => auth()->user()->hasRole(['bagwatpers'])),

                SelectFilter::make('periode_tahun')
                    ->label('Filter Periode')
                    ->relationship('periode', 'tahun') // Asumsi ada relasi 'periode' di model Pengajuan
                    ->searchable()
                    ->preload()
                    ->visible(function (): bool {
                        $user = Auth::user();
                        // Filter terlihat jika user adalah bagwatpers ATAU renmin
                        return $user && ($user->hasRole('bagwatpers') || $user->hasRole('renmin'));
                    }),

                // ✨ FILTER BERDASARKAN KATEGORI (Tabel/Model KategoriTandaKehormatan) ✨
                SelectFilter::make('kategori.nama_kategori')
                    ->label('Filter Kategori')
                    // Asumsi relasi di model Pengajuan adalah 'kategori' 
                    // dan field nama di model KategoriTandaKehormatan adalah 'nama_kategori'
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->visible(function (): bool {
                        $user = Auth::user();
                        // Filter terlihat jika user adalah bagwatpers ATAU renmin
                        return $user && ($user->hasRole('bagwatpers') || $user->hasRole('renmin'));
                    }),
                SelectFilter::make('personel.satker_id')
                    ->label('Filter Satker')
                    ->relationship('personel.satker', 'nama_satker')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->hasRole('bagwatpers')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->visible(fn() => auth()->user()->hasRole('personel')),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->visible(
                        fn($record) =>
                        $this->activeTab !== 'all' &&
                            auth()->user()->hasRole(['renmin', 'bagwatpers']) &&
                            $record->status === 'Menunggu Verifikasi'
                    )
                    ->requiresConfirmation()
                    ->action(function (Pengajuan $record) {
                        $record->update(['status' => 'Terverifikasi']);
                        Notification::make()
                            ->title('Pengajuan berhasil diverifikasi.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('ajukan')
                    ->label('Ajukan')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->visible(
                        fn($record) =>
                        $this->activeTab !== 'all' &&
                            auth()->user()->hasRole('bagwatpers') &&
                            $record->status === 'Terverifikasi'
                    )
                    ->requiresConfirmation()
                    ->action(function (Pengajuan $record) {
                        $record->update(['status' => 'Proses Pengajuan']);
                        Notification::make()
                            ->title('Pengajuan berhasil diproses.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('disetujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->visible(
                        fn($record) =>
                        $this->activeTab !== 'all' &&
                            auth()->user()->hasRole(['bagwatpers', 'renmin']) &&
                            $record->status === 'Proses Pengajuan'
                    )
                    ->requiresConfirmation()
                    ->action(function (Pengajuan $record) {
                        $record->update(['status' => 'Selesai']);
                        Notification::make()
                            ->title('Pengajuan berhasil diselesaikan.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->visible(
                        fn($record) =>
                        $this->activeTab !== 'all' &&
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
                Tables\Actions\Action::make('buatSurat')
                    ->label('Buat Surat')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->button()
                    ->visible(
                        fn($record) =>
                        $this->activeTab !== 'all' &&
                            auth()->user()->hasRole('renmin') &&
                            $record->status === 'Selesai'
                    )
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
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(function ($record) {

                        $user = auth()->user();

                        // Jika role bagwatpers → selalu boleh
                        if ($user->hasRole('bagwatpers')) {
                            return true;
                        }

                        // Jika personel → hanya jika status tertentu
                        if ($user->hasRole('personel')) {
                            return in_array($record->status, [
                                'Menunggu Verifikasi',
                                'Ditolak',
                            ]);
                        }

                        return false;
                    })

                    ->requiresConfirmation()
                    ->successNotification(
                        Notification::make()
                            ->title('Pengajuan berhasil dihapus.')
                            ->success()
                    )
                    ->failureNotification(
                        Notification::make()
                            ->title('Pengajuan gagal dihapus.')
                            ->danger()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    // 🔍 BULK VERIFIKASI → untuk renmin & bagwatpers
                    Tables\Actions\BulkAction::make('bulkVerifikasi')
                        ->label('Verifikasi Terpilih')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn() => auth()->user()->hasRole(['bagwatpers']))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            if ($records->contains(fn($record) => $record->status !== 'Menunggu Verifikasi')) {
                                Notification::make()
                                    ->title('Hanya data dengan status "Menunggu Verifikasi" yang dapat diverifikasi.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            $records->each->update(['status' => 'Terverifikasi']);
                            Notification::make()
                                ->title('Pengajuan berhasil diverifikasi.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulkAjukan')
                        ->label('Ajukan Terpilih')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn() => auth()->user()->hasRole('bagwatpers'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            if ($records->contains(fn($record) => $record->status !== 'Terverifikasi')) {
                                Notification::make()
                                    ->title('Hanya data dengan status Terverifikasi yang dapat diajukan.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            $records->each->update(['status' => 'Proses Pengajuan']);
                            Notification::make()
                                ->title('Pengajuan berhasil diajukan.')
                                ->success()
                                ->send();
                        }),

                    // 🗑️ BULK DELETE → hanya untuk bagwatpers
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->hasRole('bagwatpers'))
                        ->requiresConfirmation()
                        ->successNotificationTitle('Pengajuan berhasil dihapus!'),

                    // ❌ FORCE DELETE → hanya bagwatpers
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->hasRole('bagwatpers'))
                        ->requiresConfirmation(),

                    // 🔁 RESTORE → hanya bagwatpers
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn() => auth()->user()->hasRole('bagwatpers')),
                ]),
            ])
            ->paginationPageOptions([50, 100, 200, 'all']) // Mendefinisikan semua opsi yang tersedia
            ->defaultPaginationPageOption(50);
    }
}
