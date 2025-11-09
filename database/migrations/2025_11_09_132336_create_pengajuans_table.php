<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->string('personel_nrp');
            $table->year('periode_tahun');
            $table->integer('kategori_kode_kategori');
            // $table->unsignedBigInteger('surat_tanda_kehormatan_id')->nullable();
            $table->string('suratTandaKehormatan')->nullable();
            $table->date('tanggalPengajuan');
            $table->string('namaFile_SK_TMT');
            $table->string('pathFile_SK_TMT');
            $table->string('namaFile_SK_pangkat');
            $table->string('pathFile_SK_pangkat');
            $table->string('namaFile_SK_jabatan');
            $table->string('pathFile_SK_jabatan');
            $table->string('status')->default('Menunggu Verifikasi');
            $table->text('catatan')->nullable();

            $table->foreign('personel_nrp')->references('nrp')->on('personels')->onDelete('cascade');
            $table->foreign('periode_tahun')->references('tahun')->on('periodes')->onDelete('cascade');
            $table->foreign('kategori_kode_kategori')->references('kode_kategori')->on('kategoris')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
