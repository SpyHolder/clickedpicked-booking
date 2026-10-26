<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('id_booking');
            $table->unsignedBigInteger('id_room');
            $table->unsignedBigInteger('id_package');
            $table->date('tanggal_sesi');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('jumlah_orang');
            $table->decimal('harga', 12, 2);
            $table->timestamps();

            $table->foreign('id_booking')->references('id_booking')->on('bookings')->restrictOnDelete();
            $table->foreign('id_room')->references('id_room')->on('rooms')->restrictOnDelete();
            $table->foreign('id_package')->references('id_package')->on('room_packages')->restrictOnDelete();

            $table->index(['id_room', 'tanggal_sesi', 'jam_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};
