<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personel extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'nrp';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nrp', 'tmt_pertama', 'pangkat', 'jabatan', 'tempat_lahir', 'tanggal_lahir',
        'kode_satker', 'user_email'
    ];

    public function satker() {
        return $this->belongsTo(Satker::class, 'kode_satker', 'kode_satker');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }

    public function pengajuans() {
        return $this->hasMany(Pengajuan::class, 'personel_nrp', 'nrp');
    }
}
