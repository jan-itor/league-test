<?php

use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LeagueController::class, 'index'])->name('league.home');
Route::get('/play-all', [LeagueController::class, 'playAll'])->name('league.play-all');
Route::get('/play-next', [LeagueController::class, 'playNext'])->name('league.play-next');
Route::get('/reset', [LeagueController::class, 'reset'])->name('league.reset');
Route::get('/stages', [LeagueController::class, 'stages'])->name('league.stages');
