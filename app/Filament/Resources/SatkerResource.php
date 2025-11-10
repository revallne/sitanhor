<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SatkerResource\Pages;
use App\Filament\Resources\SatkerResource\RelationManagers;
use App\Models\Satker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SatkerResource extends Resource
{
    protected static ?string $model = Satker::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_satker')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('nama_satker')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('user_email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('deskripsi')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_satker')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_satker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSatkers::route('/'),
            'create' => Pages\CreateSatker::route('/create'),
            'view' => Pages\ViewSatker::route('/{record}'),
            'edit' => Pages\EditSatker::route('/{record}/edit'),
        ];
    }
}
