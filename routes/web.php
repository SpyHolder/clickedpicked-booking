<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminFrameController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PhotoEditController;

// ==================== PUBLIC ROUTES (Customer) ====================

// Home page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Studio (PRD 8.1: /studio, /studio/{room})
Route::get('/studio', [RoomController::class, 'index'])->name('studio.index');
Route::get('/studio/{room}', [RoomController::class, 'show'])->name('studio.show');

// Booking Wizard (PRD 8.1)
Route::get('/booking', [BookingController::class, 'showWizard'])->name('booking.wizard');
Route::post('/booking/step-1', [BookingController::class, 'processStep1'])->name('booking.step1');
Route::get('/booking/slots', [BookingController::class, 'getAvailableSlots'])->name('booking.slots');
Route::get('/booking/step-2', [BookingController::class, 'showStep2'])->name('booking.step2');
Route::post('/booking/step-2', [BookingController::class, 'processStep2'])->name('booking.step2.process');
Route::get('/booking/{kode}/payment', [BookingController::class, 'showPayment'])->name('booking.payment');
Route::post('/booking/{kode}/payment', [PaymentController::class, 'uploadProof'])->name('booking.payment.upload');
Route::get('/booking/{kode}/done', [BookingController::class, 'showConfirmation'])->name('booking.done');
Route::get('/booking/{kode}', [BookingController::class, 'showStatus'])->name('booking.status');

// Cek status (PRD 8.1: /cek-status → satu kolom kode, mengarah ke status booking atau edit)
Route::get('/check-status', [BookingController::class, 'showCheckStatus'])->name('check-status');
Route::post('/check-status', [BookingController::class, 'processCheckStatus'])->name('check-status.process');

// Payment re-upload bukti (Keputusan #2: payment lama → failed, buat baru)
Route::post('/booking/{kode}/payment/reupload', [PaymentController::class, 'reuploadProof'])->name('booking.payment.reupload');

// Edit foto dari QR (PRD 8.1)
Route::get('/edit-foto/sukses/{kode}', [PhotoEditController::class, 'showSuccess'])->name('edit-foto.success');
Route::get('/edit-foto/status/{kode}', [PhotoEditController::class, 'showPublicStatus'])->name('edit-foto.status');
Route::get('/edit-foto/{room}', [PhotoEditController::class, 'showPublicForm'])->whereNumber('room')->name('edit-foto.form');
Route::post('/edit-foto/{room}', [PhotoEditController::class, 'storePublicRequest'])->whereNumber('room')->name('edit-foto.store');

// ==================== ADMIN AUTH ====================

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.process');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// ==================== ADMIN ROUTES (Protected) ====================

Route::middleware(['auth:admin', 'admin.active'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Studio management — tanpa hapus (PRD 8.2)
    Route::get('/studio', [RoomController::class, 'adminIndex'])->name('studio.index');
    Route::get('/studio/create', [RoomController::class, 'create'])->name('studio.create');
    Route::post('/studio', [RoomController::class, 'store'])->name('studio.store');
    Route::get('/studio/{room}', [RoomController::class, 'edit'])->name('studio.edit');
    Route::put('/studio/{room}', [RoomController::class, 'update'])->name('studio.update');
    Route::patch('/studio/{room}/status', [RoomController::class, 'toggleStatus'])->name('studio.status');

    // Paket per studio — hanya toggle is_active (PRD 8.2)
    Route::get('/studio/{room}/packages', [RoomController::class, 'showPackages'])->name('studio.packages');
    Route::post('/studio/{room}/packages', [RoomController::class, 'storePackage'])->name('studio.packages.store');
    Route::put('/studio/{room}/packages/{package}', [RoomController::class, 'updatePackage'])->name('studio.packages.update');

    // Gambar studio (PRD 8.2)
    Route::post('/studio/{room}/images', [RoomController::class, 'uploadImage'])->name('studio.images.store');
    Route::delete('/studio/{room}/images/{image}', [RoomController::class, 'deleteImage'])->name('studio.images.destroy');

    // QR studio (PRD 8.2)
    Route::get('/studio/{room}/qr', [RoomController::class, 'showQr'])->name('studio.qr');

    // Katalog Frame (untuk Photo Studio Room 2 & Room 3 Batu Lima)
    Route::get('/frames', [AdminFrameController::class, 'index'])->name('frames.index');
    Route::post('/frames', [AdminFrameController::class, 'store'])->name('frames.store');
    Route::put('/frames/{frame}', [AdminFrameController::class, 'update'])->whereNumber('frame')->name('frames.update');
    Route::post('/frames/{frame}/toggle', [AdminFrameController::class, 'toggle'])->whereNumber('frame')->name('frames.toggle');
    Route::delete('/frames/{frame}', [AdminFrameController::class, 'destroy'])->whereNumber('frame')->name('frames.destroy');

    // Booking management (PRD 8.2)
    Route::get('/booking', [BookingController::class, 'adminIndex'])->name('booking.index');
    Route::get('/booking/{booking}', [BookingController::class, 'adminShow'])->name('booking.show');
    Route::patch('/booking/{booking}/status', [BookingController::class, 'updateStatus'])->name('booking.status');

    // Payment verification (PRD 8.2)
    Route::get('/payment/{payment}/proof', [PaymentController::class, 'showProof'])->name('payment.proof');
    Route::patch('/payment/{payment}/verify', [PaymentController::class, 'verify'])->name('payment.verify');

    // Edit foto admin routes (PRD 8.2 — BE-3 & BE-4)
    Route::get('/edit-foto', [PhotoEditController::class, 'adminIndex'])->name('edit-foto.index');
    Route::get('/edit-foto/create', [PhotoEditController::class, 'adminCreate'])->name('edit-foto.create');
    Route::get('/edit-foto/lookup-booking', [PhotoEditController::class, 'lookupBooking'])->name('edit-foto.lookup-booking');
    Route::post('/edit-foto', [PhotoEditController::class, 'adminStore'])->name('edit-foto.store');
    Route::get('/edit-foto/{editRequest}', [PhotoEditController::class, 'adminShow'])->whereNumber('editRequest')->name('edit-foto.show');
    Route::patch('/edit-foto/{editRequest}', [PhotoEditController::class, 'adminUpdateData'])->whereNumber('editRequest')->name('edit-foto.update');
    Route::post('/edit-foto/{editRequest}/accept', [PhotoEditController::class, 'accept'])->whereNumber('editRequest')->name('edit-foto.accept');
    Route::post('/edit-foto/{editRequest}/reject', [PhotoEditController::class, 'reject'])->whereNumber('editRequest')->name('edit-foto.reject');
    Route::post('/edit-foto/{editRequest}/claim', [PhotoEditController::class, 'claim'])->whereNumber('editRequest')->name('edit-foto.claim');
    Route::post('/edit-foto/{editRequest}/upload', [PhotoEditController::class, 'uploadPhotos'])->whereNumber('editRequest')->name('edit-foto.upload');
    Route::delete('/edit-foto/{editRequest}/photos/{photo}', [PhotoEditController::class, 'deletePhoto'])->whereNumber(['editRequest', 'photo'])->name('edit-foto.photo.destroy');
    Route::post('/edit-foto/{editRequest}/drive-link', [PhotoEditController::class, 'updateDriveLink'])->whereNumber('editRequest')->name('edit-foto.drive-link');
    Route::post('/edit-foto/{editRequest}/complete', [PhotoEditController::class, 'complete'])->whereNumber('editRequest')->name('edit-foto.complete');
    Route::post('/edit-foto/{editRequest}/send-wa', [PhotoEditController::class, 'sendWhatsApp'])->whereNumber('editRequest')->name('edit-foto.send-wa');
    Route::post('/edit-foto/{editRequest}/reopen', [PhotoEditController::class, 'reopen'])->whereNumber('editRequest')->name('edit-foto.reopen');
});
