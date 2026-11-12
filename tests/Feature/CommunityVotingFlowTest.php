<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\RoundVote;
use App\Services\GameService;
use Database\Seeders\GameDeckSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityVotingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GameDeckSeeder::class);
    }

    public function test_community_voting_and_collective_ready_flow(): void
    {
        /** @var GameService $gameService */
        $gameService = app(GameService::class);

        // 1. Cria a sala
        $game = $gameService->createGame('Votação Coletiva', 3);

        // 2. 3 Jogadores entram na sala
        $alice = $gameService->joinGame($game->code, 'Alice');
        $bob = $gameService->joinGame($game->code, 'Bob');
        $carlos = $gameService->joinGame($game->code, 'Carlos');

        // 3. Inicia a primeira rodada (SEM JUIZ FIXO)
        $round = $gameService->startRound($game);
        $this->assertEquals(1, $round->round_number);
        $this->assertNull($round->judge_player_id);
        $this->assertEquals('submitting', $round->status);

        $pickCount = $round->blackCard->pick_count;

        // 4. TODOS os jogadores enviam respostas
        $subAlice = $gameService->submitCards($round, $alice, $alice->activeHandCards()->take($pickCount)->pluck('white_card_id')->toArray());
        $subBob = $gameService->submitCards($round, $bob, $bob->activeHandCards()->take($pickCount)->pluck('white_card_id')->toArray());
        $subCarlos = $gameService->submitCards($round, $carlos, $carlos->activeHandCards()->take($pickCount)->pluck('white_card_id')->toArray());

        // 5. Após todos enviarem, a rodada transiciona para 'judging' (votação)
        $round->refresh();
        $this->assertEquals('judging', $round->status);

        // 6. Votação coletiva: Alice e Carlos votam na resposta do Bob; Bob vota na de Alice
        $gameService->castVote($round, $alice, $subBob->id);
        $gameService->castVote($round, $carlos, $subBob->id);
        $gameService->castVote($round, $bob, $subAlice->id);

        // 7. Rodada finaliza automaticamente com Bob vencedor (mais votado)
        $round->refresh();
        $this->assertEquals('finished', $round->status);
        $this->assertEquals($subBob->id, $round->winner_submission_id);
        $this->assertEquals(1, $bob->fresh()->score);

        // 8. Todos clicam em "Próxima Rodada"
        $gameService->markPlayerReadyNextRound($round, $alice);
        $gameService->markPlayerReadyNextRound($round, $bob);
        $this->assertEquals(1, $game->fresh()->rounds()->count()); // Ainda não avançou pois falta Carlos

        $gameService->markPlayerReadyNextRound($round, $carlos);

        // Quando o último clica em "Próxima Rodada", a Rodada 2 começa automaticamente!
        $this->assertEquals(2, $game->fresh()->rounds()->count());
        $round2 = $game->fresh()->currentRound;
        $this->assertEquals(2, $round2->round_number);
        $this->assertEquals('submitting', $round2->status);
    }
}
