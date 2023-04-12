<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::name('api.')->group(function () {
    /** Auth */
    Route::post('code', [AuthController::class, 'code'])->name('code');
    Route::post('code/check', [AuthController::class, 'checkCode'])->name('check-code');
    Route::post('auth', [AuthController::class, 'auth'])->name('auth');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
