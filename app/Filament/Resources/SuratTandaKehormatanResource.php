<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratTandaKehormatanResource\Pages;
use App\Filament\Resources\SuratTandaKehormatanResource\RelationManagers;
use App\Models\SuratTandaKehormatan;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;


class SuratTandaKehormatanResource extends Resource
{
    protected static ?string $model = SuratTandaKehormatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $pluralModelLabel = 'Penerima Tanda Kehormatan';

    protected ?string $heading = 'Penerima Tanda Kehormatan';

    protected static ?string $modelLabel = 'Surat Tanhor';


    public static function getNavigationSort(): ?int
    {
        return 4;
    }
    public static function getNavigationLabel(): string
    {
        $user = auth()->user();

        if ($user->hasRole('personel')) {
            return 'Tanda Kehormatan';
        }

        return 'Penerima Tanda Kehormatan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nrp')
                    ->label('NRP Penerima')
                    ->default(request('nrp'))
                    ->required()
                    ->maxLength(20)
                    ->disabled(fn($context) => $context === 'edit')      // disable saat edit
                    ->dehydrated(fn($context) => $context !== 'edit'),

                Forms\Components\Select::make('periode_tahun')
                    ->label('Periode')
                    ->default(request('periode'))
                    ->relationship('pengajuan.periode', 'tahun')
                    ->required()
                    ->disabled(fn($context) => $context === 'edit')      // disable saat edit
                    ->dehydrated(fn($context) => $context !== 'edit'),


                Forms\Components\Select::make('kategori_kode_kategori')
                    ->label('Tanda Kehormatan')
                    ->default(request('kategori'))
                    ->relationship('pengajuan.kategori', 'nama_kategori')
                    ->required()
                    ->disabled(fn($context) => $context === 'edit')      // disable saat edit
                    ->dehydrated(fn($context) => $context !== 'edit'),


                Forms\Components\Hidden::make('pengajuan_id')
                    ->default(request('pengajuan_id')),

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
                Tables\Columns\TextColumn::make('pengajuan.personel_nrp')
                    ->label('NRP')
                    ->searchable()
                    ->visible(fn() => !auth()->user()->hasRole('personel'))
                    ->grow(false)
                    ->width('90px')
                    ->lineClamp(2),

                Tables\Columns\TextColumn::make('pengajuan.personel.user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->visible(fn() => !auth()->user()->hasRole('personel'))
                    ->grow()
                    ->width('250px') // kolom fleksibel, biar tidak mepet
                    ->lineClamp(2),

                Tables\Columns\TextColumn::make('pengajuan.periode_tahun')
                    ->label('Periode')
                    ->alignCenter()
                    ->visible(fn() => !auth()->user()->hasRole('personel'))
                    ->grow(false)
                    ->width('80px'),

                Tables\Columns\TextColumn::make('pengajuan.kategori.nama_kategori')
                    ->label('Kategori')
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2)
                    ->grow(false) // kolom panjang otomatis fleksibel
                    ->width('250px'),

                Tables\Columns\TextColumn::make('noKepres')
                    ->label('Nomor Keppres')
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2)
                    ->grow()
                    ->width('250px'),
                Tables\Columns\TextColumn::make('tanggalKepres')
                    ->label('Tanggal Keppres')
                    ->visible(fn() => auth()->user()->hasRole('personel'))
                    ->formatStateUsing(function ($state) {
                        if (! $state) return null;
                        return Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('d F Y');
                    })
                    ->wrap()
                    ->lineClamp(2)
                    ->grow()
                    ->width('250px'),

                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                // Tables\Columns\TextColumn::make('deleted_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Tidak Ada Tanda Kehormatan yang Diterima')
            ->filters([
                Tables\Filters\TrashedFilter::make()->label('Data yang Dihapus')->visible(fn() => auth()->user()->hasRole(['bagwatpers', 'renmin'])),
                SelectFilter::make('periode_tahun')
                    ->label('Filter Berdasarkan Periode')
                    // Mengikuti relasi dari SuratTandaKehormatan -> Pengajuan -> Periode
                    ->relationship('pengajuan.periode', 'tahun')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->hasRole(['bagwatpers', 'renmin'])),
            ])
            ->actions([

                Tables\Actions\Action::make('lihat')
                    ->label('Lihat Surat')
                    ->color('info')
                    ->button()
                    ->visible(fn() => auth()->user()->hasRole(['bagwatpers', 'renmin']))
                    ->url(fn($record) => asset('storage/' . $record->file_surat)) // arahkan ke lokasi file di public/storage
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('download_personel')
                    ->label('Unduh Surat')
                    ->color('success') // Warna yang berbeda agar mudah dibedakan
                    ->icon('heroicon-o-arrow-down-tray') // Ikon download
                    ->button()
                    ->url(fn($record) => asset('storage/' . $record->file_surat))

                    // PENTING: Menambahkan atribut 'download' agar browser langsung mendownload
                    ->openUrlInNewTab()
                    ->extraAttributes([
                        'download' => true,
                    ])

                    // KONTROL VISIBILITY: Hanya terlihat untuk role 'personel'
                    ->visible(function () {
                        $user = auth()->user();
                        // Pastikan user login dan memiliki role 'personel'
                        return $user && $user->hasRole('personel');
                    }),
                // Tables\Actions\ViewAction::make()
                //     ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-s-pencil-square')
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-s-trash')
                    ->iconButton()
                    ->visible(fn() => auth()->user()->hasRole(['bagwatpers', 'renmin']))
                    ->requiresConfirmation()
                    ->successNotification(
                        Notification::make()
                            ->title('Surat Tanda Kehormatan berhasil dihapus.')
                            ->success()
                    )
                    ->failureNotification(
                        Notification::make()
                            ->title('Surat Tanda Kehormatan gagal dihapus.')
                            ->danger()
                    ),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])
                    ->visible(fn() => auth()->user()->hasRole(['bagwatpers', 'renmin'])),
            ])
            ->paginationPageOptions([50, 100, 200, 'all']) // Mendefinisikan semua opsi yang tersedia
            ->defaultPaginationPageOption(50);
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
        $user = auth()->user();

        // Base query tanpa soft delete scope
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        // 1. bagwatpers (admin) → lihat semua
        if ($user->hasRole('bagwatpers')) {
            return $query;
        }

        // 2. renmin → lihat hanya data dari satkernya
        if ($user->hasRole('renmin')) {

            // Ambil satker renmin dari tabel satker
            $satker = \App\Models\Satker::where('user_email', $user->email)->first();

            if (!$satker) {
                return $query->whereRaw('1 = 0'); // Tidak punya satker → tidak boleh lihat data
            }

            // Filter berdasarkan satker personel
            return $query->whereHas('pengajuan.personel', function ($q) use ($satker) {
                $q->where('kode_satker', $satker->kode_satker);
            });
        }

        // 3. personel → hanya data miliknya sendiri
        if ($user->hasRole('personel')) {

            $nrp = $user->personel->nrp ?? null;

            if (!$nrp) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('pengajuan', function ($q) use ($nrp) {
                $q->where('personel_nrp', $nrp);
            });
        }

        // Default → tidak boleh melihat apapun
        return $query->whereRaw('1 = 0');
    }
}
