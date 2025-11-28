<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Resources\Pages\Page;

class DetailPengajuan extends Page
{
    protected static string $resource = PengajuanResource::class;

    protected static string $view = 'filament.resources.pengajuan-resource.pages.detail-pengajuan';
}
