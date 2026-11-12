<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Round;
use App\Models\RoundNextReady;
use App\Models\RoundVote;
use App\Services\CardDeckService;
use App\Services\GameService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        protected GameService $gameService,
        protected QrCodeService $qrCodeService,
        protected CardDeckService $cardDeckService
    ) {}

    public function index()
    {
        return view('game.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'total_rounds' => 'required|integer|min:10|max:50',
        ]);

        $game = $this->gameService->createGame(
            $validated['title'] ?? 'Mentes Ousadas',
            $validated['total_rounds']
        );

        return redirect()->route('game.host', ['code' => $game->code]);
    }

    public function host(Request $request, string $code)
    {
        $game = Game::where('code', $code)
            ->with(['players', 'currentRound.blackCard', 'winner'])
            ->firstOrFail();

        $host = $request->getHost();

        // Se estiver acessando via link público da Cloudflare ou domínio externo na internet
        if (str_contains($host, 'trycloudflare.com') || (!in_array($host, ['localhost', '127.0.0.1']) && !str_starts_with($host, '192.168.') && !str_starts_with($host, '10.'))) {
            $joinUrl = $request->getScheme() . '://' . $host . '/join/' . $game->code;
        } 
        // Se estiver em rede local
        elseif (in_array($host, ['localhost', '127.0.0.1'])) {
            $output = shell_exec("hostname -I | awk '{print $1}'");
            $localIp = trim($output) ?: '127.0.0.1';
            $port = $request->getPort() ? ':' . $request->getPort() : '';
            $joinUrl = $request->getScheme() . '://' . $localIp . $port . '/join/' . $game->code;
        } else {
            $joinUrl = route('game.join.form', ['code' => $game->code]);
        }

        $qrCodeSvg = $this->qrCodeService->generateSvg($joinUrl, 240);

        return view('game.host', compact('game', 'joinUrl', 'qrCodeSvg'));
    }

    public function startRound(string $code): JsonResponse
    {
        $game = Game::where('code', $code)->firstOrFail();

        try {
            $round = $this->gameService->startRound($game);
            return response()->json([
                'success' => true,
                'message' => 'Rodada iniciada com sucesso!',
                'round_id' => $round->id,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function forceJudging(string $code): JsonResponse
    {
        $game = Game::where('code', $code)->firstOrFail();
        $round = $game->currentRound;

        if (!$round || $round->status !== 'submitting') {
            return response()->json(['success' => false, 'message' => 'Nenhuma rodada em submissão.'], 422);
        }

        $this->gameService->transitionToJudging($round);

        return response()->json(['success' => true, 'message' => 'Fase de votação iniciada.']);
    }

    public function removePlayer(string $code, int $playerId): JsonResponse
    {
        $game = Game::where('code', $code)->firstOrFail();
        $player = Player::where('game_id', $game->id)->where('id', $playerId)->firstOrFail();

        $player->delete();

        return response()->json(['success' => true, 'message' => 'Jogador removido com sucesso.']);
    }

    public function hostState(string $code): JsonResponse
    {
        $game = Game::where('code', $code)
            ->with(['players', 'currentRound.blackCard', 'winner'])
            ->firstOrFail();

        $currentRound = $game->currentRound;
        $submissionsCount = $currentRound ? $currentRound->submissions()->count() : 0;
        $eligibleCount = $game->players()->where('is_connected', true)->count();
        $votesCount = $currentRound ? RoundVote::where('round_id', $currentRound->id)->count() : 0;
        $readyCount = $currentRound ? RoundNextReady::where('round_id', $currentRound->id)->count() : 0;

        $anonymizedSubmissions = [];
        if ($currentRound && in_array($currentRound->status, ['judging', 'finished'])) {
            $subs = $currentRound->submissions()->with(['cards.whiteCard'])->orderBy('anonymous_order')->get();
            foreach ($subs as $sub) {
                $votesForThis = RoundVote::where('submission_id', $sub->id)->count();
                $anonymizedSubmissions[] = [
                    'submission_id' => $sub->id,
                    'anonymous_number' => $sub->anonymous_order,
                    'votes' => $votesForThis,
                    'cards' => $sub->cards->map(fn($sc) => [
                        'order' => $sc->sequence_order,
                        'text' => $sc->whiteCard->text,
                    ]),
                ];
            }
        }

        return response()->json([
            'game' => [
                'code' => $game->code,
                'title' => $game->title,
                'status' => $game->status,
                'total_rounds' => $game->total_rounds,
                'max_score' => $game->max_score,
                'players' => $game->players()->orderByDesc('score')->get(['id', 'nickname', 'score', 'is_host', 'is_connected']),
                'winner' => $game->winner ? ['id' => $game->winner->id, 'nickname' => $game->winner->nickname] : null,
            ],
            'current_round' => $currentRound ? [
                'id' => $currentRound->id,
                'round_number' => $currentRound->round_number,
                'status' => $currentRound->status,
                'black_card' => $currentRound->blackCard,
                'submissions_count' => $submissionsCount,
                'eligible_count' => $eligibleCount,
                'votes_count' => $votesCount,
                'ready_count' => $readyCount,
                'anonymized_submissions' => $anonymizedSubmissions,
                'winner_submission_id' => $currentRound->winner_submission_id,
            ] : null,
        ]);
    }
}
