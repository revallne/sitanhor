<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periode extends Model
{
    protected $primaryKey = 'tahun';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['tahun', 'tanggalAwal', 'tanggalAkhir'];

    public function pengajuans() {
        return $this->hasMany(Pengajuan::class, 'periode_tahun', 'tahun');
    }
}
