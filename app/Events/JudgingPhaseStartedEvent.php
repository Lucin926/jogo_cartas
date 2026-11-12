<?php

namespace App\Events;

use App\Models\Round;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JudgingPhaseStartedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Round $round, public array $shuffledSubmissions) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->round->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'judging_started',
            'round_id' => $this->round->id,
            'submissions' => $this->shuffledSubmissions,
            'judge_id' => $this->round->judge_player_id,
        ];
    }
}
