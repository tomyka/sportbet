<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->timestamp('kickoff_at')->nullable();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('status')->default('scheduled'); // scheduled|live|finished|postponed|cancelled
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->unsignedTinyInteger('home_score_et')->nullable();
            $table->unsignedTinyInteger('away_score_et')->nullable();
            $table->unsignedTinyInteger('home_score_pen')->nullable();
            $table->unsignedTinyInteger('away_score_pen')->nullable();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->boolean('neutral_venue')->default(false);
            $table->tinyInteger('leg')->default(1);
            $table->bigInteger('tie_id')->nullable(); // no FK — links two-leg ties
            $table->timestamp('original_kickoff_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('fixtures'); }
};
