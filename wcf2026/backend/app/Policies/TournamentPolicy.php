<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;

class TournamentPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->is_global_admin ? true : null;
    }

    public function create(User $user): bool
    {
        return false; // global admin handled by before()
    }

    public function update(User $user, Tournament $tournament): bool
    {
        return $tournament->memberships()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function delete(User $user, Tournament $tournament): bool
    {
        return $this->update($user, $tournament);
    }
}
