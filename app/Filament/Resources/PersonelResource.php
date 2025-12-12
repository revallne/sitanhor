<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonelResource\Pages;
use App\Filament\Resources\PersonelResource\RelationManagers;
use App\Models\Personel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;

class PersonelResource extends Resource
{
    protected static ?string $model = Personel::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $pluralModelLabel = 'Data Personel';

    protected static ?string $modelLabel = 'Personel';

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        $user = auth()->user();

        if ($user->hasRole('personel')) {
            return 'Profil';
        }

        return 'Data Personel';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nrp')
                    ->label('NRP')
                    ->required()
                    ->maxLength(8)
                    ->numeric(),
                Forms\Components\TextInput::make('user_email')
                    ->label('Email Terdaftar')
                    ->email()
                    ->required()
                    ->maxLength(50)
                    ->rules([
                        'required',
                        'email',
                        // Rule: harus ada di tabel 'users' pada kolom 'email'
                        'exists:users,email',
                    ])
                    ->validationMessages([
                        // Pesan error kustom untuk rule 'exists'
                        'exists' => 'Email ini tidak terdaftar sebagai akun pengguna.',
                    ]),
                Forms\Components\TextInput::make('user.name')
                    ->label('Nama Lengkap (Beserta Gelar)')
                    ->maxLength(100)
                    ->dehydrateStateUsing(fn($state) => ucwords(($state))),
                Forms\Components\Select::make('kode_satker')
                    ->label('Satuan Kerja')
                    ->relationship('satker', 'nama_satker') // tampilkan nama_satker di dropdown
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nama_satker}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('tmt_pertama')
                    ->label('TMT Pertama')
                    ->required(),
                Forms\Components\TextInput::make('pangkat')
                    ->required()
                    ->dehydrateStateUsing(fn($state) => ucwords(($state)))
                    ->maxLength(30),
                Forms\Components\TextInput::make('jabatan')
                    ->required()
                    ->dehydrateStateUsing(fn($state) => ucwords(($state)))
                    ->maxLength(100),
                Forms\Components\TextInput::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->required()
                    ->dehydrateStateUsing(fn($state) => ucwords(strtolower($state)))
                    ->maxLength(30),
                Forms\Components\DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nrp')
                    ->label('NRP')
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal']),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal w-48']) // sekitar 200px
                    ->extraHeaderAttributes(['class' => 'w-48']),

                Tables\Columns\TextColumn::make('satker.nama_satker')
                    ->label('Satuan Kerja')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal w-32'])
                    ->extraHeaderAttributes(['class' => 'w-32']),

                Tables\Columns\TextColumn::make('tmt_pertama')
                    ->label('TMT Pertama')
                    ->alignment('center')
                    ->formatStateUsing(
                        fn($state) =>
                        $state ? Carbon::parse($state)->locale('id')->translatedFormat('d M Y') : null
                    ),

                Tables\Columns\TextColumn::make('pangkat')
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal w-40'])
                    ->extraHeaderAttributes(['class' => 'w-40']),

                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'whitespace-normal w-52'])
                    ->extraHeaderAttributes(['class' => 'w-52']),

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
            ->emptyStateHeading('Tidak Ada Data Personel')
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Data yang Dihapus')->visible(fn() => Auth::user()
                        ->hasRole('bagwatpers')),
                SelectFilter::make('kode_satker')
                    ->label('Filter Berdasarkan Satker')
                    // Menggunakan relasi 'satker' di model Personel
                    ->relationship('satker', 'nama_satker')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nama_satker}")
                    ->searchable()
                    ->preload()
                    // Visibilitas: Hanya untuk Bagwatpers dan Renmin
                    ->visible(fn() => Auth::user()->hasRole('bagwatpers')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])->visible(fn() => Auth::user()->hasRole('bagwatpers')),
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
            'index' => Pages\ListPersonels::route('/'),
            'create' => Pages\CreatePersonel::route('/create'),
            'view' => Pages\ViewPersonel::route('/{record}'),
            'edit' => Pages\EditPersonel::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Base Query
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        // 1. ADMIN → akses semua
        if ($user->hasRole('bagwatpers')) {
            return $query;
        }

        // 2. RENMIN → hanya personel dalam satker yang ditangani
        if ($user->hasRole('renmin')) {

            $satker = \App\Models\Satker::where('user_email', $user->email)->first();

            if ($satker) {
                return $query->where('kode_satker', $satker->kode_satker);
            }

            return $query->whereRaw('1 = 0'); // jika satker tidak ditemukan
        }

        // 3. PERSONEL → hanya datanya sendiri
        if ($user->hasRole('personel')) {

            $nrp = $user->personel->nrp ?? null;

            if (! $nrp) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('nrp', $nrp);
        }

        // Default → tidak ada akses
        return $query->whereRaw('1 = 0');
    }
}
