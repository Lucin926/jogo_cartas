<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function blackCards(): HasMany
    {
        return $this->hasMany(BlackCard::class);
    }

    public function whiteCards(): HasMany
    {
        return $this->hasMany(WhiteCard::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
