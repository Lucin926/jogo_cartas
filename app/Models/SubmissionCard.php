<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionCard extends Model
{
    protected $fillable = ['submission_id', 'white_card_id', 'sequence_order'];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function whiteCard(): BelongsTo
    {
        return $this->belongsTo(WhiteCard::class);
    }
}
