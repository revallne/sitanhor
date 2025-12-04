<?php

namespace App\Filament\Resources\PersonelResource\Pages;

use App\Filament\Resources\PersonelResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class ViewPersonel extends ViewRecord
{
    protected static string $resource = PersonelResource::class;

    protected static ?string $title = 'Profil';

    

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Edit Data'),
            Actions\Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

     public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Personel')
                    ->description('Informasi detail Personel Polri')
                    ->schema([
                        TextEntry::make('user.name') // Menggunakan dot notation untuk relasi
                            ->label('Nama Lengkap')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('user_email')
                            ->label('Email Terdaftar')
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('nrp')
                            ->label('NRP'),
                        TextEntry::make('pangkat')
                            ->label('Pangkat'),
                        TextEntry::make('jabatan')
                            ->label('Jabatan'),
                        TextEntry::make('satker.nama_satker') // Menggunakan dot notation untuk relasi Satker
                            ->label('Satuan Kerja'),
                        TextEntry::make('tmt_pertama')
                            ->label('TMT Pertama')
                            ->date('d F Y'),
                        TextEntry::make('tempat_lahir')
                            ->label('Tempat Lahir'),
                        TextEntry::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->date('d F Y'),
                    ])
                    ->columns(2),
            ]);
    }
}
