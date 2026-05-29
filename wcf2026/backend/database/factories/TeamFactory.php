<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Team> */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = (string) fake()->unique()->country();

        return [
            'tournament_id' => Tournament::factory(),
            'name' => $name,
            'short_name' => strtoupper(substr($name, 0, 3)),
            'logo_url' => null,
        ];
    }
}
