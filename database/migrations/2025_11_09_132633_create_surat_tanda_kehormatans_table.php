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
        Schema::create('surat_tanda_kehormatans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengajuan_id');
            $table->string('noKepres');
            $table->date('tanggalKepres');
            $table->string('file_surat');
            // $table->string('namaFile');
            // $table->string('pathFile');
            //$table->date('tanggalUpload');

            $table->foreign('pengajuan_id')->references('id')->on('pengajuans')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_tanda_kehormatans');
    }
};
