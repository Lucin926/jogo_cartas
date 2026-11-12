<?php

namespace App\Events;

use App\Models\Round;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundStartedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Round $round) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->round->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        $this->round->load('blackCard');
        $totalPlayers = $this->round->game->players()->where('is_connected', true)->count();

        return [
            'type' => 'round_started',
            'round' => [
                'id' => $this->round->id,
                'round_number' => $this->round->round_number,
                'status' => $this->round->status,
                'black_card' => [
                    'id' => $this->round->blackCard->id,
                    'text' => $this->round->blackCard->text,
                    'pick_count' => $this->round->blackCard->pick_count,
                ],
                'submissions_count' => 0,
                'eligible_count' => $totalPlayers,
            ],
        ];
    }
}
