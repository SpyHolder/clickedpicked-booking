<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frames', function (Blueprint $table) {
            $table->id('id_frame');
            $table->string('kode_frame', 50)->unique();
            $table->string('nama_frame', 100);
            $table->unsignedTinyInteger('jumlah_slot')->default(2);
            $table->string('warna_aksen', 30)->nullable();
            $table->string('preview_path', 255)->nullable();
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('photo_edit_requests', function (Blueprint $table) {
            $table->string('jenis_layanan', 30)->default('foto_frame')->after('id_detail');
            $table->unsignedBigInteger('id_frame')->nullable()->after('jenis_layanan');
            $table->unsignedTinyInteger('jumlah_slot')->nullable()->after('id_frame');

            $table->foreign('id_frame')->references('id_frame')->on('frames')->nullOnDelete();
        });

        Schema::table('edited_photos', function (Blueprint $table) {
            $table->string('kategori_hasil', 30)->default('foto_frame')->after('id_item');
        });
    }

    public function down(): void
    {
        Schema::table('edited_photos', function (Blueprint $table) {
            $table->dropColumn('kategori_hasil');
        });

        Schema::table('photo_edit_requests', function (Blueprint $table) {
            $table->dropForeign(['id_frame']);
            $table->dropColumn(['jenis_layanan', 'id_frame', 'jumlah_slot']);
        });

        Schema::dropIfExists('frames');
    }
};
