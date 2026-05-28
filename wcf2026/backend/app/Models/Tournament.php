<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tournament extends Model
{
    /** @use HasFactory<\Database\Factories\TournamentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'sport', 'status', 'owner_user_id', 'starts_at', 'ends_at', 'settings'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['settings' => 'array', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasMany<Stage, $this> */
    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class);
    }

    /** @return HasMany<Team, $this> */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /** @return HasMany<TournamentMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(TournamentMembership::class);
    }

    /** @return HasMany<Fixture, $this> */
    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
