<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_packages', function (Blueprint $table) {
            $table->id('id_package');
            $table->unsignedBigInteger('id_room');
            $table->decimal('harga', 12, 2);
            $table->string('tipe_harga', 50);
            $table->integer('durasi_menit');
            $table->text('deskripsi_benefit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_room')->references('id_room')->on('rooms')->cascadeOnDelete();
            $table->index(['id_room', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_packages');
    }
};
