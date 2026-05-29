<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Tournament */
class TournamentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var \Illuminate\Support\Carbon|null $startsAt */
        $startsAt = $this->starts_at;
        /** @var \Illuminate\Support\Carbon|null $endsAt */
        $endsAt = $this->ends_at;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sport' => $this->sport,
            'status' => $this->status,
            'starts_at' => $startsAt?->toIso8601String(),
            'ends_at' => $endsAt?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
