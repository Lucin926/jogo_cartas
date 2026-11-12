<?php

namespace App\Services;

use App\Models\BlackCard;
use App\Models\Deck;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerHand;
use App\Models\Round;
use App\Models\WhiteCard;

class CardDeckService
{
    /**
     * Distribui a mão inicial para o jogador no momento em que ele entra.
     * A quantidade de cartas inicial é igual ao total de rodadas da partida (mínimo 10).
     */
    public function dealInitialHand(Player $player, int $totalCards): void
    {
        $currentCount = $player->activeHandCards()->count();
        $needed = $totalCards - $currentCount;

        if ($needed <= 0) {
            return;
        }

        $deckId = $player->game->deck_id ?? Deck::where('is_active', true)->value('id');

        $usedCardIds = PlayerHand::where('player_id', $player->id)
            ->pluck('white_card_id')
            ->toArray();

        $availableCards = WhiteCard::where('deck_id', $deckId)
            ->where('is_active', true)
            ->whereNotIn('id', $usedCardIds)
            ->inRandomOrder()
            ->limit($needed)
            ->get();

        if ($availableCards->count() < $needed) {
            $extraNeeded = $needed - $availableCards->count();
            $moreCards = WhiteCard::where('deck_id', $deckId)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit($extraNeeded)
                ->get();
            $availableCards = $availableCards->merge($moreCards);
        }

        foreach ($availableCards as $card) {
            PlayerHand::create([
                'player_id' => $player->id,
                'white_card_id' => $card->id,
                'is_used' => false,
            ]);
        }
    }

    /**
     * Sorteia uma nova carta preta ainda não jogada nesta partida.
     */
    public function drawBlackCard(Game $game): BlackCard
    {
        $deckId = $game->deck_id ?? Deck::where('is_active', true)->value('id');

        $playedBlackCardIds = Round::where('game_id', $game->id)
            ->pluck('black_card_id')
            ->toArray();

        $blackCard = BlackCard::where('deck_id', $deckId)
            ->where('is_active', true)
            ->whereNotIn('id', $playedBlackCardIds)
            ->inRandomOrder()
            ->first();

        if (!$blackCard) {
            $blackCard = BlackCard::where('deck_id', $deckId)
                ->where('is_active', true)
                ->inRandomOrder()
                ->firstOrFail();
        }

        return $blackCard;
    }
}
