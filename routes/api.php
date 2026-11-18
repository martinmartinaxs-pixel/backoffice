<?php

use App\Http\Controllers\Api\LineApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Game\GameApiController;
use App\Http\Controllers\Api\ApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/line/hook', [LineApiController::class, 'lineWebhook'])->name('hook');

Route::middleware([])->prefix('callback')->name('amb.')->group(function () {
    Route::middleware([])->prefix('PGSOFT2')->name('pg.')->group(function () {
        Route::post('verifySession', [GameApiController::class, 'verifySession'])->name('verifySession');
        Route::post('checkBalance', [GameApiController::class, 'getBalance2'])->name('balance');
        Route::post('settleBets', [GameApiController::class, 'settleBets'])->name('bet');
    });
});

Route::middleware(['cors'])->group(function () {
    Route::post('/auth/session/v2/verifyOperatorPlayerSession', [ApiController::class, 'verifyOperatorPlayerSession'])->name('verify');
    Route::post('/game-proxy/v2/GameName/Get', [ApiController::class, 'GameName'])->name('gamename');
    Route::post('/game-proxy/v2/Resources/GetByResourcesTypeIds', [ApiController::class, 'GetByResourcesTypeIds'])->name('res');
    Route::post('/auth/session/v2/verifySession', [ApiController::class, 'verifySession'])->name('verify2');
    Route::post('/game-proxy/v2/GameRule/Get', [ApiController::class, 'GameRule'])->name('rule');
    Route::post('/game-proxy/v2/BetHistory/Get', [ApiController::class, 'BetHistory'])->name('history');
    Route::post('/game-proxy/v2/GameWallet/Get', [ApiController::class, 'GameWallet'])->name('wallet');

    Route::post('/lobby/gameInfo/v1/getGameDetails', [ApiController::class, 'getGameDetail'])->name('detail');
    Route::post('/lobby/wallet/v1/get', [ApiController::class, 'getGameWallet'])->name('wallet');
    Route::post('/lobby/gameInfo/v1/getAllGames', [ApiController::class, 'getAllGames'])->name('games');
    Route::post('/lobby/initialize/v1/get', [ApiController::class, 'initialize'])->name('initialize');
    Route::post('/lobby/gameInfo/v1/getContentLastUpdated', [ApiController::class, 'getContentLastUpdated'])->name('updated');
    Route::post('/game-proxy/v2/GameUI/Get', [ApiController::class, 'GameUI'])->name('GameUI');

    Route::post('/{game_name}/v2/Spin', [ApiController::class, 'GameSpin'])->name('spin');
    Route::post('/{game_name}/v2/spin', [ApiController::class, 'GameSpin'])->name('spin2');
    Route::post('/{game_name}/v2/Spin/Bonus', [ApiController::class, 'GameBonus'])->name('bonus');
    Route::post('/{game_name}/v2/GameInfo/Get', [ApiController::class, 'GameInfo'])->name('gameinfo');
    Route::post('/{game_name}/v2/GameInfo/Update', [ApiController::class, 'Update'])->name('update');
});

Route::get('getsetting', [GameApiController::class, 'getSetting'])->name('setting');
Route::get('getbalance', [GameApiController::class, 'getBalance'])->name('balance');
Route::get('spin', [GameApiController::class, 'spinData'])->name('spin');
