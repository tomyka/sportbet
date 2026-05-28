<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Fixture;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin Fixture */
class FixtureResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'round_id' => $this->round_id,
            'kickoff_at' => $this->kickoff_at === null ? null : Carbon::parse($this->kickoff_at)->toIso8601String(),
            'home_team_id' => $this->home_team_id,
            'away_team_id' => $this->away_team_id,
            'status' => $this->status,
            'home_score' => $this->home_score,
            'away_score' => $this->away_score,
            'home_score_et' => $this->home_score_et,
            'away_score_et' => $this->away_score_et,
            'home_score_pen' => $this->home_score_pen,
            'away_score_pen' => $this->away_score_pen,
            'winner_team_id' => $this->winner_team_id,
            'neutral_venue' => $this->neutral_venue,
        ];
    }
}
