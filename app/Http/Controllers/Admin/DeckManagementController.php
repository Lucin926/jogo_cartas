<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlackCard;
use App\Models\Deck;
use App\Models\WhiteCard;
use Illuminate\Http\Request;

class DeckManagementController extends Controller
{
    public function index()
    {
        $decks = Deck::withCount(['blackCards', 'whiteCards'])->get();
        $selectedDeck = $decks->first();
        $blackCards = $selectedDeck ? $selectedDeck->blackCards()->latest()->paginate(15, ['*'], 'black_page') : collect();
        $whiteCards = $selectedDeck ? $selectedDeck->whiteCards()->latest()->paginate(15, ['*'], 'white_page') : collect();

        return view('admin.decks', compact('decks', 'selectedDeck', 'blackCards', 'whiteCards'));
    }

    public function storeBlackCard(Request $request)
    {
        $validated = $request->validate([
            'deck_id' => 'required|exists:decks,id',
            'text' => 'required|string|min:5',
        ]);

        $pickCount = BlackCard::countPickSlots($validated['text']);

        BlackCard::create([
            'deck_id' => $validated['deck_id'],
            'text' => $validated['text'],
            'pick_count' => $pickCount,
            'is_active' => true,
        ]);

        return back()->with('success', "Carta preta adicionada! (Detectado {$pickCount} espaço(s))");
    }

    public function storeWhiteCard(Request $request)
    {
        $validated = $request->validate([
            'deck_id' => 'required|exists:decks,id',
            'text' => 'required|string|min:2',
        ]);

        WhiteCard::create([
            'deck_id' => $validated['deck_id'],
            'text' => $validated['text'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Carta branca adicionada!');
    }

    public function destroyBlackCard(BlackCard $blackCard)
    {
        $blackCard->delete();
        return back()->with('success', 'Carta preta excluída.');
    }

    public function destroyWhiteCard(WhiteCard $whiteCard)
    {
        $whiteCard->delete();
        return back()->with('success', 'Carta branca excluída.');
    }
}
