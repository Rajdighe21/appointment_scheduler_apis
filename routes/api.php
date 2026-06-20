<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;







Route::apiResource('doctors', DoctorController::class);

Route::get('appointments/slots', [AppointmentController::class, 'getSlots']);
Route::post('appointments/book', [AppointmentController::class, 'bookSlot']);
Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancelSlot']);
