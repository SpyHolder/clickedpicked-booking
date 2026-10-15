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
        Schema::create('edited_photos', function (Blueprint $table) {
            $table->id('id_photo');
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_item')->nullable();
            $table->string('nama_file', 255);
            $table->string('drive_file_id', 255);
            $table->text('drive_url');
            $table->string('mime_type', 100);
            $table->bigInteger('ukuran_file');
            $table->timestamp('uploaded_at');

            $table->foreign('id_request')->references('id_request')->on('photo_edit_requests')->cascadeOnDelete();
            $table->foreign('id_item')->references('id_item')->on('edit_request_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edited_photos');
    }
};
