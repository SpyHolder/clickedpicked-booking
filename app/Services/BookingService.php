<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\RoomPackage;
use App\Enums\BookingStatus;
use App\Utils\PhoneNormalizer;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Generate kode booking: BK-XXXXXXXX (8 karakter dari base-32).
     * PRD 4.3 (dikonfirmasi).
     */
    private function generateBookingCode(): string
    {
        // 32 simbol: 0-9 dan A-V (tanpa huruf yang bisa tertukar: I,O,W,X,Y,Z)
        $chars = '0123456789ABCDEFGHJKLMNPQRSTUV';

        do {
            $code = 'BK-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (Booking::where('kode_booking', $code)->exists());

        return $code;
    }

    /**
     * PRD 7.1 langkah 2: Kunci slot dan buat booking dalam satu transaksi.
     * Memakai lockForUpdate() untuk mencegah double-booking.
     *
     * @return array{success: bool, message: string, booking: ?Booking}
     */
    public function lockSlotAndCreateBooking(array $draft, array $slotData): array
    {
        return DB::transaction(function () use ($draft, $slotData) {
            $package = RoomPackage::where('id_package', $draft['id_package'])
                ->where('id_room', $draft['id_room'])
                ->where('is_active', true)
                ->first();

            if (!$package) {
                return ['success' => false, 'message' => 'Paket tidak ditemukan atau tidak aktif', 'booking' => null];
            }

            $tanggalSesi = $slotData['tanggal_sesi'];
            $jamMulai = $slotData['jam_mulai'];
            $jamSelesai = Carbon::createFromFormat('H:i', $jamMulai)
                ->addMinutes($package->durasi_menit)
                ->format('H:i');

            // Cek bentrok dengan lockForUpdate (PRD 7.1.5)
            $conflict = BookingDetail::where('id_room', $draft['id_room'])
                ->where('tanggal_sesi', $tanggalSesi)
                ->whereHas('booking', function ($q) {
                    $q->whereIn('status_booking', [
                        BookingStatus::PENDING->value,
                        BookingStatus::CONFIRMED->value,
                    ]);
                })
                ->lockForUpdate()
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    // PRD 7.1.5: bentrok jika existing.mulai < new.selesai AND existing.selesai > new.mulai
                    $q->where('jam_mulai', '<', $jamSelesai)
                      ->where('jam_selesai', '>', $jamMulai);
                })
                ->exists();

            if ($conflict) {
                return ['success' => false, 'message' => 'Slot sudah terisi. Silakan pilih slot lain.', 'booking' => null];
            }

            // Buat booking
            $booking = Booking::create([
                'kode_booking' => $this->generateBookingCode(),
                'nama_pelanggan' => $draft['nama_pelanggan'],
                'no_hp_pelanggan' => $draft['no_hp_pelanggan'],
                'email_pelanggan' => $draft['email_pelanggan'] ?? null,
                'tanggal_booking' => now()->toDateString(),
                'status_booking' => BookingStatus::PENDING->value,
                'total_harga' => $package->harga,
            ]);

            // Buat booking detail
            BookingDetail::create([
                'id_booking' => $booking->id_booking,
                'id_room' => $draft['id_room'],
                'id_package' => $draft['id_package'],
                'tanggal_sesi' => $tanggalSesi,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'jumlah_orang' => $slotData['jumlah_orang'],
                'harga' => $package->harga,
            ]);

            return ['success' => true, 'message' => 'Booking berhasil dibuat', 'booking' => $booking];
        });
    }

    /**
     * Ambil slot yang tersedia untuk studio, paket, dan tanggal tertentu (PRD 7.1.2).
     * Mengembalikan JSON slot kosong.
     */
    public function getAvailableSlots(int $roomId, int $packageId, string $date): array
    {
        $package = RoomPackage::find($packageId);
        if (!$package) return [];

        $durasiMenit = $package->durasi_menit;

        // Slot booking yang sudah ada (pending/confirmed)
        $bookedSlots = BookingDetail::where('id_room', $roomId)
            ->where('tanggal_sesi', $date)
            ->whereHas('booking', function ($q) {
                $q->whereIn('status_booking', [
                    BookingStatus::PENDING->value,
                    BookingStatus::CONFIRMED->value,
                ]);
            })
            ->select('jam_mulai', 'jam_selesai')
            ->get()
            ->toArray();

        // Generate slot mulai 08:00 sampai 21:00 (contoh, bisa dikonfigurasi)
        $slots = [];
        $start = Carbon::createFromFormat('H:i', '08:00');
        $end = Carbon::createFromFormat('H:i', '21:00');

        while ($start->copy()->addMinutes($durasiMenit)->lte($end)) {
            $slotStart = $start->format('H:i');
            $slotEnd = $start->copy()->addMinutes($durasiMenit)->format('H:i');

            // Cek apakah bentrok
            $isAvailable = true;
            foreach ($bookedSlots as $booked) {
                if ($slotStart < $booked['jam_selesai'] && $slotEnd > $booked['jam_mulai']) {
                    $isAvailable = false;
                    break;
                }
            }

            $slots[] = [
                'jam_mulai' => $slotStart,
                'jam_selesai' => $slotEnd,
                'available' => $isAvailable,
            ];

            $start->addMinutes(30); // interval 30 menit
        }

        return $slots;
    }

    /**
     * Cek ketersediaan satu slot tertentu (PRD 7.1.5).
     */
    public function checkSlotAvailability(int $roomId, string $date, string $jamMulai, string $jamSelesai): bool
    {
        $conflict = BookingDetail::where('id_room', $roomId)
            ->where('tanggal_sesi', $date)
            ->whereHas('booking', function ($q) {
                $q->whereIn('status_booking', [
                    BookingStatus::PENDING->value,
                    BookingStatus::CONFIRMED->value,
                ]);
            })
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();

        return !$conflict;
    }

    /**
     * Ambil booking by kode.
     */
    public function getBookingByCode(string $code): ?Booking
    {
        return Booking::with(['details.room', 'details.package', 'payments'])
            ->where('kode_booking', $code)
            ->first();
    }

    /**
     * Daftar semua booking (admin) dengan filter status.
     */
    public function getAllBookings(int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $query = Booking::with(['details.room']);

        if ($status) {
            $query->where('status_booking', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Update status booking oleh admin (Keputusan #5):
     * pending → cancelled
     * confirmed → done | cancelled
     * confirmed hanya lewat verifikasi pembayaran (bukan di sini).
     */
    public function updateBookingStatus(Booking $booking, BookingStatus $newStatus): array
    {
        $allowed = match ($booking->status_booking) {
            BookingStatus::PENDING => [BookingStatus::CANCELLED],
            BookingStatus::CONFIRMED => [BookingStatus::DONE, BookingStatus::CANCELLED],
            default => [],
        };

        if (!in_array($newStatus, $allowed)) {
            return ['success' => false, 'message' => 'Perubahan status tidak diizinkan'];
        }

        $booking->update(['status_booking' => $newStatus->value]);
        return ['success' => true, 'message' => 'Status booking berhasil diubah'];
    }

    /**
     * Konfirmasi booking (dipanggil dari PaymentService setelah verifikasi).
     */
    public function confirmBooking(Booking $booking): void
    {
        $booking->update(['status_booking' => BookingStatus::CONFIRMED->value]);
    }
}