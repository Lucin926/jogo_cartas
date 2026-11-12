<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhiteCard extends Model
{
    protected $fillable = ['deck_id', 'text', 'is_active'];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function hands(): HasMany
    {
        return $this->hasMany(PlayerHand::class);
    }
}
