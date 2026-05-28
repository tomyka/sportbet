<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin Stage */
class StageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ord' => $this->ord,
            'type' => $this->type,
            'config' => $this->config,
            'starts_at' => $this->starts_at === null ? null : Carbon::parse($this->starts_at)->toIso8601String(),
            'locks_at' => $this->locks_at === null ? null : Carbon::parse($this->locks_at)->toIso8601String(),
        ];
    }
}
