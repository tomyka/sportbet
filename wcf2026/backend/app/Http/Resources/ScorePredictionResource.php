<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ScorePrediction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ScorePrediction */
class ScorePredictionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var \Illuminate\Support\Carbon|null $submittedAt */
        $submittedAt = $this->submitted_at;

        return [
            'id' => $this->id,
            'fixture_id' => $this->fixture_id,
            'tournament_id' => $this->tournament_id,
            'home_score' => $this->home_score,
            'away_score' => $this->away_score,
            'predicted_winner_team_id' => $this->predicted_winner_team_id,
            'submitted_at' => $submittedAt?->toIso8601String(),
        ];
    }
}
