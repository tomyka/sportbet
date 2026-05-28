<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Round;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Fixture> */
class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'round_id' => fn (array $attributes) => Round::factory()->state([
                'tournament_id' => $attributes['tournament_id'],
            ]),
            'home_team_id' => fn (array $attributes) => Team::factory()->state([
                'tournament_id' => $attributes['tournament_id'],
            ]),
            'away_team_id' => fn (array $attributes) => Team::factory()->state([
                'tournament_id' => $attributes['tournament_id'],
            ]),
            'status' => 'scheduled',
            'kickoff_at' => now()->addDays(7),
            'neutral_venue' => false,
            'leg' => 1,
        ];
    }
}
