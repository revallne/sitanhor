<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Actions;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use League\CommonMark\Xml\FallbackNodeXmlRenderer;
use Symfony\Component\Routing\Matcher\Dumper\StaticPrefixCollection;
use Illuminate\Support\Collection;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $pluralModelLabel = 'Pengajuan';

    protected static ?string $modelLabel = 'Pengajuan';

    public static function getNavigationSort(): ?int
    {
        return 3; 
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('periode_tahun')
                    ->label('Periode Tahun')
                    ->options(
                        \App\Models\Periode::where('status', 'buka')
                            ->pluck('tahun', 'tahun')
                    )
                    ->default(
                        \App\Models\Periode::where('status', 'buka')
                            ->latest('tahun')
                            ->value('tahun')
                    )
                    ->required(),
                Forms\Components\Select::make('kategori_kode_kategori')
                    ->label('Tanda Kehormatan')
                    ->relationship('kategori', 'nama_kategori') // tampilkan nama_kategori di dropdown
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama_kategori} ({$record->syarat_masa_dinas} Tahun)")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rule(function ($get) {
                        return function (string $attribute, $value, $fail) use ($get) {
                            $exists = Pengajuan::where('personel_nrp', auth()->user()->personel_nrp)
                                ->where('periode_tahun', $get('periode_tahun'))
                                ->where('kategori_kode_kategori', $value)
                                ->exists();

                            if ($exists) {
                                $fail('Anda sudah mengajukan kategori ini pada periode tersebut.');
                            }
                        };
                    }),
                Forms\Components\TextInput::make('surat_tanda_kehormatan')
                    ->label('Nomor dan Tanggal Keppres Nomor Tanda Kehormatan Terakhir')
                    ->helperText('Contoh: Keppres Nomor 39/TK/TAHUN 2021 tanggal 1 Januari 2021')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Hidden::make('tanggal_pengajuan')
                    ->default(now()->toDateString()), // otomatis isi dengan tanggal hari ini (tidak muncul di form)
                FileUpload::make('sk_tmt')
                    ->label('SK TMT Pertama (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('sk-tmt')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => (string) str('tmt-'
                            . (
                                Auth::user()->nrp 
                                ?? Auth::user()->personel->nrp 
                                ?? 'unknown'                  // fallback jika belum ada nrp
                            ) . '-'
                            . now()->format('YmdHis') . '.'             // tanggal dan waktu upload
                            . $file->getClientOriginalExtension()       // ambil ekstensi file asli
                        ),
                    ),
                FileUpload::make('sk_pangkat')
                    ->label('SK Pangkat Terakhir(PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('sk-pangkat')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => (string) str('pangkat-'
                            . (
                                Auth::user()->nrp 
                                ?? Auth::user()->personel->nrp 
                                ?? 'unknown'                  
                            ) . '-'
                            . now()->format('YmdHis') . '.'             // tanggal dan waktu upload
                            . $file->getClientOriginalExtension()       // ambil ekstensi file asli
                        ),
                    ),
                FileUpload::make('sk_jabatan')
                    ->label('SK Jabatan Terakhir (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('sk-jabatan')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => (string) str('jabatan-'
                            . (
                                Auth::user()->nrp 
                                ?? Auth::user()->personel->nrp 
                                ?? 'unknown'                  // fallback jika belum ada nrp
                            ) . '-'
                            . now()->format('YmdHis') . '.'             // tanggal dan waktu upload
                            . $file->getClientOriginalExtension()       // ambil ekstensi file asli
                        ),
                    ),
                FileUpload::make('drh')
                    ->label('Daftar Riwayat Hidup (PDF)')
                    ->helperText(
                        new HtmlString(
                            'Gunakan template: <a href="' . asset('storage/dokumen/DRH.docx') . '" target="_blank" class="text-blue-600 underline">Template Daftar Riwayat Hidup</a>'
                        )
                    )
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('drh')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => (string) str('drh-'
                            . (
                                Auth::user()->nrp 
                                ?? Auth::user()->personel->nrp 
                                ?? 'unknown'                  // fallback jika belum ada nrp
                            ) . '-'
                            . now()->format('YmdHis') . '.'             // tanggal dan waktu upload
                            . $file->getClientOriginalExtension()       // ambil ekstensi file asli
                        ),
                    ),
                // Forms\Components\TextArea::make('catatan')
                //     ->label('Catatan Penolakan')
                //     ->visible(fn ($record) => $record->status === 'Ditolak')
                //     ->disabled(),
                    
                // Forms\Components\TextInput::make('status')
                //     ->required()
                //     ->maxLength(255)
                //     ->default('Menunggu Verifikasi'),
                // Forms\Components\Textarea::make('catatan')
                //     ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('personel_nrp')
                    ->label('NRP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('personel.user.name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('periode_tahun')
                    ->label('Periode')
                    ->alignment('center')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Tanda Kehormatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surat_tanda_kehormatan')
                    ->label('Nomor dan Tanggal Keppres')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->alignment('center')
                    ->date('d F Y')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('sk_tmt')
                //     ->label('SK TMT Pertama')
                //     ->visible(fn ($record) => auth()->user()->hasRole(['renmin', 'bagwatpers']))
                //     ->url(fn ($record) => asset('storage/' . $record->sk_tmt)) // arahkan ke lokasi file di public/storage
                //     ->openUrlInNewTab() // buka di tab baru
                //     ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                // Tables\Columns\TextColumn::make('sk_pangkat')
                //     ->label('SK Pangkat Terakhir')
                //     ->visible(fn ($record) => auth()->user()->hasRole(['renmin', 'bagwatpers']))
                //     ->url(fn ($record) => asset('storage/' . $record->sk_pangkat)) // arahkan ke lokasi file di public/storage
                //     ->openUrlInNewTab() // buka di tab baru
                //     ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                // Tables\Columns\TextColumn::make('sk_jabatan')
                //     ->label('SK Jabatan Terakhir')
                //     ->visible(fn ($record) => auth()->user()->hasRole(['renmin', 'bagwatpers']))
                //     ->url(fn ($record) => asset('storage/' . $record->sk_jabatan)) // arahkan ke lokasi file di public/storage
                //     ->openUrlInNewTab() // buka di tab baru
                //     ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                // Tables\Columns\TextColumn::make('drh')
                //     ->label('Daftar Riwayat Hidup')
                //     ->visible(fn ($record) => auth()->user()->hasRole(['renmin', 'bagwatpers']))
                //     ->url(fn ($record) => asset('storage/' . $record->drh)) // arahkan ke lokasi file di public/storage
                //     ->openUrlInNewTab() // buka di tab baru
                //     ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->badge() // tampil sebagai badge warna
                    ->color(fn (string $state): string => match ($state) {
                        'Menunggu Verifikasi' => 'warning',   // kuning
                        'Ditolak'             => 'danger',    // merah
                        'Terverifikasi'       => 'success',    // hijau
                        'Proses Pengajuan'    => 'info',   // biru
                        'Selesai'             => 'gray',   // abu
                        default               => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                //     ->visible(fn ($record) => in_array($record->status, ['Menunggu Verifikasi', 'Selesai', 'Ditolak'])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verifikasi')
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
                Tables\Actions\Action::make('ajukan')
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
                Tables\Actions\Action::make('disetujui')
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
                Tables\Actions\Action::make('tolak')
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
                    Tables\Actions\Action::make('buatSurat')
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
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(fn () => auth()->user()->hasRole('bagwatpers'))
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
                        ->visible(fn () => auth()->user()->hasRole(['renmin', 'bagwatpers']))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            if ($records->contains(fn ($record) => $record->status !== 'Menunggu Verifikasi')) {
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
                        ->visible(fn () => auth()->user()->hasRole('bagwatpers'))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            if ($records->contains(fn ($record) => $record->status !== 'Terverifikasi')) {
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
                        ->visible(fn () => auth()->user()->hasRole('bagwatpers'))
                        ->requiresConfirmation()
                        ->successNotificationTitle('Pengajuan berhasil dihapus!'),

                    // ❌ FORCE DELETE → hanya bagwatpers
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('bagwatpers'))
                        ->requiresConfirmation(),

                    // 🔁 RESTORE → hanya bagwatpers
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('bagwatpers')),
                ]),
            ]);

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'detail' => Pages\DetailPengajuan::route('/{record}/detail'),
            'view' => Pages\ViewPengajuan::route('/{record}'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Role RENMIN → hanya lihat pengajuan dari personel yang satkernya sama
        if ($user->hasRole('renmin')) {

            // Cari satker yang dipegang oleh renmin ini via user_email
            $satker = \App\Models\Satker::where('user_email', $user->email)->first();

            if ($satker) {
                $query->whereHas('personel', function ($q) use ($satker) {
                    $q->where('kode_satker', $satker->kode_satker);
                });
            }
        }

        // Role PERSONEL → hanya lihat pengajuan miliknya sendiri
        if ($user->hasRole('personel')) {
            $nrp = $user->personel->nrp ?? null;

            if ($nrp) {
                $query->where('personel_nrp', $nrp);
            }
        }

        return $query;
    }
}
