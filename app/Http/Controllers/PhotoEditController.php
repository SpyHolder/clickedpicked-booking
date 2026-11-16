<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Http\Requests\AdminEditFotoRequest;
use App\Http\Requests\PublicEditFotoRequest;
use App\Models\Booking;
use App\Models\Frame;
use App\Models\PhotoEditRequest;
use App\Models\Room;
use App\Models\Setting;
use App\Services\PhotoEditService;
use App\Utils\PhoneNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PhotoEditController extends Controller
{
    public function __construct(
        private PhotoEditService $photoEditService,
    ) {}

    // ==================== PUBLIC (Customer QR Flow) ====================

    /**
     * Form edit foto dari QR studio (PRD 8.1: GET /edit-foto/{room}).
     */
    public function showPublicForm(int $room): View
    {
        $roomModel = Room::find($room);

        if (!$roomModel) {
            abort(404, 'Studio tidak ditemukan');
        }

        $frames = Frame::where('is_active', true)
            ->orderBy('jumlah_slot')
            ->orderBy('kode_frame')
            ->get();

        return view('edit-foto.form', [
            'room' => $roomModel,
            'frames' => $frames,
            'isInactive' => $roomModel->status !== RoomStatus::AKTIF,
        ]);
    }

    /**
     * Simpan request edit foto dari QR studio (PRD 8.1: POST /edit-foto/{room}).
     */
    public function storePublicRequest(int $room, PublicEditFotoRequest $request): RedirectResponse
    {
        $roomModel = Room::find($room);

        if (!$roomModel) {
            abort(404, 'Studio tidak ditemukan');
        }

        if ($roomModel->status !== RoomStatus::AKTIF) {
            return back()->with('error', 'Formulir untuk studio ini sedang tidak tersedia. Silakan hubungi admin.');
        }

        $editRequest = $this->photoEditService->createFromQr($roomModel, $request->validated());

        return redirect()
            ->route('edit-foto.success', $editRequest->kode_request)
            ->with('success', 'Permintaan edit foto berhasil dikirim!');
    }

    /**
     * Halaman sukses setelah kirim form edit foto (PRD FE 6.1).
     */
    public function showSuccess(string $kode): View
    {
        $editRequest = $this->photoEditService->getByCode($kode);

        if (!$editRequest) {
            abort(404, 'Permintaan edit foto tidak ditemukan');
        }

        return view('edit-foto.success', [
            'editRequest' => $editRequest,
        ]);
    }

    /**
     * Halaman status edit foto publik (PRD 8.1: GET /edit-foto/status/{kode}).
     * Nomor WA disamarkan dan tanpa link Google Drive.
     */
    public function showPublicStatus(string $kode): View
    {
        $editRequest = $this->photoEditService->getByCode($kode);

        if (!$editRequest) {
            abort(404, 'Permintaan edit foto tidak ditemukan');
        }

        return view('edit-foto.status', [
            'editRequest' => $editRequest,
            'maskedPhone' => $this->photoEditService->maskPhoneNumber($editRequest->nomor_wa),
        ]);
    }

    // ==================== ADMIN ====================

    /**
     * Daftar antrian request edit foto (PRD 8.2: GET /admin/edit-foto).
     */
    public function adminIndex(Request $request): View
    {
        $filters = $request->only(['status', 'id_room', 'sumber', 'tanggal_dari', 'tanggal_sampai', 'q']);

        return view('admin.edit-foto.index', [
            'requests' => $this->photoEditService->getAdminRequests($filters),
            'rooms' => Room::orderBy('nama_room')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * Form input request manual oleh admin (PRD 8.2: GET /admin/edit-foto/create).
     */
    public function adminCreate(): View
    {
        return view('admin.edit-foto.create', [
            'rooms' => Room::where('status', RoomStatus::AKTIF)->orderBy('nama_room')->get(),
            'frames' => Frame::where('is_active', true)->orderBy('jumlah_slot')->orderBy('kode_frame')->get(),
        ]);
    }

    /**
     * Lookup booking berdasarkan kode untuk auto-fill form manual admin (PRD FE 7.4).
     */
    public function lookupBooking(Request $request): JsonResponse
    {
        $kode = strtoupper(trim((string) $request->query('kode', '')));
        if ($kode === '') {
            return response()->json(['found' => false]);
        }

        $booking = Booking::with('details')->where('kode_booking', $kode)->first();
        if (!$booking) {
            return response()->json(['found' => false]);
        }

        $firstDetail = $booking->details->first();

        return response()->json([
            'found' => true,
            'nama_pelanggan' => $booking->nama_pelanggan,
            'nomor_wa' => $booking->no_hp_pelanggan,
            'id_room' => $firstDetail?->id_room,
        ]);
    }

    /**
     * Simpan request edit foto manual dari admin (PRD 8.2: POST /admin/edit-foto).
     */
    public function adminStore(AdminEditFotoRequest $request): RedirectResponse
    {
        $adminId = Auth::guard('admin')->id();
        $editRequest = $this->photoEditService->createFromAdmin($adminId, $request->validated());

        return redirect()
            ->route('admin.edit-foto.show', $editRequest->id_request)
            ->with('success', "Request edit foto {$editRequest->kode_request} berhasil dibuat.");
    }

    /**
     * Detail request edit foto untuk admin (PRD 8.2: GET /admin/edit-foto/{editRequest}).
     */
    public function adminShow(int $editRequest): View
    {
        $model = PhotoEditRequest::with([
            'room',
            'frame',
            'booking',
            'detail.package',
            'items',
            'editedPhotos.item',
            'statusLogs' => fn ($q) => $q->with('admin')->orderByDesc('changed_at'),
            'deliveries' => fn ($q) => $q->with('admin')->orderByDesc('created_at'),
            'creator',
            'verifier',
            'handler',
        ])->find($editRequest);

        if (!$model) {
            abort(404, 'Request edit foto tidak ditemukan');
        }

        $returningCustomer = $this->photoEditService->getReturningCustomerInfo(
            $model->nomor_wa,
            $model->id_request,
            $model->id_booking
        );

        $waTemplate = Setting::getValue(
            'wa_template_edit_delivery',
            'Halo {nama}, hasil edit foto & frame Anda ({kode_request}) sudah selesai. Silakan cek & unduh di: {link_drive}'
        );

        $downloadLink = $model->drive_folder_url ?: route('edit-foto.status', $model->kode_request);

        $defaultWaMessage = str_replace(
            ['{nama}', '{kode_request}', '{link_drive}'],
            [$model->nama_pelanggan, $model->kode_request, $downloadLink],
            (string) $waTemplate
        );

        return view('admin.edit-foto.show', [
            'editRequest' => $model,
            'returningCustomer' => $returningCustomer,
            'defaultWaMessage' => $defaultWaMessage,
        ]);
    }

    /**
     * Koreksi data pelanggan saat status masih menunggu_verifikasi (PRD 8.2).
     */
    public function adminUpdateData(int $editRequest, Request $request): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'nomor_wa' => ['required', 'string', function ($attr, $val, $fail) {
                if (!PhoneNormalizer::isValid($val)) {
                    $fail('Format nomor WhatsApp tidak valid.');
                }
            }],
            'kode_booking' => 'nullable|string|max:50',
            'catatan' => 'nullable|string|max:1000',
            'mark_checked' => 'nullable|boolean',
        ], [
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'nomor_wa.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $result = $this->photoEditService->updateCustomerData($model, $validated);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Terima request: menunggu_verifikasi -> pending (PRD 7.5).
     */
    public function accept(int $editRequest): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $result = $this->photoEditService->acceptRequest($model, Auth::guard('admin')->id());
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Tolak request: menunggu_verifikasi / pending -> ditolak (PRD 7.5).
     */
    public function reject(int $editRequest, Request $request): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $result = $this->photoEditService->rejectRequest(
            $model,
            Auth::guard('admin')->id(),
            $validated['alasan_penolakan']
        );

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Ambil request secara atomik: pending -> diproses (PRD 7.5).
     */
    public function claim(int $editRequest): RedirectResponse
    {
        $result = $this->photoEditService->claimRequest($editRequest, Auth::guard('admin')->id());
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Upload file foto hasil edit (Foto + Frame / Custom Frame) oleh Admin.
     */
    public function uploadPhotos(int $editRequest, Request $request): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $validated = $request->validate([
            'photos' => 'required|array|min:1|max:20',
            'photos.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:15360',
            'kategori_hasil' => 'required|string|in:foto_frame,custom_frame',
            'id_item' => 'nullable|integer|exists:edit_request_items,id_item',
        ], [
            'photos.required' => 'Pilih minimal 1 file gambar hasil edit.',
            'photos.*.mimes' => 'Format file harus JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran file maksimal 15 MB per foto.',
        ]);

        $result = $this->photoEditService->uploadEditedPhotos(
            $model,
            $request->file('photos', []),
            $validated['kategori_hasil'],
            $validated['id_item'] ?? null
        );

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Hapus file foto hasil edit oleh Admin.
     */
    public function deletePhoto(int $editRequest, int $photo): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $result = $this->photoEditService->deleteEditedPhoto($model, $photo);
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Perbarui tautan folder Google Drive secara manual.
     */
    public function updateDriveLink(int $editRequest, Request $request): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $validated = $request->validate([
            'drive_folder_url' => 'nullable|url|max:1000',
        ], [
            'drive_folder_url.url' => 'Format URL Google Drive tidak valid.',
        ]);

        $result = $this->photoEditService->updateDriveLink($model, $validated['drive_folder_url'] ?? null);
        return back()->with('success', $result['message']);
    }

    /**
     * Tandai selesai: diproses -> selesai (PRD 7.5).
     */
    public function complete(int $editRequest): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $result = $this->photoEditService->completeRequest($model, Auth::guard('admin')->id());
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Catat pengiriman WhatsApp & ubah status menjadi Dikirim.
     */
    public function sendWhatsApp(int $editRequest, Request $request): RedirectResponse
    {
        $model = PhotoEditRequest::find($editRequest);
        if (!$model) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $validated = $request->validate([
            'pesan' => 'required|string|max:2000',
        ], [
            'pesan.required' => 'Pesan WhatsApp tidak boleh kosong.',
        ]);

        $result = $this->photoEditService->sendWhatsAppDelivery(
            $model,
            Auth::guard('admin')->id(),
            $validated['pesan']
        );

        return back()
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('wa_redirect_url', $result['wa_url'] ?? null);
    }

    /**
     * Buka kembali request dari dikirim -> diproses (PRD 7.5).
     */
    public function reopen(int $editRequest): RedirectResponse
    {
        $result = $this->photoEditService->reopenRequest($editRequest, Auth::guard('admin')->id());
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
