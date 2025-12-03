<?php

namespace App\Filament\Resources\PersonelResource\Pages;

use App\Filament\Resources\PersonelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;

class CreatePersonel extends CreateRecord
{
    protected static string $resource = PersonelResource::class;

    protected static ?string $title = 'Data Personel Baru';
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan data 'user_email' dan 'user.name' ada
        if (isset($data['user_email']) && isset($data['user']['name'])) {
            $userName = $data['user']['name'];
            $userEmail = $data['user_email'];

            // Cari atau buat User berdasarkan email
            $user = User::firstOrNew(['email' => $userEmail]);
            
            // Update nama User dan simpan
            $user->name = $userName;
            $user->password = $user->password ?? \Hash::make('password'); // Atur password jika user baru
            $user->save();

            // Hapus 'user' dari data Personel
            unset($data['user']);
        }
        
        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Data Baru'), // Mengubah label "Create"
            
            $this->getCreateAnotherFormAction()
                ->label('Simpan & Buat Lainnya'), // Mengubah label "Create & create another"
                
            $this->getCancelFormAction()
                ->label('Batalkan'), // Mengubah label "Cancel"
        ];
    }
    
}


