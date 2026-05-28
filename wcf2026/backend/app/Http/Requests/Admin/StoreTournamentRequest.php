<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'unique:tournaments,slug'],
            'sport' => ['required', 'string', 'in:football,basketball,other'],
            'status' => ['sometimes', 'string', 'in:draft,open,locked,finished'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
