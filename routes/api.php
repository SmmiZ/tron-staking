<?php

use App\Http\Controllers\Api\{AuthController,
    InfoController,
    ReactorController,
    StakeController,
    TransactionController,
    WalletController};
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
    Route::get('leader-code', [AuthController::class, 'checkLeaderCode'])->name('check-leader-code');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('leader-code', [AuthController::class, 'addLeaderCode'])->name('add-leader-code');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        /** INFO */
        Route::get('connect-info', [InfoController::class, 'connectInfo'])->name('connect-info');

        /** STAKES */
        Route::prefix('stakes')->name('stakes.')->group(function () {
            Route::post('', [StakeController::class, 'stake'])->name('stake');
            Route::get('', [StakeController::class, 'show'])->name('show');
            Route::post('unstake', [StakeController::class, 'unstake'])->name('unstake');
            Route::get('available-for-unfreeze', [StakeController::class, 'getAvailableUnfreezeTrxAmount'])->name('available-for-unfreeze');
        });

        /** WALLETS */
        Route::apiResource('wallets', WalletController::class);
        Route::get('wallets/{wallet:id}/check-access', [WalletController::class, 'checkAccess'])->name('wallets.check-access');

        /** REACTORS */
        Route::apiResource('reactors', ReactorController::class)->except(['update']);

        /** TRANSACTIONS */
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('internal', [TransactionController::class, 'internalTxs'])->name('internal');
//            Route::get('tron', [TransactionController::class, 'tronTxs'])->name('tron');
        });
    });
});
