<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Tournament;
use Illuminate\Http\Request;

trait EnforcesDraftVisibility
{
    /**
     * Abort 404 if the tournament is a draft and the current user cannot view it.
     * Allowed: is_global_admin OR membership with role owner|admin.
     */
    protected function abortIfDraftHidden(Tournament $tournament, Request $request): void
    {
        if (! $tournament->isDraft()) {
            return;
        }

        $user = $request->user();

        if ($user === null) {
            abort(404);
        }

        if ($user->is_global_admin) {
            return;
        }

        $canView = $tournament->memberships()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        if (! $canView) {
            abort(404);
        }
    }
}
