<?php

namespace App\Events;

use App\Models\Player;
use App\Models\Round;
use App\Models\Submission;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundWinnerAnnouncedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Round $round,
        public Submission $winningSubmission,
        public Player $winnerPlayer,
        public bool $isGameFinished = false
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->round->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        $cards = $this->winningSubmission->cards()->with('whiteCard')->get()->map(function ($c) {
            return $c->whiteCard->text;
        });

        $leaderboard = $this->round->game->players()
            ->orderByDesc('score')
            ->get(['id', 'nickname', 'score', 'is_host']);

        return [
            'type' => 'round_winner',
            'round_id' => $this->round->id,
            'winning_submission_id' => $this->winningSubmission->id,
            'winner' => [
                'id' => $this->winnerPlayer->id,
                'nickname' => $this->winnerPlayer->nickname,
                'score' => $this->winnerPlayer->score,
            ],
            'winning_cards' => $cards,
            'is_game_finished' => $this->isGameFinished,
            'leaderboard' => $leaderboard,
        ];
    }
}
