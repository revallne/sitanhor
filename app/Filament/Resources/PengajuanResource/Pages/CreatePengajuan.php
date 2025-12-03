<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;
    protected ?string $heading = 'Pengajuan Baru';

    protected ?string $subheading = 'Formulir Pengajuan Tanda Kehormatan Polri';
}
