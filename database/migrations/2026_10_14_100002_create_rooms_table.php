<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('id_room');
            $table->string('nama_room', 100);
            $table->string('jenis_room', 50);
            $table->text('deskripsi')->nullable();
            $table->integer('kapasitas');
            $table->string('status', 30)->default('aktif');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
