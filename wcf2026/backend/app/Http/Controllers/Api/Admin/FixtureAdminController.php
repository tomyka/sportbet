<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFixtureRequest;
use App\Http\Requests\Admin\UpdateFixtureRequest;
use App\Http\Resources\FixtureResource;
use App\Models\Fixture;
use App\Models\Tournament;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FixtureAdminController extends Controller
{
    public function store(StoreFixtureRequest $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $fixture = $tournament->fixtures()->create($request->validated());

        try {
            AuditLogger::record('fixture.created', $user->id, Fixture::class, $fixture->id);
        } catch (\Throwable) {
        }

        return (new FixtureResource($fixture))->response()->setStatusCode(201);
    }

    public function update(UpdateFixtureRequest $request, Tournament $tournament, Fixture $fixture): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $fixture->update($request->validated());

        try {
            AuditLogger::record('fixture.updated', $user->id, Fixture::class, $fixture->id);
        } catch (\Throwable) {
        }

        return (new FixtureResource($fixture->fresh()))->response();
    }

    public function destroy(Request $request, Tournament $tournament, Fixture $fixture): Response
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $fixture->delete();

        try {
            AuditLogger::record('fixture.deleted', $user->id, Fixture::class, $fixture->id);
        } catch (\Throwable) {
        }

        return response()->noContent();
    }
}
