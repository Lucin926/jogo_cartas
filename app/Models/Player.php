<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'game_id',
        'session_token',
        'nickname',
        'score',
        'is_host',
        'is_connected',
        'last_seen_at',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function hands(): HasMany
    {
        return $this->hasMany(PlayerHand::class);
    }

    public function activeHandCards(): HasMany
    {
        return $this->hasMany(PlayerHand::class)->where('is_used', false)->with('whiteCard');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
