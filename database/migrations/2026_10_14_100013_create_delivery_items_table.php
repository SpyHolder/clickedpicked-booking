<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id('id_delivery_item');
            $table->unsignedBigInteger('id_delivery');
            $table->unsignedBigInteger('id_photo');

            $table->foreign('id_delivery')->references('id_delivery')->on('photo_deliveries')->cascadeOnDelete();
            $table->foreign('id_photo')->references('id_photo')->on('edited_photos')->restrictOnDelete();

            $table->unique(['id_delivery', 'id_photo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_items');
    }
};
