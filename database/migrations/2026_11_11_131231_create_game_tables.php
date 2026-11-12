<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Baralhos
        Schema::create('decks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Cartas Pretas (Perguntas)
        Schema::create('black_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_id')->constrained('decks')->onDelete('cascade');
            $table->text('text');
            $table->unsignedTinyInteger('pick_count')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Cartas Brancas (Respostas)
        Schema::create('white_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_id')->constrained('decks')->onDelete('cascade');
            $table->text('text');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Jogos / Salas
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('title')->default('Mentes Ousadas');
            $table->foreignId('deck_id')->nullable()->constrained('decks')->nullOnDelete();
            $table->enum('status', ['lobby', 'in_progress', 'paused', 'finished'])->default('lobby');
            $table->unsignedInteger('max_score')->default(5);
            $table->unsignedInteger('round_timeout_seconds')->default(0); // 0 = desativado
            $table->unsignedBigInteger('winner_player_id')->nullable();
            $table->timestamps();
        });

        // 5. Jogadores
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('session_token', 64)->unique();
            $table->string('nickname');
            $table->unsignedInteger('score')->default(0);
            $table->boolean('is_host')->default(false);
            $table->boolean('is_connected')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        // 6. Rodadas
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->unsignedInteger('round_number')->default(1);
            $table->foreignId('black_card_id')->constrained('black_cards');
            $table->foreignId('judge_player_id')->constrained('players')->onDelete('cascade');
            $table->enum('status', ['submitting', 'judging', 'finished'])->default('submitting');
            $table->unsignedBigInteger('winner_submission_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 7. Mãos dos Jogadores (10 cartas brancas ativas por jogador)
        Schema::create('player_hands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('white_card_id')->constrained('white_cards')->onDelete('cascade');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index(['player_id', 'is_used']);
        });

        // 8. Submissões de Respostas na Rodada
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('rounds')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->unsignedInteger('anonymous_order')->default(0);
            $table->timestamps();

            $table->unique(['round_id', 'player_id']);
        });

        // 9. Cartas da Submissão (suporta 1, 2 ou 3 cartas em ordem)
        Schema::create('submission_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('white_card_id')->constrained('white_cards')->onDelete('cascade');
            $table->unsignedTinyInteger('sequence_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_cards');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('player_hands');
        Schema::dropIfExists('rounds');
        Schema::dropIfExists('players');
        Schema::dropIfExists('games');
        Schema::dropIfExists('white_cards');
        Schema::dropIfExists('black_cards');
        Schema::dropIfExists('decks');
    }
};
