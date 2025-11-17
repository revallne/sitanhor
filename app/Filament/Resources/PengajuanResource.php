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

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $pluralModelLabel = 'Pengajuan';

    protected static ?string $modelLabel = 'Pengajuan';

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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('personel.user.name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('periode_tahun')
                    ->label('Periode')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Tanda Kehormatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('surat_tanda_kehormatan')
                    ->label('Nomor dan Tanggal Keppres')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sk_tmt')
                    ->label('SK TMT Pertama')
                    ->url(fn ($record) => asset('storage/' . $record->sk_tmt)) // arahkan ke lokasi file di public/storage
                    ->openUrlInNewTab() // buka di tab baru
                    ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                Tables\Columns\TextColumn::make('sk_pangkat')
                    ->label('SK Pangkat Terakhir')
                    ->url(fn ($record) => asset('storage/' . $record->sk_pangkat)) // arahkan ke lokasi file di public/storage
                    ->openUrlInNewTab() // buka di tab baru
                    ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                Tables\Columns\TextColumn::make('sk_jabatan')
                    ->label('SK Jabatan Terakhir')
                    ->url(fn ($record) => asset('storage/' . $record->sk_jabatan)) // arahkan ke lokasi file di public/storage
                    ->openUrlInNewTab() // buka di tab baru
                    ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                Tables\Columns\TextColumn::make('drh')
                    ->label('Daftar Riwayat Hidup')
                    ->url(fn ($record) => asset('storage/' . $record->drh)) // arahkan ke lokasi file di public/storage
                    ->openUrlInNewTab() // buka di tab baru
                    ->formatStateUsing(fn ($state) => '📄 Lihat Surat'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('suratTandaKehormatan')
                //     ->searchable(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
