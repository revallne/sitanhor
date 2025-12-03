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
                    ->maxLength(255),
                Forms\Components\TextInput::make('user.name')
                    ->label('Nama Lengkap'),
                Forms\Components\Select::make('kode_satker')
                    ->label('Satuan Kerja')
                    ->relationship('satker', 'nama_satker') // tampilkan nama_satker di dropdown
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama_satker} - {$record->kode_satker}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('tmt_pertama')
                    ->label('TMT Pertama')
                    ->required(),
                Forms\Components\TextInput::make('pangkat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jabatan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->required()
                    ->maxLength(255),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('satker.nama_satker')
                    ->label('Satuan Kerja')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tmt_pertama')
                    ->label('TMT Pertama')
                    ->alignment('center')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('pangkat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('ttl')
                //     ->label('Tempat, Tanggal Lahir')
                //     ->getStateUsing(fn ($record) => 
                //         "{$record->tempat_lahir}, " . \Carbon\Carbon::parse($record->tanggal_lahir)->format('Y-m-d')
                //     ),
                // Tables\Columns\TextColumn::make('user_email')
                //     ->label('Email')
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
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
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
