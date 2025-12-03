<?php

namespace App\Filament\Resources\SuratTandaKehormatanResource\Pages;

use App\Filament\Resources\SuratTandaKehormatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSuratTandaKehormatans extends ListRecords
{
    protected static string $resource = SuratTandaKehormatanResource::class;

    protected ?string $heading = 'Tanda Kehormatan';

    protected ?string $subheading = 'Tanda Kehormatan yang Diterima';


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        // Personel tidak pakai tabs
        if ($user->hasRole('personel')) {
            return [];
        }

        return [
            'all' => Tab::make('Semua'),
            
            '8tahun' => Tab::make('8 Tahun')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('pengajuan', function ($q) {
                        $q->where('kategori_kode_kategori', '8');
                    })
                ),

            '16tahun' => Tab::make('16 Tahun')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('pengajuan', function ($q) {
                        $q->where('kategori_kode_kategori', '16');
                    })
                ),

            '24tahun' => Tab::make('24 Tahun')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('pengajuan', function ($q) {
                        $q->where('kategori_kode_kategori', '24');
                    })
                ),

            '32tahun' => Tab::make('32 Tahun')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('pengajuan', function ($q) {
                        $q->where('kategori_kode_kategori', '32');
                    })
                ),
        ];
    }

}
