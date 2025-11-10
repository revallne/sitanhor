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
        Schema::create('satkers', function (Blueprint $table) {
            $table->integer('kode_satker')->primary();
            $table->string('nama_satker')->unique();

            // Menambahkan Foreign Key ke Users via email
            // Pastikan kolom 'email' di tabel 'users' sudah unique
            $table->string('user_email')->unique(); 
            $table->foreign('user_email')
                  ->references('email')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Atau 'cascade'/'restrict' sesuai kebutuhan
            $table->string('deskripsi');    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satkers');
    }
};
