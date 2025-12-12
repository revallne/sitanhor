<?php

namespace App\Filament\Resources\PersonelResource\Pages;

use App\Filament\Resources\PersonelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;

class EditPersonel extends EditRecord
{
    protected static string $resource = PersonelResource::class;

    protected static ?string $title = 'Edit Profil';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Lihat Data'),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ambil record Personel saat ini
        $personel = $this->getRecord();
        
        // Ambil data nama dari relasi User
        $userName = $personel->user->name ?? null;

        // Tambahkan data 'user.name' ke array data form
        // Ini akan mengisi field Forms\Components\TextInput::make('user.name')
        $data['user']['name'] = $userName;

        return $data;
    }


    /**
     * Mutate form data before it is saved to the Personel model.
     * (Hook untuk menyimpan nama ke tabel users, yang sudah kita buat sebelumnya)
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. Periksa apakah data 'user' dan 'name' ada dari form
        if (isset($data['user']['name'])) {
            $personel = $this->getRecord();
            $user = $personel->user;

            // 2. Jika Personel memiliki User yang berelasi, update namanya
            if ($user) {
                $user->name = $data['user']['name'];
                $user->save(); // Simpan perubahan nama ke tabel 'users'
            }
            
            // 3. Hapus 'user' dari data Personel
            unset($data['user']);
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan Data'), // Ubah label di sini
            
            $this->getCancelFormAction()
                ->label('Batalkan Edit'), // Ubah label di sini
        ];
    }
}
