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
        Schema::create('personels', function (Blueprint $table) {
            // Sesuai ERD: PK adalah nrp (varchar)
            $table->string('nrp')->primary();
            
            $table->string('user_email')->unique();
            $table->foreign('user_email')
              ->references('email')
              ->on('users')
              ->onUpdate('cascade') // Penting jika email user berubah
              ->onDelete('cascade');
            
            // FK ke Satker
            $table->integer('kode_satker');
            $table->foreign('kode_satker')->references('kode_satker')->on('satkers')->onDelete('cascade')->onUpdate('cascade');

            // Data diri sesuai ERD
            $table->date('tmt_pertama'); // tmtPertama
            $table->string('pangkat');
            $table->string('jabatan');
            $table->string('tempat_lahir'); // tempatLahir
            $table->date('tanggal_lahir'); // tanggalLahir
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personels');
    }
};
