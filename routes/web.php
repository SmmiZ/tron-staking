<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\{ConsumerController, HomeController, UserController};
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
    //Авторизация
    Route::group([/*'middleware' => 'throttle:staff_login'*/], function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    });

    //Действия
    Route::group(['middleware' => ['auth:staff']], function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::resource('users', UserController::class)->only(['index', 'show']);
        Route::resource('consumers', ConsumerController::class);
    });
});
