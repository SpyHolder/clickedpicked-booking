<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\DeliveryStatus;
use App\Enums\EditStatus;
use App\Enums\RequestSource;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\EditedPhoto;
use App\Models\EditRequestItem;
use App\Models\EditStatusLog;
use App\Models\Frame;
use App\Models\PhotoDelivery;
use App\Models\PhotoEditRequest;
use App\Models\Room;
use App\Utils\PhoneNormalizer;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PhotoEditService
{
    /**
     * Generate kode request: ED-XXXXXXXX (8 karakter dari base-32).
     * PRD 4.3.
     */
    public function generateRequestCode(): string
    {
        $chars = '0123456789ABCDEFGHJKLMNPQRSTUV';

        do {
            $code = 'ED-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (PhotoEditRequest::where('kode_request', $code)->exists());

        return $code;
    }

    /**
     * Samarkan nomor WA untuk tampilan status publik (misal: 62812****7890).
     * PRD 6.4 & 10.
     */
    public function maskPhoneNumber(string $phone): string
    {
        $clean = trim($phone);
        $len = strlen($clean);

        if ($len <= 7) {
            return substr($clean, 0, 2) . '****';
        }

        $prefix = substr($clean, 0, 5);
        $suffix = substr($clean, -4);
        $maskedLength = max(4, $len - 9);

        return $prefix . str_repeat('*', $maskedLength) . $suffix;
    }

    /**
     * Cari sesi booking yang cocok di studio tersebut (PRD 7.3).
     * Jendela waktu: dari jam_mulai sesi sampai 24 jam setelah jam_selesai.
     * Hanya booking berstatus confirmed atau done. Jika ada beberapa, ambil sesi terbaru.
     */
    public function matchBooking(int $roomId, string $normalizedPhone, ?Carbon $now = null): ?BookingDetail
    {
        $now = $now ?? now();

        $candidates = BookingDetail::with('booking')
            ->where('id_room', $roomId)
            ->whereHas('booking', function ($query) use ($normalizedPhone) {
                $query->where('no_hp_pelanggan', $normalizedPhone)
                    ->whereIn('status_booking', [
                        BookingStatus::CONFIRMED->value,
                        BookingStatus::DONE->value,
                    ]);
            })
            ->orderByDesc('tanggal_sesi')
            ->orderByDesc('jam_mulai')
            ->get();

        foreach ($candidates as $detail) {
            $dateStr = $detail->tanggal_sesi instanceof Carbon
                ? $detail->tanggal_sesi->format('Y-m-d')
                : substr((string) $detail->tanggal_sesi, 0, 10);

            $startTime = Carbon::parse($dateStr . ' ' . $detail->jam_mulai);
            $endTime = Carbon::parse($dateStr . ' ' . $detail->jam_selesai)->addHours(24);

            if ($now->betweenIncluded($startTime, $endTime)) {
                return $detail;
            }
        }

        return null;
    }

    /**
     * Buat request edit foto dari form QR pelanggan (PRD 7.3).
     */
    public function createFromQr(Room $room, array $data): PhotoEditRequest
    {
        $normalizedPhone = PhoneNormalizer::normalize($data['nomor_wa']);
        $matchedDetail = $this->matchBooking($room->id_room, $normalizedPhone);

        $jenisLayanan = $data['jenis_layanan'] ?? 'foto_frame';
        $idFrame = ($jenisLayanan === 'foto_frame' && !empty($data['id_frame'])) ? (int) $data['id_frame'] : null;
        $jumlahSlot = !empty($data['jumlah_slot']) ? (int) $data['jumlah_slot'] : null;

        if ($idFrame && !$jumlahSlot) {
            $frame = Frame::find($idFrame);
            $jumlahSlot = $frame?->jumlah_slot;
        }

        if (!$jumlahSlot && !empty($data['items'])) {
            $jumlahSlot = count($data['items']);
        }

        return DB::transaction(function () use ($room, $data, $normalizedPhone, $matchedDetail, $jenisLayanan, $idFrame, $jumlahSlot) {
            $editRequest = PhotoEditRequest::create([
                'kode_request' => $this->generateRequestCode(),
                'id_room' => $room->id_room,
                'id_booking' => $matchedDetail?->id_booking,
                'id_detail' => $matchedDetail?->id_detail,
                'jenis_layanan' => $jenisLayanan,
                'id_frame' => $idFrame,
                'jumlah_slot' => $jumlahSlot,
                'sumber' => RequestSource::QR->value,
                'nama_pelanggan' => $data['nama_pelanggan'],
                'nomor_wa' => $normalizedPhone,
                'perlu_dicek' => $matchedDetail === null,
                'persetujuan_data' => true,
                'persetujuan_at' => now(),
                'status_edit' => EditStatus::MENUNGGU_VERIFIKASI->value,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $this->saveRequestItems($editRequest->id_request, $data['items'] ?? []);

            EditStatusLog::create([
                'id_request' => $editRequest->id_request,
                'changed_by' => null,
                'status_lama' => 'baru',
                'status_baru' => EditStatus::MENUNGGU_VERIFIKASI->value,
                'changed_at' => now(),
            ]);

            return $editRequest->load(['room', 'frame', 'items', 'booking', 'detail']);
        });
    }

    /**
     * Buat request edit foto secara manual oleh Admin (PRD 7.3 poin 5).
     */
    public function createFromAdmin(int $adminId, array $data): PhotoEditRequest
    {
        $normalizedPhone = PhoneNormalizer::normalize($data['nomor_wa']);
        $idBooking = null;
        $idDetail = null;
        $idRoom = (int) $data['id_room'];

        if (!empty($data['kode_booking'])) {
            $booking = Booking::with('details')
                ->where('kode_booking', strtoupper(trim($data['kode_booking'])))
                ->first();

            if ($booking) {
                $idBooking = $booking->id_booking;
                $detail = $booking->details->first();
                if ($detail) {
                    $idDetail = $detail->id_detail;
                    $idRoom = $detail->id_room;
                }
            }
        } else {
            $matchedDetail = $this->matchBooking($idRoom, $normalizedPhone);
            if ($matchedDetail) {
                $idBooking = $matchedDetail->id_booking;
                $idDetail = $matchedDetail->id_detail;
            }
        }

        $jenisLayanan = $data['jenis_layanan'] ?? 'foto_frame';
        $idFrame = ($jenisLayanan === 'foto_frame' && !empty($data['id_frame'])) ? (int) $data['id_frame'] : null;
        $jumlahSlot = !empty($data['jumlah_slot']) ? (int) $data['jumlah_slot'] : null;

        if ($idFrame && !$jumlahSlot) {
            $frame = Frame::find($idFrame);
            $jumlahSlot = $frame?->jumlah_slot;
        }

        if (!$jumlahSlot && !empty($data['items'])) {
            $jumlahSlot = count($data['items']);
        }

        return DB::transaction(function () use ($adminId, $data, $normalizedPhone, $idRoom, $idBooking, $idDetail, $jenisLayanan, $idFrame, $jumlahSlot) {
            $editRequest = PhotoEditRequest::create([
                'kode_request' => $this->generateRequestCode(),
                'id_room' => $idRoom,
                'id_booking' => $idBooking,
                'id_detail' => $idDetail,
                'jenis_layanan' => $jenisLayanan,
                'id_frame' => $idFrame,
                'jumlah_slot' => $jumlahSlot,
                'sumber' => RequestSource::ADMIN->value,
                'created_by' => $adminId,
                'nama_pelanggan' => $data['nama_pelanggan'],
                'nomor_wa' => $normalizedPhone,
                'perlu_dicek' => $idBooking === null,
                'persetujuan_data' => true,
                'persetujuan_at' => now(),
                'status_edit' => EditStatus::MENUNGGU_VERIFIKASI->value,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $this->saveRequestItems($editRequest->id_request, $data['items'] ?? []);

            EditStatusLog::create([
                'id_request' => $editRequest->id_request,
                'changed_by' => $adminId,
                'status_lama' => 'baru',
                'status_baru' => EditStatus::MENUNGGU_VERIFIKASI->value,
                'changed_at' => now(),
            ]);

            return $editRequest->load(['room', 'frame', 'items', 'booking', 'detail']);
        });
    }

    /**
     * Simpan daftar foto/instruksi edit ke tabel edit_request_items.
     */
    private function saveRequestItems(int $requestId, array $items): void
    {
        foreach ($items as $item) {
            $namaFile = trim((string) ($item['nama_file'] ?? ''));
            if ($namaFile === '') {
                continue;
            }

            EditRequestItem::create([
                'id_request' => $requestId,
                'nama_file' => $namaFile,
                'instruksi_edit' => !empty($item['instruksi_edit']) ? trim((string) $item['instruksi_edit']) : null,
            ]);
        }
    }

    /**
     * Upload foto hasil edit oleh Admin (mendukung kategori 'foto_frame' & 'custom_frame').
     * Menggunakan penyimpanan publik lokal secara otomatis sebagai fallback apabila Google Drive belum dikonfigurasi.
     */
    public function uploadEditedPhotos(PhotoEditRequest $editRequest, array $files, string $kategoriHasil = 'foto_frame', ?int $idItem = null): array
    {
        if (!in_array($editRequest->status_edit, [EditStatus::DIPROSES, EditStatus::SELESAI, EditStatus::DIKIRIM], true)) {
            return [
                'success' => false,
                'message' => 'Foto hasil edit hanya dapat diunggah saat request berstatus Diproses atau Selesai.',
            ];
        }

        $savedCount = 0;
        $folder = 'edited-photos/' . $editRequest->kode_request;

        DB::transaction(function () use ($editRequest, $files, $kategoriHasil, $idItem, $folder, &$savedCount) {
            foreach ($files as $file) {
                if (!$file instanceof UploadedFile || !$file->isValid()) {
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
                $storedPath = $file->storeAs($folder, $safeName, 'public');
                $publicUrl = asset('storage/' . $storedPath);

                EditedPhoto::create([
                    'id_request' => $editRequest->id_request,
                    'id_item' => $idItem ?: null,
                    'kategori_hasil' => in_array($kategoriHasil, ['foto_frame', 'custom_frame'], true) ? $kategoriHasil : 'foto_frame',
                    'nama_file' => $originalName,
                    'drive_file_id' => 'local:' . $storedPath,
                    'drive_url' => $publicUrl,
                    'mime_type' => $file->getClientMimeType(),
                    'ukuran_file' => $file->getSize(),
                    'uploaded_at' => now(),
                ]);

                $savedCount++;
            }

            if ($savedCount > 0 && empty($editRequest->drive_folder_url)) {
                $editRequest->update([
                    'drive_folder_id' => 'local-folder:' . $editRequest->kode_request,
                    'drive_folder_url' => route('edit-foto.status', $editRequest->kode_request),
                ]);
            }
        });

        if ($savedCount === 0) {
            return [
                'success' => false,
                'message' => 'Tidak ada file foto valid yang berhasil diunggah.',
            ];
        }

        return [
            'success' => true,
            'message' => "{$savedCount} file hasil edit berhasil diunggah.",
        ];
    }

    /**
     * Hapus satu file hasil edit yang sudah diunggah.
     */
    public function deleteEditedPhoto(PhotoEditRequest $editRequest, int $photoId): array
    {
        $photo = EditedPhoto::where('id_request', $editRequest->id_request)
            ->where('id_photo', $photoId)
            ->first();

        if (!$photo) {
            return [
                'success' => false,
                'message' => 'File foto tidak ditemukan.',
            ];
        }

        if ($photo->drive_file_id && str_starts_with($photo->drive_file_id, 'local:')) {
            $localPath = substr($photo->drive_file_id, 6);
            Storage::disk('public')->delete($localPath);
        }

        $photo->delete();

        return [
            'success' => true,
            'message' => 'File hasil edit berhasil dihapus.',
        ];
    }

    /**
     * Simpan / perbarui link folder Google Drive secara manual oleh Admin.
     */
    public function updateDriveLink(PhotoEditRequest $editRequest, ?string $driveUrl): array
    {
        $cleanUrl = trim((string) $driveUrl);

        $editRequest->update([
            'drive_folder_url' => $cleanUrl !== '' ? $cleanUrl : null,
            'drive_folder_id' => $cleanUrl !== '' ? ($editRequest->drive_folder_id ?: 'manual-drive-link') : null,
        ]);

        return [
            'success' => true,
            'message' => 'Tautan Google Drive berhasil diperbarui.',
        ];
    }

    /**
     * Catat pengiriman WhatsApp & ubah status menjadi Dikirim (PRD 7.5).
     */
    public function sendWhatsAppDelivery(PhotoEditRequest $editRequest, int $adminId, string $pesan): array
    {
        if (!in_array($editRequest->status_edit, [EditStatus::SELESAI, EditStatus::DIKIRIM], true)) {
            return [
                'success' => false,
                'message' => 'Pengiriman WhatsApp hanya dapat dilakukan setelah request ditandai Selesai.',
            ];
        }

        $oldStatus = $editRequest->status_edit;

        DB::transaction(function () use ($editRequest, $adminId, $pesan, $oldStatus) {
            PhotoDelivery::create([
                'id_request' => $editRequest->id_request,
                'id_admin' => $adminId,
                'metode' => 'wa',
                'recipient_phone' => $editRequest->nomor_wa,
                'pesan' => trim($pesan),
                'status_pengiriman' => DeliveryStatus::SENT->value,
                'sent_at' => now(),
            ]);

            if ($oldStatus !== EditStatus::DIKIRIM) {
                $editRequest->update([
                    'status_edit' => EditStatus::DIKIRIM->value,
                ]);

                EditStatusLog::create([
                    'id_request' => $editRequest->id_request,
                    'changed_by' => $adminId,
                    'status_lama' => $oldStatus->value,
                    'status_baru' => EditStatus::DIKIRIM->value,
                    'changed_at' => now(),
                ]);
            }
        });

        $waDirectUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $editRequest->nomor_wa) . '?text=' . rawurlencode(trim($pesan));

        return [
            'success' => true,
            'message' => 'Pengiriman WhatsApp berhasil dicatat dan status diperbarui menjadi Terkirim.',
            'wa_url' => $waDirectUrl,
        ];
    }

    /**
     * Lookup Pelanggan Kembali (PRD 7.4).
     */
    public function getReturningCustomerInfo(string $normalizedPhone, ?int $excludeRequestId = null, ?int $excludeBookingId = null): array
    {
        $prevRequestsQuery = PhotoEditRequest::where('nomor_wa', $normalizedPhone);
        if ($excludeRequestId) {
            $prevRequestsQuery->where('id_request', '!=', $excludeRequestId);
        }
        $requestCount = (clone $prevRequestsQuery)->count();
        $recentRequests = $prevRequestsQuery->orderByDesc('created_at')->limit(5)->get();

        $prevBookingsQuery = Booking::where('no_hp_pelanggan', $normalizedPhone);
        if ($excludeBookingId) {
            $prevBookingsQuery->where('id_booking', '!=', $excludeBookingId);
        }
        $bookingCount = (clone $prevBookingsQuery)->count();
        $recentBookings = $prevBookingsQuery->orderByDesc('created_at')->limit(5)->get();

        $combined = collect();

        foreach ($recentRequests as $req) {
            $combined->push([
                'type' => 'edit',
                'kode' => $req->kode_request,
                'status' => $req->status_edit instanceof EditStatus ? $req->status_edit->label() : (string) $req->status_edit,
                'color' => $req->status_edit instanceof EditStatus ? $req->status_edit->color() : 'slate',
                'tanggal' => $req->created_at,
                'url' => route('admin.edit-foto.show', $req->id_request),
            ]);
        }

        foreach ($recentBookings as $bk) {
            $combined->push([
                'type' => 'booking',
                'kode' => $bk->kode_booking,
                'status' => $bk->status_booking instanceof BookingStatus ? $bk->status_booking->label() : (string) $bk->status_booking,
                'color' => $bk->status_booking instanceof BookingStatus ? $bk->status_booking->color() : 'slate',
                'tanggal' => $bk->created_at,
                'url' => route('admin.booking.show', $bk->id_booking),
            ]);
        }

        $recentItems = $combined->sortByDesc('tanggal')->take(5)->values()->all();

        return [
            'is_returning' => ($requestCount + $bookingCount) > 0,
            'request_count' => $requestCount,
            'booking_count' => $bookingCount,
            'recent_items' => $recentItems,
        ];
    }

    /**
     * Koreksi data pelanggan saat status masih menunggu_verifikasi (PRD 7.4 / 8.2).
     */
    public function updateCustomerData(PhotoEditRequest $editRequest, array $data): array
    {
        if ($editRequest->status_edit !== EditStatus::MENUNGGU_VERIFIKASI) {
            return [
                'success' => false,
                'message' => 'Data hanya dapat dikoreksi saat status masih Menunggu Verifikasi.',
            ];
        }

        $normalizedPhone = PhoneNormalizer::normalize($data['nomor_wa']);
        $idBooking = $editRequest->id_booking;
        $idDetail = $editRequest->id_detail;
        $perluDicek = $editRequest->perlu_dicek;

        if (array_key_exists('kode_booking', $data)) {
            $kodeBooking = strtoupper(trim((string) $data['kode_booking']));
            if ($kodeBooking !== '') {
                $booking = Booking::with('details')->where('kode_booking', $kodeBooking)->first();
                if (!$booking) {
                    return [
                        'success' => false,
                        'message' => 'Kode booking tidak ditemukan.',
                    ];
                }
                $idBooking = $booking->id_booking;
                $idDetail = $booking->details->first()?->id_detail;
                $perluDicek = false;
            } else {
                $idBooking = null;
                $idDetail = null;
            }
        }

        $editRequest->update([
            'nama_pelanggan' => $data['nama_pelanggan'],
            'nomor_wa' => $normalizedPhone,
            'catatan' => $data['catatan'] ?? $editRequest->catatan,
            'id_booking' => $idBooking,
            'id_detail' => $idDetail,
            'perlu_dicek' => isset($data['mark_checked']) ? false : $perluDicek,
        ]);

        return [
            'success' => true,
            'message' => 'Data permintaan edit berhasil diperbarui.',
        ];
    }

    /**
     * Terima request: menunggu_verifikasi -> pending (PRD 7.5).
     */
    public function acceptRequest(PhotoEditRequest $editRequest, int $adminId): array
    {
        $currentStatus = $editRequest->status_edit;
        if (!$currentStatus->canTransitionTo(EditStatus::PENDING)) {
            return [
                'success' => false,
                'message' => "Transisi status dari {$currentStatus->label()} ke Antre tidak diizinkan.",
            ];
        }

        DB::transaction(function () use ($editRequest, $adminId, $currentStatus) {
            $editRequest->update([
                'status_edit' => EditStatus::PENDING->value,
                'verified_by' => $adminId,
                'verified_at' => now(),
                'perlu_dicek' => false,
            ]);

            EditStatusLog::create([
                'id_request' => $editRequest->id_request,
                'changed_by' => $adminId,
                'status_lama' => $currentStatus->value,
                'status_baru' => EditStatus::PENDING->value,
                'changed_at' => now(),
            ]);
        });

        return [
            'success' => true,
            'message' => 'Request berhasil diverifikasi dan masuk antrian.',
        ];
    }

    /**
     * Tolak request: menunggu_verifikasi / pending -> ditolak (PRD 7.5).
     */
    public function rejectRequest(PhotoEditRequest $editRequest, int $adminId, string $alasan): array
    {
        $currentStatus = $editRequest->status_edit;
        if (!$currentStatus->canTransitionTo(EditStatus::DITOLAK)) {
            return [
                'success' => false,
                'message' => "Request berstatus {$currentStatus->label()} tidak dapat ditolak.",
            ];
        }

        DB::transaction(function () use ($editRequest, $adminId, $alasan, $currentStatus) {
            $editRequest->update([
                'status_edit' => EditStatus::DITOLAK->value,
                'alasan_penolakan' => trim($alasan),
                'verified_by' => $editRequest->verified_by ?? $adminId,
                'verified_at' => $editRequest->verified_at ?? now(),
            ]);

            EditStatusLog::create([
                'id_request' => $editRequest->id_request,
                'changed_by' => $adminId,
                'status_lama' => $currentStatus->value,
                'status_baru' => EditStatus::DITOLAK->value,
                'changed_at' => now(),
            ]);
        });

        return [
            'success' => true,
            'message' => 'Request edit foto telah ditolak.',
        ];
    }

    /**
     * Ambil request secara atomik: pending -> diproses (PRD 7.5).
     */
    public function claimRequest(int $requestId, int $adminId): array
    {
        return DB::transaction(function () use ($requestId, $adminId) {
            $affected = PhotoEditRequest::where('id_request', $requestId)
                ->where('status_edit', EditStatus::PENDING->value)
                ->update([
                    'status_edit' => EditStatus::DIPROSES->value,
                    'handled_by' => $adminId,
                    'updated_at' => now(),
                ]);

            if ($affected !== 1) {
                return [
                    'success' => false,
                    'message' => 'Request ini sudah diambil oleh admin lain atau statusnya sudah berubah.',
                ];
            }

            EditStatusLog::create([
                'id_request' => $requestId,
                'changed_by' => $adminId,
                'status_lama' => EditStatus::PENDING->value,
                'status_baru' => EditStatus::DIPROSES->value,
                'changed_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Request berhasil diambil dan sedang diproses.',
            ];
        });
    }

    /**
     * Tandai selesai: diproses -> selesai (PRD 7.5).
     * Syarat: minimal 1 foto hasil sudah terunggah (atau link Drive sudah diisi).
     */
    public function completeRequest(PhotoEditRequest $editRequest, int $adminId): array
    {
        $currentStatus = $editRequest->status_edit;
        if (!$currentStatus->canTransitionTo(EditStatus::SELESAI)) {
            return [
                'success' => false,
                'message' => "Transisi dari {$currentStatus->label()} ke Selesai tidak sah.",
            ];
        }

        $uploadedCount = $editRequest->editedPhotos()
            ->whereNotNull('drive_file_id')
            ->where('drive_file_id', '!=', '')
            ->count();

        if ($uploadedCount < 1 && empty($editRequest->drive_folder_url)) {
            return [
                'success' => false,
                'message' => 'Minimal satu foto hasil edit harus sudah diunggah sebelum menandai selesai.',
            ];
        }

        DB::transaction(function () use ($editRequest, $adminId, $currentStatus) {
            $editRequest->update([
                'status_edit' => EditStatus::SELESAI->value,
                'completed_at' => now(),
            ]);

            EditStatusLog::create([
                'id_request' => $editRequest->id_request,
                'changed_by' => $adminId,
                'status_lama' => $currentStatus->value,
                'status_baru' => EditStatus::SELESAI->value,
                'changed_at' => now(),
            ]);
        });

        return [
            'success' => true,
            'message' => 'Request ditandai selesai dan siap dikirim ke WhatsApp pelanggan.',
        ];
    }

    /**
     * Buka kembali request untuk revisi secara atomik: dikirim -> diproses (PRD 7.5).
     */
    public function reopenRequest(int $requestId, int $adminId): array
    {
        return DB::transaction(function () use ($requestId, $adminId) {
            $affected = PhotoEditRequest::where('id_request', $requestId)
                ->where('status_edit', EditStatus::DIKIRIM->value)
                ->update([
                    'status_edit' => EditStatus::DIPROSES->value,
                    'handled_by' => $adminId,
                    'updated_at' => now(),
                ]);

            if ($affected !== 1) {
                return [
                    'success' => false,
                    'message' => 'Request hanya dapat dibuka kembali dari status Terkirim.',
                ];
            }

            EditStatusLog::create([
                'id_request' => $requestId,
                'changed_by' => $adminId,
                'status_lama' => EditStatus::DIKIRIM->value,
                'status_baru' => EditStatus::DIPROSES->value,
                'changed_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Request berhasil dibuka kembali untuk revisi (status kembali ke Diproses).',
            ];
        });
    }

    /**
     * Ambil request berdasarkan kode (publik / admin).
     */
    public function getByCode(string $kode): ?PhotoEditRequest
    {
        return PhotoEditRequest::with([
            'room',
            'frame',
            'items',
            'editedPhotos',
            'statusLogs' => fn ($q) => $q->orderBy('changed_at'),
        ])
            ->where('kode_request', strtoupper(trim($kode)))
            ->first();
    }

    /**
     * Daftar request untuk admin dengan filter lengkap (PRD 7.4 FE).
     */
    public function getAdminRequests(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PhotoEditRequest::with(['room', 'frame', 'handler', 'booking'])
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->where('status_edit', $filters['status']);
        }

        if (!empty($filters['id_room'])) {
            $query->where('id_room', $filters['id_room']);
        }

        if (!empty($filters['sumber'])) {
            $query->where('sumber', $filters['sumber']);
        }

        if (!empty($filters['tanggal_dari'])) {
            $query->whereDate('created_at', '>=', $filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $query->whereDate('created_at', '<=', $filters['tanggal_sampai']);
        }

        if (!empty($filters['q'])) {
            $search = trim($filters['q']);
            $query->where(function ($q) use ($search) {
                $q->where('kode_request', 'like', "%{$search}%")
                    ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                    ->orWhere('nomor_wa', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
