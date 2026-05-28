<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStageRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:group_stage,round_robin,knockout'],
            'ord' => ['sometimes', 'integer', 'min:0'],
            'config' => ['nullable', 'array'],
            'starts_at' => ['nullable', 'date'],
            'locks_at' => ['nullable', 'date'],
        ];
    }
}
