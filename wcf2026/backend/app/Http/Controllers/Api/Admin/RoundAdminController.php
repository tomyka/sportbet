<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoundRequest;
use App\Models\Round;
use App\Models\Stage;
use App\Models\Tournament;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoundAdminController extends Controller
{
    public function store(StoreRoundRequest $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $round = $stage->rounds()->create(array_merge(
            $request->validated(),
            ['tournament_id' => $tournament->id]
        ));

        try {
            AuditLogger::record('round.created', $user->id, Round::class, $round->id);
        } catch (\Throwable) {
        }

        return response()->json(['data' => ['id' => $round->id, 'name' => $round->name]], 201);
    }

    public function destroy(Request $request, Tournament $tournament, Stage $stage, Round $round): Response
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $round->delete();

        try {
            AuditLogger::record('round.deleted', $user->id, Round::class, $round->id);
        } catch (\Throwable) {
        }

        return response()->noContent();
    }
}
