<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Stage */
class StageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var \Illuminate\Support\Carbon|null $startsAt */
        $startsAt = $this->starts_at;
        /** @var \Illuminate\Support\Carbon|null $locksAt */
        $locksAt = $this->locks_at;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'ord' => $this->ord,
            'type' => $this->type,
            'config' => $this->config,
            'starts_at' => $startsAt?->toIso8601String(),
            'locks_at' => $locksAt?->toIso8601String(),
        ];
    }
}
