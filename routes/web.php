<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotifyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/spotify/login', [SpotifyController::class, 'redirectToSpotify']);
Route::get('/spotify/callback', [SpotifyController::class, 'handleSpotifyCallback']);
Route::put('/spotify/play', [SpotifyController::class, 'play']);
Route::put('/spotify/pause', [SpotifyController::class, 'pause']);
Route::post('/spotify/next', [SpotifyController::class, 'next']);
Route::get('/spotify/search', [SpotifyController::class, 'search']);

require __DIR__.'/auth.php';
