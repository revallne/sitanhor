<?php

namespace App\Filament\Resources\PersonelResource\Pages;

use App\Filament\Resources\PersonelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Satker;
use App\Models\Kategori;

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

    public function getTabs(): array
    {
        $user = Auth::user();
        
        // Cek jika bukan Bagwatpers atau Renmin, kembalikan array kosong (tanpa tabs)
        if (!$user || !$user->hasRole(['bagwatpers', 'renmin'])) {
            return [];
        }
        
        // Logika untuk Renmin: Membatasi data hanya Satker milik Renmin
        $satkerKode = null;
        if ($user->hasRole('renmin')) {
            $satker = Satker::where('user_email', $user->email)->first();
            $satkerKode = $satker->kode_satker ?? null;
        }

        // Ambil kategori yang terkait dengan masa dinas (sesuaikan kodenya di model Kategori)
        // Asumsi kode kategori adalah '8_TAHUN', '16_TAHUN', '24_TAHUN', dst.
        $kategoriMasaDinas = Kategori::where('syarat_masa_dinas', '>', 0)->get();

        $tabs = [];

        // TAB 1: SEMUA PERSONEL (Hanya berdasarkan Role akses, tanpa filter masa dinas)
        $tabs['all'] = Tab::make('Semua Personel')
            ->modifyQueryUsing(function (Builder $query) use ($satkerKode) {
                // Terapkan pembatasan Satker untuk Renmin
                if ($satkerKode) {
                    $query->where('kode_satker', $satkerKode);
                }
                return $query;
            });
            
        // TAB DINAMIS: POTENSIAL PER KATEGORI
        foreach ($kategoriMasaDinas as $kategori) {
            $syaratDinasTahun = $kategori->syarat_masa_dinas;
            $kodeKategori = $kategori->kode_kategori;
            $label = "Memenuhi Syarat {$syaratDinasTahun} Tahun"; // Contoh: Potensial 8 Tahun

            $tabs[$kodeKategori] = Tab::make($label)
                ->modifyQueryUsing(function (Builder $query) use ($syaratDinasTahun, $kodeKategori, $satkerKode) {
                    
                    // 1. FILTER BERDASARKAN MASA DINAS
                    // Cek apakah masa dinas sudah cukup
                    $query->whereRaw("TIMESTAMPDIFF(YEAR, tmt_pertama, CURDATE()) >= ?", [$syaratDinasTahun]);

                    // 2. FILTER BERDASARKAN BELUM ADA PENGAJUAN YANG VALID
                    $query->whereDoesntHave('pengajuans', function (Builder $subQuery) use ($kodeKategori) {
                        $subQuery->where('kategori_kode_kategori', $kodeKategori)
                                 // Memastikan belum ada pengajuan yang Statusnya sudah diproses/valid
                                 ->whereIn('status', ['Menunggu Verifikasi', 'Terverifikasi', 'Proses Pengajuan', 'Selesai']);
                    });

                    // 3. FILTER BERDASARKAN SATKER (Jika Renmin)
                    if ($satkerKode) {
                        $query->where('kode_satker', $satkerKode);
                    }
                    
                    return $query;
                });
        }
        
        return $tabs;
    }
}
