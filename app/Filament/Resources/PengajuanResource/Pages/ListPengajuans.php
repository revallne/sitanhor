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

    protected ?string $heading = 'Pengajuan Tanda Kehormatan';

    protected ?string $subheading = 'Daftar Pengajuan yang Telah Dibuat';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Ekspor Data Pengajuan')
                ->openUrlInNewTab(),
            Actions\CreateAction::make()->label('Buat Pengajuan Baru'),
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

            'ditolak' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Ditolak')
                ),

            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Selesai')
                ),
        ];
    }

}
