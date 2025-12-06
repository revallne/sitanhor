<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat Akun Baru'),
        ];
    }
    public function getTabs(): array
    {
        // Mendapatkan builder dasar model User untuk digunakan di badge
        $userBuilder = User::query();

        return [
            // -----------------------
            // ▶ TAB SEMUA AKUN
            // -----------------------
            'all' => Tab::make('Semua Akun'),


            // -----------------------
            // ▶ TAB RENMIN
            // -----------------------
            'renmin' => Tab::make('Renmin')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas('roles', fn($q) => $q->where('name', 'renmin'))
                )
                ->icon('heroicon-o-user-group'),

            // -----------------------
            // ▶ TAB PERSONEL
            // -----------------------
            'personel' => Tab::make('Personel')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas('roles', fn($q) => $q->where('name', 'personel'))
                )
                ->icon('heroicon-o-user'),

            // -----------------------
            // ▶ TAB BAGWATPERS (ADMIN)
            // -----------------------
            'bagwatpers' => Tab::make('Admin (Bagwatpers)')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas('roles', fn($q) => $q->where('name', 'bagwatpers'))
                )
                ->icon('heroicon-o-shield-check'),

        ];
    }
}
