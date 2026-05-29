<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Stage;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Stage> */
class StageFactory extends Factory
{
    protected $model = Stage::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'name' => fake()->randomElement(['Group Stage', 'Round of 16', 'Quarter-Finals', 'Semi-Finals', 'Final']),
            'ord' => fake()->numberBetween(0, 10),
            'type' => fake()->randomElement(['group_stage', 'knockout']),
            'config' => null,
        ];
    }
}
