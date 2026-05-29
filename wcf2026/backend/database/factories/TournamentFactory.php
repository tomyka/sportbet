<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Tournament> */
class TournamentFactory extends Factory
{
    protected $model = Tournament::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->lexify('Tournament ???');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sport' => fake()->randomElement(['football', 'basketball']),
            'status' => 'open',
            'owner_user_id' => User::factory(),
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(30),
            'settings' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function withOwner(User $user): static
    {
        return $this->state(['owner_user_id' => $user->id]);
    }
}
