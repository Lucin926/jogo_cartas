<?php

namespace App\Events;

use App\Models\Round;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmissionReceivedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Round $round, public int $submissionsCount, public int $eligibleCount) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->round->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'submission_received',
            'round_id' => $this->round->id,
            'submissions_count' => $this->submissionsCount,
            'eligible_count' => $this->eligibleCount,
        ];
    }
}
