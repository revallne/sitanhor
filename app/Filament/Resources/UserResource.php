<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Illuminate\Validation\Rule;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $pluralModelLabel = 'Akun Pengguna';

    protected static ?string $modelLabel = 'User';

    public static function getNavigationSort(): ?int
    {
        return 1;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Pengguna')
                    ->schema([

                        Grid::make(1) // 👉 memastikan semua field 1 kolom
                            ->schema([

                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->placeholder('Contoh: Dhela Revaline, S.Kom')
                                    ->required()
                                    ->maxLength(100)
                                    ->prefixIcon('heroicon-s-user')
                                    ->dehydrateStateUsing(fn($state) => ucwords(($state))),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->placeholder('nama@example.com')
                                    ->email()
                                    ->required()
                                    ->maxLength(50)
                                    ->prefixIcon('heroicon-s-envelope')
                                    ->helperText('Pastikan email valid dan aktif.')
                                    ->rules([
                                        'required',
                                        'email',
                                        // Rule: harus unik di tabel 'users', kolom 'email'
                                        'unique:users,email',
                                    ])
                                    // PENTING: Gunakan ignoreRecord() agar rule unique mengabaikan record yang sedang diedit
                                    ->unique(ignoreRecord: true)
                                    // Tambahkan pesan error kustom untuk rule 'unique'
                                    ->validationMessages([
                                        'unique' => 'Email ini sudah digunakan oleh akun lain. Harap gunakan email yang berbeda.',
                                    ]),

                                Forms\Components\TextInput::make('password')
                                    ->visibleOn('create')
                                    ->label('Password')
                                    ->password()
                                    ->required()
                                    ->prefixIcon('heroicon-s-lock-closed')
                                    ->maxLength(255),

                                Select::make('roles')
                                    ->label('Role Pengguna')
                                    ->multiple()
                                    ->preload()
                                    ->relationship('roles', 'name')
                                    ->placeholder('Pilih satu atau lebih role')
                                    ->searchable()
                                    ->required(),

                            ]),
                    ])
                    ->collapsible()   // Biar lebih mewah & modern
                    ->icon('heroicon-s-identification') // Ikon header section
                    ->columnSpan('full'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->wrap()
                    ->extraHeaderAttributes([
                        'style' => 'width: 400px;'
                    ])
                    ->extraAttributes([
                        'style' => 'width: 400px;'
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->wrap()
                    ->extraHeaderAttributes([
                        'style' => 'width: 300px;'
                    ])
                    ->extraAttributes([
                        'style' => 'width: 300px;'
                    ]),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('email_verified_at')
                //     ->dateTime()
                //     ->sortable(),
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
                Tables\Filters\TrashedFilter::make()->label('Data yang Dihapus'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
