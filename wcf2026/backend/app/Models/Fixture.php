<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fixture extends Model
{
    /** @use HasFactory<\Database\Factories\FixtureFactory> */
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'round_id', 'kickoff_at', 'home_team_id', 'away_team_id',
        'status', 'home_score', 'away_score', 'home_score_et', 'away_score_et',
        'home_score_pen', 'away_score_pen', 'winner_team_id', 'neutral_venue',
        'leg', 'tie_id', 'original_kickoff_at', 'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['kickoff_at' => 'datetime', 'original_kickoff_at' => 'datetime', 'neutral_venue' => 'boolean'];
    }

    /** @return BelongsTo<Tournament, $this> */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /** @return BelongsTo<Round, $this> */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /** @return BelongsTo<Team, $this> */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /** @return BelongsTo<Team, $this> */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /** @return BelongsTo<Team, $this> */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    /** @return HasMany<ScorePrediction, $this> */
    public function scorePredictions(): HasMany
    {
        return $this->hasMany(ScorePrediction::class);
    }
}
