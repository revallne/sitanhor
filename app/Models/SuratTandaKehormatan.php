<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTandaKehormatan extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'noKepres', 'tanggalKepres',
        'namaFile', 'pathFile'
    ];

    public function pengajuan() {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'id');
    }
}
