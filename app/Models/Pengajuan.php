<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pengajuan extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'personel_nrp', 'periode_tahun', 'kategori_kode_kategori',
        'suratTandaKehormatan', 'tanggalPengajuan',
        'namaFile_SK_TMT', 'pathFile_SK_TMT',
        'namaFile_SK_pangkat', 'pathFile_SK_pangkat',
        'namaFile_SK_jabatan', 'pathFile_SK_jabatan',
        'status', 'catatan'
    ];

    public function personel() {
        return $this->belongsTo(Personel::class, 'personel_nrp', 'nrp');
    }

    public function periode() {
        return $this->belongsTo(Periode::class, 'periode_tahun', 'tahun');
    }

    public function kategori() {
        return $this->belongsTo(Kategori::class, 'kategori_kode_kategori', 'kode_kategori');
    }

    public function suratTandaKehormatan() {
        return $this->hasOne(SuratTandaKehormatan::class, 'pengajuan_id', 'id');
    }
}
