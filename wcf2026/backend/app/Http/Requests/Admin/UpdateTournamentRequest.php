<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        /** @var \App\Models\Tournament $tournament */
        $tournament = $this->route('tournament');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:100', "unique:tournaments,slug,{$tournament->id}"],
            'sport' => ['sometimes', 'string', 'in:football,basketball,other'],
            'status' => ['sometimes', 'string', 'in:draft,open,locked,finished'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
