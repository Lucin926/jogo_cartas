<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackCard extends Model
{
    protected $fillable = ['deck_id', 'text', 'pick_count', 'is_active'];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /**
     * Detecta automaticamente o número de lacunas no texto se pick_count não for fixado.
     */
    public static function countPickSlots(string $text): int
    {
        preg_match_all('/_{2,}/', $text, $matches);
        $count = count($matches[0]);
        return $count > 0 ? $count : 1;
    }
}
