<?php

use App\Http\Controllers\Api\{AuthController, InfoController, StakeController, WalletController};
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

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        /** INFO */
        Route::get('connect-info', [InfoController::class, 'connectInfo'])->name('connect-info');

        Route::apiResource('stakes', StakeController::class)->except(['update']);
        Route::apiResource('wallets', WalletController::class);
        Route::get('wallets/{wallet:id}/check-access', [WalletController::class, 'checkAccess'])->name('wallets.check-access');
    });
});
