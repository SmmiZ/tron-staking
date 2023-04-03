<?php

use App\Jobs\FreezeTRX;
use App\Jobs\GetReward;
use App\Jobs\VoteSR;
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

//    dd(FreezeTRX::dispatch());
//    dd(VoteSR::dispatch());
//    dd(GetReward::dispatch());

    return view('welcome');
});
