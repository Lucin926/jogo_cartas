<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    protected $fillable = [
        'game_id',
        'round_number',
        'black_card_id',
        'judge_player_id',
        'status',
        'winner_submission_id',
        'expires_at',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function blackCard(): BelongsTo
    {
        return $this->belongsTo(BlackCard::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(RoundVote::class);
    }

    public function nextReady(): HasMany
    {
        return $this->hasMany(RoundNextReady::class);
    }

    public function winnerSubmission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'winner_submission_id');
    }
}
