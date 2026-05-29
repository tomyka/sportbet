<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStageRequest;
use App\Http\Resources\StageResource;
use App\Models\Stage;
use App\Models\Tournament;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StageAdminController extends Controller
{
    public function store(StoreStageRequest $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $stage = $tournament->stages()->create($request->validated());

        try {
            AuditLogger::record('stage.created', $user->id, Stage::class, $stage->id);
        } catch (\Throwable) {
        }

        return (new StageResource($stage))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, Tournament $tournament, Stage $stage): Response
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $stage->delete();

        try {
            AuditLogger::record('stage.deleted', $user->id, Stage::class, $stage->id);
        } catch (\Throwable) {
        }

        return response()->noContent();
    }
}
