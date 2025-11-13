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

    protected static ?string $pluralModelLabel = 'Personel';

    protected static ?string $modelLabel = 'Personel';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nrp')
                    ->label('NRP')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('user_email')
                    ->label('Email Terdaftar')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->default(fn () => Auth::user()->name) // otomatis isi dari user login
                    ->disabled() // tidak bisa diubah sama sekali
                    ->dehydrated(false),
                Forms\Components\Select::make('kode_satker')
                    ->label('Satuan Kerja')
                    ->relationship('satker', 'nama_satker') // tampilkan nama_satker di dropdown
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama_satker} - {$record->kode_satker}")
                    ->searchable()
                    ->preload()
                    ->required(),
                // Forms\Components\TextInput::make('kode_satker')
                //     ->required()
                //     ->numeric(),
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
                Tables\Columns\TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('satker.nama_satker')
                    ->label('Satuan Kerja')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tmt_pertama')
                    ->label('TMT Pertama')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pangkat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ttl')
                    ->label('Tempat, Tanggal Lahir')
                    ->getStateUsing(fn ($record) => 
                        "{$record->tempat_lahir}, " . \Carbon\Carbon::parse($record->tanggal_lahir)->format('Y-m-d')
                    ),
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
            'index' => Pages\ListPersonels::route('/'),
            'create' => Pages\CreatePersonel::route('/create'),
            'view' => Pages\ViewPersonel::route('/{record}'),
            'edit' => Pages\EditPersonel::route('/{record}/edit'),
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
