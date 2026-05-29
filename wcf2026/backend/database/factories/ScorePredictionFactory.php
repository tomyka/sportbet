<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ScorePrediction>
 */
class ScorePredictionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            // tournament_id is set first; fixture_id closure uses it to create a matching fixture
            'tournament_id' => Tournament::factory(),
            'user_id' => User::factory(),
            'fixture_id' => fn (array $attrs) => Fixture::factory()->create([
                'tournament_id' => $attrs['tournament_id'],
            ])->id,
            'home_score' => $this->faker->numberBetween(0, 5),
            'away_score' => $this->faker->numberBetween(0, 5),
            'predicted_winner_team_id' => null,
            'submitted_at' => now(),
        ];
    }
}
