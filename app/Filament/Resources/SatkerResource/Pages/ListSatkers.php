<?php

namespace App\Filament\Resources\SatkerResource\Pages;

use App\Filament\Resources\SatkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSatkers extends ListRecords
{
    protected static string $resource = SatkerResource::class;
        protected ?string $heading = 'Satuan Kerja';

    protected ?string $subheading = 'Satuan Kerja di Polda Jawa Tengah';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Satuan Kerja Baru'),
        ];
    }
}
