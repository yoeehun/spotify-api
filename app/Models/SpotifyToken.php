<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpotifyToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'access_token', 'refresh_token', 'expires_at',
    ];

    protected $dates = ['expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
