<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Satker extends Model
{
    // Mendefinisikan Primary Key kustom sesuai ERD
    protected $primaryKey = 'kode_satker';
    public $incrementing = false; // Karena PK bukan auto-increment standar
    protected $keyType = 'int';

    protected $fillable = [
        'kode_satker',
        'user_email',
        'deskripsi',
    ];

    // Relasi: Satu Satker memiliki banyak Personel
    public function personel(): HasMany
    {
        return $this->hasMany(Personel::class, 'kode_satker', 'kode_satker');
    }

    // Relasi ke User (akun login)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }
}
