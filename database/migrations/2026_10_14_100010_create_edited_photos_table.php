<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edited_photos', function (Blueprint $table) {
            $table->id('id_photo');
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_item')->nullable();
            $table->string('nama_file', 255);
            $table->string('drive_file_id', 255)->nullable();
            $table->text('drive_url')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('ukuran_file')->nullable();
            $table->timestamp('uploaded_at')->nullable();

            $table->foreign('id_request')->references('id_request')->on('photo_edit_requests')->cascadeOnDelete();
            $table->foreign('id_item')->references('id_item')->on('edit_request_items')->nullOnDelete();

            $table->index('id_request');
            $table->index('drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edited_photos');
    }
};
