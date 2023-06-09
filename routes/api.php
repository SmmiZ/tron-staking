<?php

use App\Http\Controllers\Api\{AccountController,
    AuthController,
    InfoController,
    ConsumerController,
    MerchantWalletController,
    ReactorController,
    StakeController,
    StructureController,
    TransactionController,
    WalletController,
    WithdrawalController};
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

        /** ACCOUNT */
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('', [AccountController::class, 'show'])->name('show');
            Route::post('', [AccountController::class, 'update'])->name('update');
        });

        /** INFO */
        Route::get('connect-info', [InfoController::class, 'connectInfo'])->name('connect-info');

        /** STAKES */
        Route::apiResource('stakes', StakeController::class)->except(['update']);

        /** WALLETS */
        Route::apiResource('wallets', WalletController::class)->except(['update']);
        Route::get('wallets/{wallet:id}/check-access', [WalletController::class, 'checkAccess'])->name('wallets.check-access');
        /** MERCHANT WALLETS */
        Route::get('merchant-wallet', [MerchantWalletController::class, 'getTempAddress'])->name('merchant-wallet');

        /** REACTORS */
        Route::apiResource('reactors', ReactorController::class)->except(['update']);

        /** CONSUMERS */
        Route::apiResource('consumers', ConsumerController::class)->except(['update']);
        Route::post('consumers/pay', [ConsumerController::class, 'payConsumer'])->name('consumers.pay');

        /** TRANSACTIONS */
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('internal', [TransactionController::class, 'internalTxs'])->name('internal');
            Route::get('tron', [TransactionController::class, 'tronTxs'])->name('tron');
        });

        /** STRUCTURE */
        Route::prefix('structure')->name('structure.')->group(function () {
            Route::post('invite', [StructureController::class, 'invite'])->name('invite');
            Route::get('levels', [StructureController::class, 'levels'])->name('levels');
            Route::get('partners', [StructureController::class, 'partners'])->name('partners');
        });

        /** WITHDRAWALS */
        Route::apiResource('withdrawals', WithdrawalController::class)->except(['update']);
    });
});
