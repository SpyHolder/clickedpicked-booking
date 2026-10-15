<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;

// Public routes - tidak butuh auth
Route::prefix('v1')->group(function(){
    // Rooms - public access untuk customer
    Route::get('/rooms',[RoomController::class,'index']);
    Route::get('/rooms/{id}',[RoomController::class,'show']);
    Route::get('/rooms/{roomId}/packages',[RoomController::class,'getPackages']);
    
    // Booking - public access untuk customer
    Route::post('/bookings',[BookingController::class,'store']);
    Route::get('/bookings/{code}',[BookingController::class,'show']);
    Route::post('/bookings/check-availability',[BookingController::class,'checkAvailability']);
    
    // Payment - public untuk customer upload bukti
    Route::post('/bookings/{bookingCode}/payments',[PaymentController::class,'createForBooking']);
    Route::post('/payments/{paymentId}/upload-proof',[PaymentController::class,'uploadProof']);
});

// Admin routes - butuh auth admin
Route::prefix('v1/admin')->middleware(['auth:admin'])->group(function(){
    // Room management
    Route::post('/rooms',[RoomController::class,'store']);
    Route::put('/rooms/{id}',[RoomController::class,'update']);
    Route::delete('/rooms/{id}',[RoomController::class,'destroy']);
    
    // Package management
    Route::post('/rooms/{roomId}/packages',[RoomController::class,'storePackage']);
    Route::put('/packages/{packageId}',[RoomController::class,'updatePackage']);
    Route::delete('/packages/{packageId}',[RoomController::class,'destroyPackage']);
    
    // Booking management
    Route::get('/bookings',[BookingController::class,'index']);
    Route::post('/bookings/{code}/confirm',[BookingController::class,'confirm']);
    Route::post('/bookings/{code}/cancel',[BookingController::class,'cancel']);
    Route::post('/bookings/{code}/complete',[BookingController::class,'complete']);
    
    // Payment verification
    Route::post('/payments/{paymentId}/verify',[PaymentController::class,'verify']);
    Route::post('/payments/{paymentId}/reject',[PaymentController::class,'reject']);
    Route::post('/edit-requests/{requestId}/payments',[PaymentController::class,'createForEditRequest']);
});