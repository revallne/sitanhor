<?php

namespace App\Filament\Resources\PersonelResource\Pages;

use App\Filament\Resources\PersonelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonels extends ListRecords
{
    protected static string $resource = PersonelResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Personel Baru'),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();

        // Jika role personel → redirect ke halaman view data dirinya
        if ($user->hasRole('personel')) {

            // Ambil relasi user -> personel
            $personel = $user->personel;

            // Jika belum ada record Personel untuk user tersebut → arahkan ke halaman create
            if (! $personel) {
                $this->redirect( // bukan return redirect()
                    route('filament.sitanhor.resources.personels.create')
                );
                return;
            }

            // Jika sudah ada → arahkan ke halaman view datanya
            $this->redirect(
                route('filament.sitanhor.resources.personels.view', $personel->nrp)
            );
            return;
        }
    }
}
