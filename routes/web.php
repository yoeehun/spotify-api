<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotifyController;
use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

// ... Spotify routes ...



// Spotify routes (must be logged in)
Route::middleware(['auth'])->group(function () {
    Route::get('/spotify/redirect', [SpotifyController::class, 'redirectToSpotify'])->name('spotify.redirect');
    Route::get('/spotify/callback', [SpotifyController::class, 'handleSpotifyCallback']);
    
    // Remove this if no profile method in your SpotifyController
    // Route::get('/spotify/profile', [SpotifyController::class, 'profile']);

    Route::put('/spotify/play', [SpotifyController::class, 'play']);
    Route::put('/spotify/pause', [SpotifyController::class, 'pause']);
    Route::post('/spotify/next', [SpotifyController::class, 'next']);
    Route::get('/spotify/search', [SpotifyController::class, 'search']);
});

require __DIR__.'/auth.php';
