<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\PengajuanObserver;


#[ObservedBy(PengajuanObserver::class)]
class Pengajuan extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        'personel_nrp', 'periode_tahun', 'kategori_kode_kategori',
        'surat_tanda_kehormatan', 'tanggal_pengajuan',
        'sk_tmt',
        'sk_pangkat',
        'sk_jabatan',
        'drh',
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
