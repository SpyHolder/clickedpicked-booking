<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('rooms', 'cabang')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('cabang', 50)->default('batu_lima')->after('nama_room');
                $table->index('cabang');
            });
        }

        // Normalisasi data existing agar terisi cabang dan jenis_room standar
        $rooms = DB::table('rooms')->get();
        foreach ($rooms as $room) {
            $namaLower = strtolower($room->nama_room ?? '');
            $jenisLower = strtolower($room->jenis_room ?? '');

            $cabang = 'batu_lima';
            if (str_contains($namaLower, 'merdeka') || str_contains($jenisLower, 'merdeka')) {
                $cabang = 'jalan_merdeka';
            }

            $jenis = 'photo_studio';
            if (str_contains($namaLower, 'booth') || str_contains($jenisLower, 'booth')) {
                $jenis = 'photo_booth';
            }

            // Bersihkan nama room jika mengandung prefix cabang manual
            $cleanName = $room->nama_room;
            if (preg_match('/Room\s*\d+/i', $room->nama_room, $matches)) {
                $cleanName = ucwords(strtolower(trim($matches[0])));
            }

            DB::table('rooms')
                ->where('id_room', $room->id_room)
                ->update([
                    'cabang' => $cabang,
                    'jenis_room' => $jenis,
                    'nama_room' => $cleanName,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rooms', 'cabang')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropIndex(['cabang']);
                $table->dropColumn('cabang');
            });
        }
    }
};
