<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Models\Group;
use App\Models\Tournament;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GroupAdminController extends Controller
{
    public function store(StoreGroupRequest $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $group = $tournament->groups()->create($request->validated());

        try {
            AuditLogger::record('group.created', $user->id, Group::class, $group->id);
        } catch (\Throwable) {
        }

        return response()->json(['data' => ['id' => $group->id, 'name' => $group->name]], 201);
    }

    public function destroy(Request $request, Tournament $tournament, Group $group): Response
    {
        $this->authorize('update', $tournament);

        $user = $this->authUser($request);

        $group->delete();

        try {
            AuditLogger::record('group.deleted', $user->id, Group::class, $group->id);
        } catch (\Throwable) {
        }

        return response()->noContent();
    }
}
