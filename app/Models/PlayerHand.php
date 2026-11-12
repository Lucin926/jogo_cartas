<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerHand extends Model
{
    protected $fillable = ['player_id', 'white_card_id', 'is_used'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function whiteCard(): BelongsTo
    {
        return $this->belongsTo(WhiteCard::class);
    }
}
