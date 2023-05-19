<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\{ConsumerController,
    ExecutorController,
    HomeController,
    InternalTxController,
    OrderController,
    TronTxController,
    UserController,
    WithdrawalController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group([
    'prefix' => 'staff-lobby',
], function () {
    /** Авторизация */
    Route::group([/*'middleware' => 'throttle:staff_login'*/], function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    });

    Route::group(['middleware' => ['auth:staff']], function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::resource('consumers', ConsumerController::class);
        Route::resource('users', UserController::class)->only(['index', 'show']);

        /** Транзакции */
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::resource('tron', TronTxController::class)->only(['index', 'show']);
            Route::resource('internal', InternalTxController::class)->only(['index', 'show']);
        });

        /** Заказы */
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
        Route::resource('orders.executors', ExecutorController::class)->only(['index']);

        /** Статистика */
        Route::get('resource-consumption', [HomeController::class, 'resourceConsumption'])->name('resource-consumption');

        /** Заявки на вывод */
        Route::resource('withdrawals', WithdrawalController::class)->only(['index', 'show']);
        Route::group(['prefix' => 'withdrawals', 'as' => 'withdrawals.'], function () {
            Route::post('{withdrawal}/accept', [WithdrawalController::class, 'accept'])->name('accept');
            Route::post('{withdrawal}/decline', [WithdrawalController::class, 'decline'])->name('decline');
        });
    });
});
