<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitScorePredictionRequest;
use App\Http\Resources\ScorePredictionResource;
use App\Models\Fixture;
use App\Models\ScorePrediction;
use App\Models\Tournament;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScorePredictionController extends Controller
{
    /**
     * List the authenticated user's score predictions for a tournament.
     */
    public function index(Request $request, Tournament $tournament): AnonymousResourceCollection
    {
        $user = $this->authUser($request);

        $predictions = ScorePrediction::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->orderBy('fixture_id')
            ->get();

        return ScorePredictionResource::collection($predictions);
    }

    /**
     * Submit or update the authenticated user's score prediction for a fixture.
     */
    public function upsert(
        SubmitScorePredictionRequest $request,
        Tournament $tournament,
        Fixture $fixture,
    ): JsonResponse {
        $user = $this->authUser($request);

        // Deadline check — return 423 Locked when kickoff has passed or match is underway
        if ($this->isPredictionLocked($fixture)) {
            abort(423, 'Prediction window has closed for this fixture.');
        }

        $prediction = ScorePrediction::updateOrCreate(
            [
                'user_id' => $user->id,
                'fixture_id' => $fixture->id,
            ],
            [
                'tournament_id' => $tournament->id,
                'home_score' => $request->integer('home_score'),
                'away_score' => $request->integer('away_score'),
                'predicted_winner_team_id' => $request->filled('predicted_winner_team_id')
                    ? $request->integer('predicted_winner_team_id')
                    : null,
                'submitted_at' => now(),
            ]
        );

        try {
            AuditLogger::record('score_prediction.upserted', $user->id, ScorePrediction::class, $prediction->id);
        } catch (\Throwable) {
        }

        return (new ScorePredictionResource($prediction))->response()->setStatusCode(200);
    }

    private function isPredictionLocked(Fixture $fixture): bool
    {
        if (in_array($fixture->status, ['live', 'finished'], true)) {
            return true;
        }

        if ($fixture->kickoff_at !== null && now()->greaterThanOrEqualTo($fixture->kickoff_at)) {
            return true;
        }

        return false;
    }
}
