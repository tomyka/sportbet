<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Round;
use App\Models\Stage;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Round> */
class RoundFactory extends Factory
{
    protected $model = Round::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'stage_id' => fn (array $attributes) => Stage::factory()->state([
                'tournament_id' => $attributes['tournament_id'],
            ]),
            'name' => 'Matchday ' . fake()->numberBetween(1, 10),
            'ord' => fake()->numberBetween(0, 10),
            'deadline_at' => null,
        ];
    }
}
