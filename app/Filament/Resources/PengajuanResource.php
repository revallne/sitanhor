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

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('personel_nrp')
                //     ->label('NRP')
                //     ->required()
                //     ->maxLength(15),
                Forms\Components\TextInput::make('periode_tahun')
                    ->label('Tahun Periode')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(date('Y') + 1)
                    ->required()
                    ->rule('digits:4')
                    ->required(),
                Forms\Components\Select::make('kategori_kode_kategori')
                    ->label('Tanda Kehormatan')
                    ->relationship('kategori', 'nama_kategori') // tampilkan nama_kategori di dropdown
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama_kategori} ({$record->syarat_masa_dinas} Tahun)")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('surat_tanda_kehormatan')
                    ->label('Tanda Kehormatan yang Sudah Dimiliki')
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('periode_tahun'),
                Tables\Columns\TextColumn::make('kategori_kode_kategori')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('suratTandaKehormatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggalPengajuan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sk_tmt')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sk_pangkat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sk_jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('drh')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('namaFile_SK_TMT')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('pathFile_SK_TMT')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('namaFile_SK_pangkat')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('pathFile_SK_pangkat')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('namaFile_SK_jabatan')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('pathFile_SK_jabatan')
                    // ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
