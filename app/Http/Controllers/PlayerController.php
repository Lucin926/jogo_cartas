<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use App\Models\RoundNextReady;
use App\Models\RoundVote;
use App\Models\Submission;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function __construct(
        protected GameService $gameService
    ) {}

    public function showJoinForm(string $code)
    {
        $code = strtoupper(trim($code));
        $game = Game::where('code', $code)->first();

        if (!$game) {
            // Se a sala não existir ou for código antigo, redireciona amigavelmente para a tela inicial
            return redirect()->route('home')->with('error', 'A sala informada não foi encontrada ou já foi encerrada.');
        }

        return view('player.join', compact('game'));
    }

    public function join(Request $request, string $code)
    {
        $code = strtoupper(trim($code));
        $validated = $request->validate([
            'nickname' => 'required|string|min:2|max:30',
            'session_token' => 'nullable|string',
        ]);

        try {
            $player = $this->gameService->joinGame(
                $code,
                $validated['nickname'],
                $validated['session_token'] ?? null
            );

            return response()->json([
                'success' => true,
                'player' => [
                    'id' => $player->id,
                    'nickname' => $player->nickname,
                    'session_token' => $player->session_token,
                    'score' => $player->score,
                ],
                'redirect_url' => route('player.play', ['token' => $player->session_token]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sala não encontrada.'], 404);
        }
    }

    public function play(string $token)
    {
        $player = Player::where('session_token', $token)->with('game')->firstOrFail();
        $game = $player->game;

        return view('player.play', compact('player', 'game'));
    }

    public function syncState(string $token): JsonResponse
    {
        $player = Player::where('session_token', $token)->with('game')->firstOrFail();
        $game = $player->game;
        $currentRound = $game->currentRound;

        $hasSubmitted = false;
        $hasVoted = false;
        $isReadyNext = false;
        $ownSubmissionId = null;

        if ($currentRound) {
            $mySub = $currentRound->submissions()->where('player_id', $player->id)->first();
            $hasSubmitted = (bool) $mySub;
            $ownSubmissionId = $mySub?->id;
            $hasVoted = RoundVote::where('round_id', $currentRound->id)->where('player_id', $player->id)->exists();
            $isReadyNext = RoundNextReady::where('round_id', $currentRound->id)->where('player_id', $player->id)->exists();
        }

        $handCards = $this->gameService->getPlayerHandData($player);

        $anonymizedSubmissions = [];
        if ($currentRound && in_array($currentRound->status, ['judging', 'finished'])) {
            $subs = $currentRound->submissions()->with(['cards.whiteCard'])->orderBy('anonymous_order')->get();
            foreach ($subs as $sub) {
                $anonymizedSubmissions[] = [
                    'submission_id' => $sub->id,
                    'anonymous_number' => $sub->anonymous_order,
                    'is_own' => ($sub->id === $ownSubmissionId),
                    'cards' => $sub->cards->map(fn($sc) => [
                        'order' => $sc->sequence_order,
                        'text' => $sc->whiteCard->text,
                    ]),
                ];
            }
        }

        $totalPlayers = $game->players()->where('is_connected', true)->count();
        $readyCount = $currentRound ? RoundNextReady::where('round_id', $currentRound->id)->count() : 0;
        $votesCount = $currentRound ? RoundVote::where('round_id', $currentRound->id)->count() : 0;

        return response()->json([
            'player' => [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'score' => $player->score,
                'is_host' => $player->is_host,
                'hand' => $handCards,
            ],
            'game' => [
                'code' => $game->code,
                'title' => $game->title,
                'status' => $game->status,
                'max_score' => $game->max_score,
            ],
            'current_round' => $currentRound ? [
                'id' => $currentRound->id,
                'round_number' => $currentRound->round_number,
                'status' => $currentRound->status,
                'black_card' => $currentRound->blackCard,
                'has_submitted' => $hasSubmitted,
                'has_voted' => $hasVoted,
                'is_ready_next' => $isReadyNext,
                'votes_count' => $votesCount,
                'ready_count' => $readyCount,
                'total_players' => $totalPlayers,
                'anonymized_submissions' => $anonymizedSubmissions,
                'winner_submission_id' => $currentRound->winner_submission_id,
            ] : null,
        ]);
    }

    public function submitCards(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'round_id' => 'required|integer',
            'card_ids' => 'required|array|min:1|max:3',
            'card_ids.*' => 'integer',
        ]);

        $player = Player::where('session_token', $token)->firstOrFail();
        $round = Round::where('id', $validated['round_id'])->firstOrFail();

        try {
            $submission = $this->gameService->submitCards($round, $player, $validated['card_ids']);
            return response()->json([
                'success' => true,
                'message' => 'Resposta enviada com sucesso!',
                'submission_id' => $submission->id,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Qualquer jogador vota na resposta que achar melhor (não pode ser na dele).
     */
    public function castVote(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'round_id' => 'required|integer',
            'submission_id' => 'required|integer',
        ]);

        $player = Player::where('session_token', $token)->firstOrFail();
        $round = Round::where('id', $validated['round_id'])->firstOrFail();

        try {
            $vote = $this->gameService->castVote($round, $player, $validated['submission_id']);
            return response()->json([
                'success' => true,
                'message' => 'Voto computado com sucesso!',
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Jogador clica em "Próxima Rodada".
     */
    public function readyNextRound(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'round_id' => 'required|integer',
        ]);

        $player = Player::where('session_token', $token)->firstOrFail();
        $round = Round::where('id', $validated['round_id'])->firstOrFail();

        $this->gameService->markPlayerReadyNextRound($round, $player);

        return response()->json([
            'success' => true,
            'message' => 'Pronto para a próxima rodada!',
        ]);
    }
}
