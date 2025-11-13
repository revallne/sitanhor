<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratTandaKehormatanResource\Pages;
use App\Filament\Resources\SuratTandaKehormatanResource\RelationManagers;
use App\Models\SuratTandaKehormatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Auth;

class SuratTandaKehormatanResource extends Resource
{
    protected static ?string $model = SuratTandaKehormatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    
    protected static ?string $pluralModelLabel = 'Surat Tanda Kehormatan';

    protected static ?string $modelLabel = 'Surat Tanhor';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nrp')
                    ->label('NRP Penerima')
                    ->required()
                    ->maxLength(20),

                Forms\Components\Select::make('periode_tahun')
                    ->label('Periode')
                    ->relationship('pengajuan.periode', 'tahun')
                    ->required(),

                Forms\Components\Select::make('kategori_kode_kategori')
                    ->label('Tanda Kehormatan')
                    ->relationship('pengajuan.kategori', 'nama_kategori')
                    ->required(),

                Forms\Components\Hidden::make('pengajuan_id'),

                Forms\Components\TextInput::make('noKepres')
                    ->label('Nomor Keppres')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggalKepres')
                    ->label('Tanggal Keppres')
                    ->required(),
                Forms\Components\FileUpload::make('file_surat')
                    ->required()
                    ->label('Surat Tanda Kehormatan (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('surat-tanhor')
                    ->getUploadedFileNameForStorageUsing(
                        function (TemporaryUploadedFile $file, $livewire): string {
                            $nrp = $livewire->data['nrp'] ?? 'unknown'; // ambil nilai dari input form 'nrp'
                            
                            return 'tanhor-' . $nrp . '-' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
                        }
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Sembunyikan kolom pengajuan_id (jika sebelumnya ada)
                // Tables\Columns\TextColumn::make('pengajuan_id')->hidden(),

                // Ambil field dari relasi pengajuan
                Tables\Columns\TextColumn::make('pengajuan.personel_nrp')
                    ->label('NRP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pengajuan.personel.user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pengajuan.periode_tahun')
                    ->label('Periode')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pengajuan.kategori.nama_kategori')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('noKepres')
                    ->label('Nomor Keppres')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggalKepres')
                    ->label('Tanggal Keppres')
                    ->date(),
                Tables\Columns\TextColumn::make('file_surat')
                    ->label('File Surat')
                    ->url(fn ($record) => asset('storage/' . $record->file_surat)) // arahkan ke lokasi file di public/storage
                    ->openUrlInNewTab() // buka di tab baru
                    ->formatStateUsing(fn ($state) => '📄 Lihat Surat'), // ubah teks jadi link “Lihat Surat”
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
            'index' => Pages\ListSuratTandaKehormatans::route('/'),
            'create' => Pages\CreateSuratTandaKehormatan::route('/create'),
            'view' => Pages\ViewSuratTandaKehormatan::route('/{record}'),
            'edit' => Pages\EditSuratTandaKehormatan::route('/{record}/edit'),
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
