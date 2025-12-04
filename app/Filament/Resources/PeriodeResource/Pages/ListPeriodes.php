<?php

namespace App\Filament\Resources\PeriodeResource\Pages;

use App\Filament\Resources\PeriodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodes extends ListRecords
{
    protected static string $resource = PeriodeResource::class;

    protected ?string $heading = 'Periode';

    protected ?string $subheading = 'Periode Pengajuan Tanda Kehormatan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buka Periode Baru'),
        ];
    }
}
