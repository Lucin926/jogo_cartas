<?php

namespace App\Events;

use App\Models\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoinedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Player $player) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->player->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'player_joined',
            'player' => [
                'id' => $this->player->id,
                'nickname' => $this->player->nickname,
                'score' => $this->player->score,
                'is_host' => $this->player->is_host,
                'is_connected' => $this->player->is_connected,
            ],
            'total_players' => $this->player->game->players()->count(),
        ];
    }
}
