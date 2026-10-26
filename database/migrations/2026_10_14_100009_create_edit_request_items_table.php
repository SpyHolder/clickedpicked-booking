<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edit_request_items', function (Blueprint $table) {
            $table->id('id_item');
            $table->unsignedBigInteger('id_request');
            $table->string('nama_file', 255);
            $table->text('instruksi_edit')->nullable();
            $table->timestamps();

            $table->foreign('id_request')->references('id_request')->on('photo_edit_requests')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edit_request_items');
    }
};
