<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamRequest;
use App\Http\Requests\Admin\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\Tournament;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamAdminController extends Controller
{
    public function store(StoreTeamRequest $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $team = $tournament->teams()->create($request->validated());

        try {
            AuditLogger::record('team.created', $user->id, Team::class, $team->id);
        } catch (\Throwable) {
        }

        return (new TeamResource($team))->response()->setStatusCode(201);
    }

    public function update(UpdateTeamRequest $request, Tournament $tournament, Team $team): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $team->update($request->validated());

        try {
            AuditLogger::record('team.updated', $user->id, Team::class, $team->id);
        } catch (\Throwable) {
        }

        return (new TeamResource($team->fresh()))->response();
    }

    public function destroy(Request $request, Tournament $tournament, Team $team): Response
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $team->delete();

        try {
            AuditLogger::record('team.deleted', $user->id, Team::class, $team->id);
        } catch (\Throwable) {
        }

        return response()->noContent();
    }
}
