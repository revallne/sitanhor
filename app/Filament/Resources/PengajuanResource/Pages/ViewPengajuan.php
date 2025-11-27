<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

class ViewPengajuan extends ViewRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    // protected function getFormSchema(): array
    // {
    //     return [
    //         Forms\Components\TextInput::make('personel_nrp')->label('NRP')->disabled(),
    //         Forms\Components\TextInput::make('periode_tahun')->label('Periode')->disabled(),
    //         Forms\Components\TextInput::make('kategori.nama_kategori')->label('Kategori')->disabled(),
    //         // ... field lain sesuai kebutuhan

    //         Forms\Components\Textarea::make('catatan')
    //             ->label('Catatan Penolakan')
    //             ->visible(fn ($record) => $record->status === 'Ditolak')
    //             ->disabled()
    //             ->columnSpanFull(),
    //     ];
    // }

    
}
