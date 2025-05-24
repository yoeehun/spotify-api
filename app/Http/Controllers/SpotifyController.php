<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\SpotifyToken;

class SpotifyController extends Controller
{
    // Redirect to Spotify for auth (Free or Premium)
    public function redirectToSpotify()
    {
        $query = http_build_query([
            'client_id' => config('services.spotify.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('services.spotify.redirect'),
            'scope' => 'user-read-email user-read-private', // Minimal scopes
        ]);

        return redirect("https://accounts.spotify.com/authorize?$query");
    }

    // Callback: store tokens in DB
    public function handleSpotifyCallback(Request $request)
    {
        $code = $request->query('code');

        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.spotify.redirect'),
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            SpotifyToken::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($data['expires_in']),
                ]
            );

            return redirect('/dashboard')->with('status', 'Spotify connected!');
        }

        return redirect('/dashboard')->with('error', 'Spotify authentication failed.');
    }

    // Refresh token if expired
    private function refreshAccessToken(SpotifyToken $token)
    {
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            $token->access_token = $data['access_token'];
            $token->expires_at = now()->addSeconds($data['expires_in'] ?? 3600);
            if (isset($data['refresh_token'])) {
                $token->refresh_token = $data['refresh_token'];
            }
            $token->save();
            return true;
        }

        return false;
    }

    // Generic Spotify API request
    private function spotifyRequest($method, $endpoint, $body = [])
    {
        $user = Auth::user();
        $token = SpotifyToken::where('user_id', $user->id)->first();

        if (!$token) {
            return response()->json(['error' => 'Spotify not connected.'], 401);
        }

        if (now()->greaterThanOrEqualTo($token->expires_at)) {
            if (!$this->refreshAccessToken($token)) {
                return response()->json(['error' => 'Failed to refresh token.'], 401);
            }
        }

        $url = "https://api.spotify.com/v1/{$endpoint}";
        $response = Http::withToken($token->access_token)->$method($url, $body);

        // Retry once if unauthorized
        if ($response->status() === 401 && $this->refreshAccessToken($token)) {
            $response = Http::withToken($token->access_token)->$method($url, $body);
        }

        if ($response->failed()) {
            return response()->json(['error' => 'Spotify API error', 'details' => $response->json()], $response->status());
        }

        return response()->json($response->json());
    }

    // Get logged-in user's Spotify profile
    public function profile()
    {
        return $this->spotifyRequest('get', 'me');
    }

    // Check if user is Premium before allowing playback
    private function isPremium()
    {
        $profileResponse = $this->spotifyRequest('get', 'me');

        $data = $profileResponse->getData(true);
        return isset($data['product']) && $data['product'] === 'premium';
    }

    // Playback controls (Premium only)
    public function play()
    {
        if (!$this->isPremium()) {
            return response()->json(['error' => 'Spotify Premium required for playback.'], 403);
        }
        return $this->spotifyRequest('put', 'me/player/play');
    }

    public function pause()
    {
        if (!$this->isPremium()) {
            return response()->json(['error' => 'Spotify Premium required for playback.'], 403);
        }
        return $this->spotifyRequest('put', 'me/player/pause');
    }

    public function next()
    {
        if (!$this->isPremium()) {
            return response()->json(['error' => 'Spotify Premium required for playback.'], 403);
        }
        return $this->spotifyRequest('post', 'me/player/next');
    }

    // Search for tracks and return preview URLs
    public function search(Request $request)
    {
        $query = $request->query('query');
        $type = $request->query('type', 'track');

        $response = $this->spotifyRequest('get', "search?q=" . urlencode($query) . "&type=" . $type);

        $data = $response->getData(true);

        $items = $data[$type . 's']['items'] ?? [];

        $results = collect($items)->map(function ($item) {
            return [
                'name' => $item['name'],
                'artist' => $item['artists'][0]['name'] ?? '',
                'preview_url' => $item['preview_url'],
                'external_url' => $item['external_urls']['spotify'] ?? '',
            ];
        });

        return response()->json($results);
    }
}
