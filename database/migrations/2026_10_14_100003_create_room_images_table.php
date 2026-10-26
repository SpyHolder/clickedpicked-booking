<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_images', function (Blueprint $table) {
            $table->id('id_image');
            $table->unsignedBigInteger('id_room');
            $table->string('image_url', 255);
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_room')->references('id_room')->on('rooms')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_images');
    }
};
