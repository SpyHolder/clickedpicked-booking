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
        Schema::create('photo_edit_requests', function (Blueprint $table) {
            $table->id('id_request');
            $table->string('kode_request', 50)->unique();
            $table->unsignedBigInteger('id_room');
            $table->unsignedBigInteger('id_booking')->nullable();
            $table->unsignedBigInteger('id_detail')->nullable();
            $table->string('sumber', 20);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->string('nama_pelanggan', 100);
            $table->string('nomor_wa', 20);
            $table->boolean('perlu_dicek')->default(false);
            $table->boolean('persetujuan_data')->default(false);
            $table->timestamp('persetujuan_at')->nullable();
            $table->string('status_edit', 30)->default('menunggu_verifikasi');
            $table->text('alasan_penolakan')->nullable();
            $table->string('drive_folder_id', 255)->nullable();
            $table->text('drive_folder_url')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('id_room')->references('id_room')->on('rooms')->restrictOnDelete();
            $table->foreign('id_booking')->references('id_booking')->on('bookings')->nullOnDelete();
            $table->foreign('id_detail')->references('id_detail')->on('booking_details')->nullOnDelete();
            $table->foreign('created_by')->references('id_admin')->on('admins')->nullOnDelete();
            $table->foreign('verified_by')->references('id_admin')->on('admins')->nullOnDelete();
            $table->foreign('handled_by')->references('id_admin')->on('admins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_edit_requests');
    }
};
