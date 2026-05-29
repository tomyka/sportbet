<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Tournament;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFixtureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string|\Illuminate\Validation\Rules\Exists>> */
    public function rules(): array
    {
        /** @var Tournament $tournament */
        $tournament = $this->route('tournament');

        return [
            'kickoff_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:scheduled,live,finished,postponed,cancelled'],
            'home_score' => ['nullable', 'integer', 'min:0'],
            'away_score' => ['nullable', 'integer', 'min:0'],
            'home_score_et' => ['nullable', 'integer', 'min:0'],
            'away_score_et' => ['nullable', 'integer', 'min:0'],
            'home_score_pen' => ['nullable', 'integer', 'min:0'],
            'away_score_pen' => ['nullable', 'integer', 'min:0'],
            'winner_team_id' => [
                'nullable',
                'integer',
                Rule::exists('teams', 'id')->where('tournament_id', $tournament->id),
            ],
            'neutral_venue' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
