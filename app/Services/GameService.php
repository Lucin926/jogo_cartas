<?php

namespace App\Services;

use App\Events\JudgingPhaseStartedEvent;
use App\Events\NextRoundReadyEvent;
use App\Events\PlayerHandUpdatedEvent;
use App\Events\PlayerJoinedEvent;
use App\Events\RoundStartedEvent;
use App\Events\RoundWinnerAnnouncedEvent;
use App\Events\SubmissionReceivedEvent;
use App\Events\VoteReceivedEvent;
use App\Models\Deck;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerHand;
use App\Models\Round;
use App\Models\RoundNextReady;
use App\Models\RoundVote;
use App\Models\Submission;
use App\Models\SubmissionCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameService
{
    public function __construct(
        protected CardDeckService $cardDeckService,
        protected QrCodeService $qrCodeService
    ) {}

    public function createGame(?string $title = null, int $totalRounds = 10): Game
    {
        $deck = Deck::where('is_active', true)->first();

        // Mínimo de 10 rodadas
        $totalRounds = max(10, $totalRounds);

        do {
            $code = strtoupper(Str::random(6));
        } while (Game::where('code', $code)->exists());

        return Game::create([
            'code' => $code,
            'title' => $title ?: 'Mentes Ousadas',
            'deck_id' => $deck?->id,
            'status' => 'lobby',
            'total_rounds' => $totalRounds,
            'max_score' => $totalRounds,
            'round_timeout_seconds' => 0,
        ]);
    }

    public function joinGame(string $gameCode, string $nickname, ?string $sessionToken = null): Player
    {
        $game = Game::where('code', $gameCode)->firstOrFail();

        if ($sessionToken) {
            $existing = Player::where('session_token', $sessionToken)
                ->where('game_id', $game->id)
                ->first();
            if ($existing) {
                $existing->update([
                    'is_connected' => true,
                    'last_seen_at' => now(),
                    'nickname' => $nickname ?: $existing->nickname,
                ]);
                broadcast(new PlayerJoinedEvent($existing));
                return $existing;
            }
        }

        $token = (string) Str::uuid();
        $isFirst = $game->players()->count() === 0;

        $player = Player::create([
            'game_id' => $game->id,
            'session_token' => $token,
            'nickname' => trim($nickname),
            'score' => 0,
            'is_host' => $isFirst,
            'is_connected' => true,
            'last_seen_at' => now(),
        ]);

        // Distribui a mão inicial com a quantidade exata de rodadas da partida (ex: 10 rodadas = 10 cartas, 15 = 15 cartas)
        $this->cardDeckService->dealInitialHand($player, $game->total_rounds);

        broadcast(new PlayerJoinedEvent($player));

        return $player;
    }

    public function startRound(Game $game): Round
    {
        return DB::transaction(function () use ($game) {
            $players = $game->players()->where('is_connected', true)->orderBy('id')->get();
            if ($players->count() < 1) {
                throw new \DomainException('É necessário ter pelo menos 1 participante para iniciar o jogo.');
            }

            $lastRound = $game->rounds()->orderByDesc('round_number')->first();
            $nextRoundNumber = ($lastRound?->round_number ?? 0) + 1;

            // Se já atingiu o total de rodadas configuradas, finaliza a partida
            if ($nextRoundNumber > $game->total_rounds) {
                $winner = $game->players()->orderByDesc('score')->first();
                $game->update([
                    'status' => 'finished',
                    'winner_player_id' => $winner?->id,
                ]);
                throw new \DomainException('Todas as rodadas foram concluídas!');
            }

            $blackCard = $this->cardDeckService->drawBlackCard($game);

            $round = Round::create([
                'game_id' => $game->id,
                'round_number' => $nextRoundNumber,
                'black_card_id' => $blackCard->id,
                'judge_player_id' => null,
                'status' => 'submitting',
            ]);

            $game->update(['status' => 'in_progress']);

            // Atualiza a mão atual de cada um via WebSocket
            foreach ($players as $p) {
                $handCards = $this->getPlayerHandData($p);
                broadcast(new PlayerHandUpdatedEvent($p, $handCards));
            }

            broadcast(new RoundStartedEvent($round));

            return $round;
        });
    }

    public function submitCards(Round $round, Player $player, array $whiteCardIds): Submission
    {
        return DB::transaction(function () use ($round, $player, $whiteCardIds) {
            if ($round->status !== 'submitting') {
                throw new \DomainException('Esta rodada não está aceitando respostas.');
            }

            $alreadySubmitted = Submission::where('round_id', $round->id)
                ->where('player_id', $player->id)
                ->exists();
            if ($alreadySubmitted) {
                throw new \DomainException('Você já enviou suas cartas nesta rodada.');
            }

            $pickCount = $round->blackCard->pick_count;
            if (count($whiteCardIds) !== $pickCount) {
                throw new \DomainException("Esta carta preta exige exatamente {$pickCount} carta(s).");
            }

            $ownedHands = PlayerHand::where('player_id', $player->id)
                ->whereIn('white_card_id', $whiteCardIds)
                ->where('is_used', false)
                ->get();

            if ($ownedHands->count() !== count($whiteCardIds)) {
                throw new \DomainException('Você tentou jogar cartas que não estão na sua mão.');
            }

            // Marca as cartas como usadas — A mão diminui automaticamente de 10 -> 9 -> 8... SEM REPOR!
            PlayerHand::where('player_id', $player->id)
                ->whereIn('white_card_id', $whiteCardIds)
                ->update(['is_used' => true]);

            $submission = Submission::create([
                'round_id' => $round->id,
                'player_id' => $player->id,
                'anonymous_order' => 0,
            ]);

            foreach ($whiteCardIds as $index => $cardId) {
                SubmissionCard::create([
                    'submission_id' => $submission->id,
                    'white_card_id' => $cardId,
                    'sequence_order' => $index + 1,
                ]);
            }

            $remainingHand = $this->getPlayerHandData($player);
            broadcast(new PlayerHandUpdatedEvent($player, $remainingHand));

            $submission->load('cards.whiteCard');
            $submissionData = [
                'id' => $submission->id,
                'player_nickname' => $player->nickname,
                'cards' => $submission->cards->map(fn($sc) => [
                    'order' => $sc->sequence_order,
                    'text' => $sc->whiteCard->text,
                ])->toArray(),
            ];

            $submissionsCount = $round->submissions()->count();
            $eligiblePlayers = $round->game->players()->where('is_connected', true)->count();

            broadcast(new SubmissionReceivedEvent($round, $submissionsCount, $eligiblePlayers, $submissionData));

            if ($submissionsCount >= $eligiblePlayers && $eligiblePlayers > 0) {
                $this->transitionToJudging($round);
            }

            return $submission;
        });
    }

    public function transitionToJudging(Round $round): void
    {
        $round->update(['status' => 'judging']);

        $submissions = $round->submissions()
            ->with(['cards.whiteCard'])
            ->get()
            ->shuffle();

        $anonymized = [];
        $order = 1;
        foreach ($submissions as $sub) {
            $sub->update(['anonymous_order' => $order]);
            $cards = $sub->cards->map(fn($sc) => [
                'order' => $sc->sequence_order,
                'text' => $sc->whiteCard->text,
            ])->toArray();

            $anonymized[] = [
                'submission_id' => $sub->id,
                'anonymous_number' => $order,
                'cards' => $cards,
            ];
            $order++;
        }

        broadcast(new JudgingPhaseStartedEvent($round, $anonymized));
    }

    public function castVote(Round $round, Player $voter, int $submissionId): RoundVote
    {
        return DB::transaction(function () use ($round, $voter, $submissionId) {
            if ($round->status !== 'judging') {
                throw new \DomainException('A rodada não está em fase de votação.');
            }

            $alreadyVoted = RoundVote::where('round_id', $round->id)
                ->where('player_id', $voter->id)
                ->exists();
            if ($alreadyVoted) {
                throw new \DomainException('Você já votou nesta rodada.');
            }

            $submission = Submission::where('round_id', $round->id)
                ->where('id', $submissionId)
                ->firstOrFail();

            if ($submission->player_id === $voter->id) {
                $totalActivePlayers = $round->game->players()->where('is_connected', true)->count();
                // Só impede o auto-voto se houver 2 ou mais jogadores na partida
                if ($totalActivePlayers > 1) {
                    throw new \DomainException('Você não pode votar na sua própria resposta.');
                }
            }

            $vote = RoundVote::create([
                'round_id' => $round->id,
                'player_id' => $voter->id,
                'submission_id' => $submission->id,
            ]);

            $votesCount = RoundVote::where('round_id', $round->id)->count();
            $eligibleVoters = $round->game->players()->where('is_connected', true)->count();

            broadcast(new VoteReceivedEvent($round, $votesCount, $eligibleVoters));

            if ($votesCount >= $eligibleVoters && $eligibleVoters > 0) {
                $this->finalizeRoundWinner($round);
            }

            return $vote;
        });
    }

    public function finalizeRoundWinner(Round $round): void
    {
        $voteCounts = RoundVote::where('round_id', $round->id)
            ->select('submission_id', DB::raw('count(*) as total_votes'))
            ->groupBy('submission_id')
            ->orderByDesc('total_votes')
            ->get();

        if ($voteCounts->isNotEmpty()) {
            $topVotes = $voteCounts->first()->total_votes;
            $topSubmissions = $voteCounts->where('total_votes', $topVotes)->pluck('submission_id');
            $winningSubmissionId = $topSubmissions->random();
        } else {
            $winningSubmissionId = $round->submissions()->inRandomOrder()->value('id');
        }

        $winningSubmission = Submission::with('player')->findOrFail($winningSubmissionId);
        $winnerPlayer = $winningSubmission->player;

        $round->update([
            'status' => 'finished',
            'winner_submission_id' => $winningSubmission->id,
        ]);

        $winnerPlayer->increment('score');

        // Checa se era a última rodada da partida
        $isGameFinished = $round->round_number >= $round->game->total_rounds;
        if ($isGameFinished) {
            $finalChampion = $round->game->players()->orderByDesc('score')->first();
            $round->game->update([
                'status' => 'finished',
                'winner_player_id' => $finalChampion?->id,
            ]);
        }

        broadcast(new RoundWinnerAnnouncedEvent($round, $winningSubmission, $winnerPlayer, $isGameFinished));
    }

    public function markPlayerReadyNextRound(Round $round, Player $player): void
    {
        RoundNextReady::firstOrCreate([
            'round_id' => $round->id,
            'player_id' => $player->id,
        ]);

        $readyCount = RoundNextReady::where('round_id', $round->id)->count();
        $totalPlayers = $round->game->players()->where('is_connected', true)->count();

        broadcast(new NextRoundReadyEvent($round, $readyCount, $totalPlayers));

        // Quando todos clicam em "Próxima Rodada", avança para a próxima se ainda não terminou a partida
        if ($readyCount >= $totalPlayers && $totalPlayers >= 1 && $round->game->status !== 'finished') {
            $this->startRound($round->game);
        }
    }

    public function getPlayerHandData(Player $player): array
    {
        return $player->activeHandCards()
            ->get()
            ->map(function ($hand) {
                return [
                    'id' => $hand->white_card_id,
                    'text' => $hand->whiteCard->text,
                ];
            })
            ->values()
            ->toArray();
    }
}
