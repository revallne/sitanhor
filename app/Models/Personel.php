<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personel extends Model
{
    // Mendefinisikan Primary Key kustom (NRP sebagai string)
    protected $primaryKey = 'nrp';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nrp',
        'user_email',
        'kode_satker',
        'tmt_pertama',
        'pangkat',
        'jabatan',
        'tempat_lahir',
    ];

    protected $casts = [
        'tmt_pertama' => 'date',
    ];

    // Relasi ke User (akun login)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }

    // Relasi ke Satker
    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class, 'kode_satker', 'kode_satker');
    }

    // // Relasi ke Pengajuan (Sesuai ERD, Personel mengajukan banyak Pengajuan)
    // public function pengajuan(): HasMany
    // {
    //     // Asumsi Anda nanti membuat model Pengajuan dengan FK 'nrp'
    //     return $this->hasMany(Pengajuan::class, 'personel_nrp', 'nrp');
    // }
}
