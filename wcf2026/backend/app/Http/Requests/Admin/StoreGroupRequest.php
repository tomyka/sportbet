<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Tournament;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'stage_id' => [
                'required',
                'integer',
                Rule::exists('stages', 'id')->where('tournament_id', $tournament->id),
            ],
        ];
    }
}
