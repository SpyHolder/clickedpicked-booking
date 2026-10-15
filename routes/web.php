<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

// Home page
Route::get('/', function () {
    return view('welcome');
});

// ==================== PUBLIC ROUTES (Customer) ====================

// Rooms - public access untuk customer
Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/rooms/{roomId}/packages', [RoomController::class, 'getPackages'])->name('rooms.packages');

// Booking Wizard - 4 step process
Route::get('/booking', [BookingController::class, 'showWizard'])->name('booking.wizard');
Route::post('/booking/step1', [BookingController::class, 'processStep1'])->name('booking.step1');
Route::get('/booking/step2', [BookingController::class, 'showStep2'])->name('booking.step2');
Route::post('/booking/step2', [BookingController::class, 'processStep2'])->name('booking.step2.process');
Route::get('/booking/payment/{kode}', [BookingController::class, 'showPayment'])->name('booking.payment');
Route::post('/booking/{kode}/payment', [PaymentController::class, 'uploadProofForBooking'])->name('payment.upload');
Route::get('/booking/confirmation/{kode}', [BookingController::class, 'showConfirmation'])->name('booking.confirmation');
Route::get('/booking/status/{kode}', [BookingController::class, 'showStatus'])->name('booking.status');

// Booking AJAX endpoints
Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.checkAvailability');
Route::post('/booking/available-slots', [BookingController::class, 'getAvailableSlots'])->name('booking.availableSlots');

// Payment - re-upload proof (if rejected)
Route::post('/booking/{kode}/payment/reupload', [PaymentController::class, 'reuploadProof'])->name('payment.reupload');

// ==================== ADMIN ROUTES (Protected with auth:admin) ====================

Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Room management - CRUD pages
    Route::get('/rooms', [RoomController::class, 'adminIndex'])->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{id}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    
    // Package management
    Route::get('/rooms/{roomId}/packages', [RoomController::class, 'showPackages'])->name('rooms.packages');
    Route::post('/rooms/{roomId}/packages', [RoomController::class, 'storePackage'])->name('rooms.storePackage');
    Route::put('/packages/{packageId}', [RoomController::class, 'updatePackage'])->name('packages.update');
    Route::delete('/packages/{packageId}', [RoomController::class, 'destroyPackage'])->name('packages.destroy');
    
    // Booking management
    Route::get('/bookings', [BookingController::class, 'adminIndex'])->name('bookings.index');
    Route::get('/bookings/{kode}', [BookingController::class, 'adminShow'])->name('bookings.show');
    Route::post('/bookings/{kode}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{kode}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{kode}/complete', [BookingController::class, 'complete'])->name('bookings.complete');
    
    // Payment verification
    Route::get('/payments/pending', [PaymentController::class, 'pendingVerifications'])->name('payments.pending');
    Route::get('/payments/{id}/verify', [PaymentController::class, 'showVerification'])->name('payments.verifyForm');
    Route::post('/payments/{id}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{id}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/payments/{id}/proof', [PaymentController::class, 'showProof'])->name('payments.proof');
    Route::post('/edit-requests/{requestId}/payments', [PaymentController::class, 'uploadProofForEditRequest'])->name('editRequests.payments');
});
