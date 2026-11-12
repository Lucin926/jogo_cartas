<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Game;
use App\Models\Player;
use App\Services\GameService;
use Database\Seeders\GameDeckSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiplayerGameFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GameDeckSeeder::class);
    }

    public function test_complete_round_lifecycle_with_real_players(): void
    {
        /** @var GameService $gameService */
        $gameService = app(GameService::class);

        // 1. Cria a sala
        $game = $gameService->createGame('Teste Automatizado', 3);
        $this->assertDatabaseHas('games', ['code' => $game->code, 'status' => 'lobby']);

        // 2. 3 Jogadores entram na sala
        $alice = $gameService->joinGame($game->code, 'Alice');
        $bob = $gameService->joinGame($game->code, 'Bob');
        $carlos = $gameService->joinGame($game->code, 'Carlos');

        // Cada jogador deve receber 10 cartas na mão
        $this->assertEquals(10, $alice->activeHandCards()->count());
        $this->assertEquals(10, $bob->activeHandCards()->count());
        $this->assertEquals(10, $carlos->activeHandCards()->count());

        // 3. Inicia a primeira rodada
        $round = $gameService->startRound($game);
        $this->assertEquals(1, $round->round_number);
        $this->assertEquals('submitting', $round->status);
        $this->assertNotNull($round->black_card_id);
        $this->assertNotNull($round->judge_player_id);

        $judgeId = $round->judge_player_id;
        $nonJudges = collect([$alice, $bob, $carlos])->filter(fn($p) => $p->id !== $judgeId)->values();
        $this->assertCount(2, $nonJudges);

        // 4. Jogadores comuns enviam suas respostas
        $pickCount = $round->blackCard->pick_count;

        foreach ($nonJudges as $player) {
            $cardsToPlay = $player->activeHandCards()->take($pickCount)->pluck('white_card_id')->toArray();
            $submission = $gameService->submitCards($round, $player, $cardsToPlay);
            $this->assertNotNull($submission);
        }

        // 5. Após todos enviarem, a rodada transiciona para 'judging'
        $round->refresh();
        $this->assertEquals('judging', $round->status);

        // 6. Juiz escolhe a primeira submissão anônima como vencedora
        $judgePlayer = Player::find($judgeId);
        $winningSubmission = $round->submissions()->first();
        $gameService->pickWinner($round, $judgePlayer, $winningSubmission->id);

        $round->refresh();
        $this->assertEquals('finished', $round->status);
        $this->assertEquals($winningSubmission->id, $round->winner_submission_id);

        // Autor da resposta vencedora deve ter recebido +1 ponto
        $winnerPlayer = $winningSubmission->player->fresh();
        $this->assertEquals(1, $winnerPlayer->score);
    }
}
