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
        Schema::create('photo_deliveries', function (Blueprint $table) {
            $table->id('id_delivery');
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->string('metode', 20);
            $table->string('recipient_phone', 20);
            $table->text('pesan');
            $table->string('status_pengiriman', 30)->default('pending');
            $table->string('external_message_id', 255)->nullable();
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->text('response_api')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('id_request')->references('id_request')->on('photo_edit_requests')->cascadeOnDelete();
            $table->foreign('id_admin')->references('id_admin')->on('admins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_deliveries');
    }
};
