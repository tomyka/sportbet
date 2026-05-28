<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin Tournament */
class TournamentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sport' => $this->sport,
            'status' => $this->status,
            'starts_at' => $this->starts_at === null ? null : Carbon::parse($this->starts_at)->toIso8601String(),
            'ends_at' => $this->ends_at === null ? null : Carbon::parse($this->ends_at)->toIso8601String(),
            'created_at' => Carbon::parse($this->created_at)->toIso8601String(),
        ];
    }
}
