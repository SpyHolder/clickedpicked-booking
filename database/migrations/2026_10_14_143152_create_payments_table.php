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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('id_payment');
            $table->unsignedBigInteger('id_booking')->nullable();
            $table->unsignedBigInteger('id_request')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->string('jenis_pembayaran', 30);
            $table->string('metode_pembayaran', 50);
            $table->decimal('jumlah_bayar', 12, 2);
            $table->date('tanggal_bayar');
            $table->string('status_pembayaran', 30)->default('pending');
            $table->string('bukti_pembayaran', 255);
            $table->timestamps();

            $table->foreign('id_booking')->references('id_booking')->on('bookings')->nullOnDelete();
            $table->foreign('verified_by')->references('id_admin')->on('admins')->nullOnDelete();
            $table->foreign('id_request')->references('id_request')->on('photo_edit_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
