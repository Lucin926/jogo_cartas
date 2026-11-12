<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Permite judge_player_id ser nulo na tabela rounds (já que a votação é coletiva)
        Schema::table('rounds', function (Blueprint $table) {
            $table->unsignedBigInteger('judge_player_id')->nullable()->change();
        });

        // 2. Tabela de votos coletivos da rodada
        Schema::create('round_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('rounds')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['round_id', 'player_id']);
        });

        // 3. Tabela de confirmação "Próxima Rodada" (Ready) de cada jogador
        Schema::create('round_next_ready', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('rounds')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['round_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_next_ready');
        Schema::dropIfExists('round_votes');
    }
};
