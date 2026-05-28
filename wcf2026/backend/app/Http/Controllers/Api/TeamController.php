<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnforcesDraftVisibility;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    use EnforcesDraftVisibility;

    public function index(Request $request, Tournament $tournament): AnonymousResourceCollection
    {
        $this->abortIfDraftHidden($tournament, $request);

        return TeamResource::collection($tournament->teams()->orderBy('name')->get());
    }
}
