<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratTandaKehormatan extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'noKepres', 'tanggalKepres',
        'file_surat'
    ];

    public function pengajuan() {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'id');
    }
}
