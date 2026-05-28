<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTournamentRequest;
use App\Http\Requests\Admin\UpdateTournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TournamentAdminController extends Controller
{
    public function store(StoreTournamentRequest $request): JsonResponse
    {
        $this->authorize('create', Tournament::class);

        $user = $this->authUser($request);

        $tournament = Tournament::create(array_merge(
            $request->validated(),
            ['owner_user_id' => $user->id, 'status' => 'draft']
        ));

        try {
            AuditLogger::record('tournament.created', $user->id, Tournament::class, $tournament->id);
        } catch (\Throwable) {
        }

        return (new TournamentResource($tournament))->response()->setStatusCode(201);
    }

    public function update(UpdateTournamentRequest $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $tournament->update($request->validated());

        try {
            AuditLogger::record('tournament.updated', $user->id, Tournament::class, $tournament->id);
        } catch (\Throwable) {
        }

        return (new TournamentResource($tournament->fresh()))->response();
    }

    public function destroy(Request $request, Tournament $tournament): Response
    {
        $this->authorize('delete', $tournament);

        $user = $this->authUser($request);

        $tournament->delete();

        try {
            AuditLogger::record('tournament.deleted', $user->id, Tournament::class, $tournament->id);
        } catch (\Throwable) {
        }

        return response()->noContent();
    }
}
