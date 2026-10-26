<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edit_status_logs', function (Blueprint $table) {
            $table->id('id_log');
            $table->unsignedBigInteger('id_request');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('status_lama', 30);
            $table->string('status_baru', 30);
            $table->timestamp('changed_at');

            $table->foreign('id_request')->references('id_request')->on('photo_edit_requests')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id_admin')->on('admins')->nullOnDelete();

            $table->index(['id_request', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edit_status_logs');
    }
};
