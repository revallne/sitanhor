<?php

namespace App\Filament\Widgets;

use App\Models\Periode;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder; // Import Builder

class PeriodeTable extends BaseWidget
{
    // Pertahankan columnSpan full
    protected int | string | array $columnSpan = 'full'; 

    // Judul yang lebih menonjol
    protected static ?string $heading = 'Periode Berjalan Saat Ini';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['renmin', 'personel']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Periode::query()
                    // Mengambil periode yang statusnya 'buka' atau yang terakhir
                    ->where('status', 'buka')
                    ->orderBy('tahun', 'desc')
                    ->limit(1)
            )
            ->paginated(false)
            ->searchable(false)
            ->filters([])
            // ✨ PERBAIKAN TAMPILAN: Menghilangkan garis tabel agar terlihat seperti kartu info ✨
            ->striped(false)
            ->defaultSort('tahun', 'desc')
            
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun Periode')
                    ->weight('bold')
                    ->size('lg')
                    ->color('primary') // Menyorot tahun dengan warna primary
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tanggalAwal')
                    ->label('Tanggal Mulai')
                    ->formatStateUsing(function ($state) {
                        if (! $state) return null;
                        return Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('d F Y');
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tanggalAkhir')
                    ->label('Tanggal Selesai')
                    ->formatStateUsing(function ($state) {
                        if (! $state) return null;
                        return Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('d F Y');
                    })
                    ->alignCenter(),
            ])
            // ✨ Perbaikan Content Layout: Mengatur tampilan baris (opsional) ✨
            ->contentGrid(null) // Pastikan tidak ada grid kustom yang merusak tampilan tabel
            
            // ✨ Tambahkan Styling Header dan Footer agar terlihat clean ✨
            ->headerActions([
                Tables\Actions\Action::make('info')
                    ->label('Informasi Penting')
                    ->icon('heroicon-o-information-circle')
                    ->color('gray')
                    ->action(function () {
                        // Tidak ada action, hanya sebagai dekorasi
                    })
                    ->tooltip('Periode ini menentukan pengajuan yang valid saat ini.')
            ])
            ->heading('Periode Pengajuan')
            ->description('Informasi periode pengajuan tanda kehormatan Polri.');
    }

    // Perbaikan Query: Memprioritaskan status 'buka'
    public function getTableQuery(): Builder
    {
        return Periode::query()
            ->where('status', 'buka')
            ->orderBy('tahun', 'desc')
            ->limit(1);
    }
}