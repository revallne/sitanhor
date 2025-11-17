<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        // Jika role personel → tidak pakai tabs sama sekali
        if ($user->hasRole('personel')) {
            return [];
        }

        return [
            // -----------------------
            // ▶ TABS STATUS
            // -----------------------
            'all' => Tab::make('Semua'),

            'menunggu' => Tab::make('Menunggu Verifikasi')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Menunggu Verifikasi')
                ),

            'terverifikasi' => Tab::make('Terverifikasi')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Terverifikasi')
                ),

            'proses' => Tab::make('Proses Pengajuan')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Proses Pengajuan')
                ),

            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Selesai')
                ),

            'ditolak' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Ditolak')
                ),

            // -----------------------
            // ▶ TABS KATEGORI
            // -----------------------
            // '8tahun' => Tab::make('8 Tahun')
            //     ->modifyQueryUsing(fn (Builder $query) => 
            //         $query->where('kategori_id', '8')   // sesuaikan kolom kategori
            //     ),

            // '16tahun' => Tab::make('16 Tahun')
            //     ->modifyQueryUsing(fn (Builder $query) => 
            //         $query->where('kategori_id', '16')
            //     ),

            // '24tahun' => Tab::make('24 Tahun')
            //     ->modifyQueryUsing(fn (Builder $query) => 
            //         $query->where('kategori_id', '24')
            //     ),

            // '32tahun' => Tab::make('32 Tahun')
            //     ->modifyQueryUsing(fn (Builder $query) => 
            //         $query->where('kategori_id', '32')
            //     ),
        ];
    }

}
