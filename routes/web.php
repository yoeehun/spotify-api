<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotifyController;

Route::get('/', function () {
    return view('welcome');
});

// Spotify routes (must be logged in)
Route::middleware(['auth'])->group(function () {
    Route::get('/spotify/redirect', [SpotifyController::class, 'redirectToSpotify'])->name('spotify.redirect');
    Route::get('/spotify/callback', [SpotifyController::class, 'handleSpotifyCallback']);
    Route::get('/spotify/profile', [SpotifyController::class, 'profile']);

    Route::put('/spotify/play', [SpotifyController::class, 'play']);
    Route::put('/spotify/pause', [SpotifyController::class, 'pause']);
    Route::post('/spotify/next', [SpotifyController::class, 'next']);
    Route::get('/spotify/search', [SpotifyController::class, 'search']);
});

require __DIR__.'/auth.php';
