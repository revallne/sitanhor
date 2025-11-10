<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $primaryKey = 'kode_kategori';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['kode_kategori', 'nama_kategori', 'syarat_masa_dinas'];

    public function pengajuans() {
        return $this->hasMany(Pengajuan::class, 'kategori_kode_kategori', 'kode_Kategori');
    }
}
