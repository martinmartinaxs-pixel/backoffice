<?php

use App\Http\Controllers\Backoffice\GameController;
use App\Http\Controllers\Rent\LoginLineController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\UserController;
use App\Http\Controllers\Main\MainController;
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
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::post('login', [DashboardController::class, 'login'])->name('login');
Route::post('login/token/{token}', [LoginLineController::class, 'loginByToken'])->name('login.token');
Route::get('logout', [UserController::class, 'logout'])->name('logout');

Route::middleware(['login'])->namespace('Backoffice')->prefix('backoffice')->name('admin.')->group(function () {
    Route::get('', [DashboardController::class, 'index'])->name('index');

    Route::middleware([])->prefix('user')->name('user.')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('index');
    });

    Route::middleware([])->prefix('game')->name('game.')->group(function () {
        Route::get('/{game}', [GameController::class, 'index'])->name('index');
        Route::post('savesetting', [GameController::class, 'saveSetting'])->name('save');
        Route::post('savecredit', [GameController::class, 'saveCredit'])->name('credit');
    });
});



