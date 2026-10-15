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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('id_booking');
            $table->string('kode_booking', 50)->unique();
            $table->string('nama_pelanggan', 100);
            $table->string('no_hp_pelanggan', 20);
            $table->string('email_pelanggan', 100)->nullable();
            $table->date('tanggal_booking');
            $table->string('status_booking', 30)->default('pending');
            $table->decimal('total_harga', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
