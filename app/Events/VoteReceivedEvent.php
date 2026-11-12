<?php

namespace App\Events;

use App\Models\Round;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteReceivedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Round $round,
        public int $votesCount,
        public int $eligibleCount
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->round->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'vote_received',
            'round_id' => $this->round->id,
            'votes_count' => $this->votesCount,
            'eligible_count' => $this->eligibleCount,
        ];
    }
}
