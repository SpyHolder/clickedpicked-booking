<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;

// Home page
Route::get('/', function () {
    return view('welcome');
});

// ==================== PUBLIC ROUTES (Customer) ====================

// Rooms - public access untuk customer
Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/rooms/{roomId}/packages', [RoomController::class, 'getPackages'])->name('rooms.packages');

// Booking - public access untuk customer
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{code}', [BookingController::class, 'show'])->name('bookings.show');
Route::post('/bookings/check-availability', [BookingController::class, 'checkAvailability'])->name('bookings.checkAvailability');

// Payment - public untuk customer upload bukti
Route::post('/bookings/{bookingCode}/payments', [PaymentController::class, 'createForBooking'])->name('payments.createForBooking');
Route::post('/payments/{paymentId}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payments.uploadProof');

// ==================== ADMIN ROUTES (Protected with auth:admin) ====================

Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Room management
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    
    // Package management
    Route::post('/rooms/{roomId}/packages', [RoomController::class, 'storePackage'])->name('rooms.storePackage');
    Route::put('/packages/{packageId}', [RoomController::class, 'updatePackage'])->name('packages.update');
    Route::delete('/packages/{packageId}', [RoomController::class, 'destroyPackage'])->name('packages.destroy');
    
    // Booking management
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{code}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{code}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{code}/complete', [BookingController::class, 'complete'])->name('bookings.complete');
    
    // Payment verification
    Route::post('/payments/{paymentId}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{paymentId}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::post('/edit-requests/{requestId}/payments', [PaymentController::class, 'createForEditRequest'])->name('editRequests.payments');
});
