<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnforcesDraftVisibility;
use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TournamentController extends Controller
{
    use EnforcesDraftVisibility;

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Tournament::query();

        if ($user === null) {
            $query->where('status', '!=', 'draft');
        } elseif (! $user->is_global_admin) {
            $query->where(function (Builder $query) use ($user): void {
                $query->where('status', '!=', 'draft')
                    ->orWhereHas('memberships', function (Builder $memberships) use ($user): void {
                        $memberships->where('user_id', $user->id)
                            ->whereIn('role', ['owner', 'admin']);
                    });
            });
        }

        return TournamentResource::collection($query->latest()->paginate(20));
    }

    public function show(Request $request, Tournament $tournament): TournamentResource
    {
        $this->abortIfDraftHidden($tournament, $request);

        return new TournamentResource($tournament);
    }
}
