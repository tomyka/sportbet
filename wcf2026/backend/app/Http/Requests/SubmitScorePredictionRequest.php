<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitScorePredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth enforced by route middleware
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0', 'max:99'],
            'away_score' => ['required', 'integer', 'min:0', 'max:99'],
            // Optional: the team the user predicts will win (for knockout draw scenarios)
            'predicted_winner_team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ];
    }
}
