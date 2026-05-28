<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tournament;
use App\Models\TournamentMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TournamentMembership> */
class TournamentMembershipFactory extends Factory
{
    protected $model = TournamentMembership::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'tournament_id' => Tournament::factory(), 'role' => 'player'];
    }

    public function asOwner(): static
    {
        return $this->state(['role' => 'owner']);
    }

    public function asAdmin(): static
    {
        return $this->state(['role' => 'admin']);
    }
}
