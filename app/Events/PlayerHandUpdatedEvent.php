<?php

namespace App\Events;

use App\Models\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerHandUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Player $player, public array $handCards) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('player.' . $this->player->session_token),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'hand_updated',
            'cards' => $this->handCards,
            'score' => $this->player->score,
        ];
    }
}
