<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Tournament;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFixtureRequest extends FormRequest
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
            'round_id' => [
                'required',
                'integer',
                Rule::exists('rounds', 'id')->where('tournament_id', $tournament->id),
            ],
            'home_team_id' => [
                'required',
                'integer',
                Rule::exists('teams', 'id')->where('tournament_id', $tournament->id),
            ],
            'away_team_id' => [
                'required',
                'integer',
                Rule::exists('teams', 'id')->where('tournament_id', $tournament->id),
                'different:home_team_id',
            ],
            'kickoff_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:scheduled,live,finished,postponed,cancelled'],
            'neutral_venue' => ['sometimes', 'boolean'],
            'leg' => ['sometimes', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
