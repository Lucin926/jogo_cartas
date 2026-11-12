<?php

use App\Http\Controllers\Admin\DeckManagementController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas do Jogo
|--------------------------------------------------------------------------
*/

Route::get('/', [GameController::class, 'index'])->name('home');
Route::post('/games', [GameController::class, 'store'])->name('game.store');

// Tela Principal da Partida (Mesa / Host / TV)
Route::get('/host/{code}', [GameController::class, 'host'])->name('game.host');
Route::get('/api/games/{code}/host-state', [GameController::class, 'hostState'])->name('game.host.state');
Route::post('/api/games/{code}/start-round', [GameController::class, 'startRound'])->name('game.start.round');
Route::post('/api/games/{code}/force-judging', [GameController::class, 'forceJudging'])->name('game.force.judging');
Route::delete('/api/games/{code}/players/{playerId}', [GameController::class, 'removePlayer'])->name('game.player.remove');

// Tela dos Jogadores (Mobile)
Route::get('/join/{code}', [PlayerController::class, 'showJoinForm'])->name('game.join.form');
Route::post('/join/{code}', [PlayerController::class, 'join'])->name('game.join');
Route::get('/play/{token}', [PlayerController::class, 'play'])->name('player.play');
Route::get('/api/players/{token}/sync', [PlayerController::class, 'syncState'])->name('player.sync');
Route::post('/api/players/{token}/submit', [PlayerController::class, 'submitCards'])->name('player.submit');
Route::post('/api/players/{token}/vote', [PlayerController::class, 'castVote'])->name('player.vote');
Route::post('/api/players/{token}/ready-next', [PlayerController::class, 'readyNextRound'])->name('player.ready.next');

// Painel Administrativo de Baralhos
Route::prefix('admin/decks')->group(function () {
    Route::get('/', [DeckManagementController::class, 'index'])->name('admin.decks.index');
    Route::post('/black-cards', [DeckManagementController::class, 'storeBlackCard'])->name('admin.black-cards.store');
    Route::post('/white-cards', [DeckManagementController::class, 'storeWhiteCard'])->name('admin.white-cards.store');
    Route::delete('/black-cards/{blackCard}', [DeckManagementController::class, 'destroyBlackCard'])->name('admin.black-cards.destroy');
    Route::delete('/white-cards/{whiteCard}', [DeckManagementController::class, 'destroyWhiteCard'])->name('admin.white-cards.destroy');
});
